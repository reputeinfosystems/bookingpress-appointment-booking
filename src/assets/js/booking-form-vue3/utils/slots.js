/**
 * slots.js — slot inventory plumbing for add-ons (§M0.13).
 *
 * Each step component renders a `<div class="bp-v3-slot" data-bp-v3-slot="…"
 * data-bp-v3-instance="…">` for every named slot. Add-ons inject UI into
 * these containers via:
 *
 *   window.BookingPressFormV3.renderInSlot(instanceId, slotName, factory)
 *
 * The factory receives `{ node, instanceId, slotName, state, bus, api,
 * ...extraContext }` and is responsible for rendering DOM into `node`.
 * It is re-invoked whenever the slot container is re-mounted (because the
 * step it lives in just rendered).
 *
 * This module also installs a MutationObserver on every Vue 3 form shell
 * so that whenever a new slot container appears in the DOM (i.e. when a
 * step is entered), the `bp-v3:slot-ready` event fires AND any registered
 * factories run automatically.
 */

const REGISTRY_KEY = '__bp_v3_slot_factories';   // Map<`${instanceId}|${slotName}`, Array<factory>>
const OBSERVERS_KEY = '__bp_v3_slot_observers';  // Map<instanceId, MutationObserver>
const NODE_RUN_KEY = '__bp_v3_factories_run';    // per slot node: Set<factory> already run on it

function ensureRegistry() {
  if (!window.BookingPressFormV3) {
    window.BookingPressFormV3 = { instances: {} };
  }
  if (!window.BookingPressFormV3[REGISTRY_KEY]) {
    window.BookingPressFormV3[REGISTRY_KEY] = new Map();
  }
  if (!window.BookingPressFormV3[OBSERVERS_KEY]) {
    window.BookingPressFormV3[OBSERVERS_KEY] = new Map();
  }
  return window.BookingPressFormV3;
}

/**
 * Collect every factory registered for this (instanceId, slotName): the
 * instance-specific registrations plus the `*` wildcard ones. Multiple add-ons
 * may target the same slot (e.g. two payment gateways on
 * `summary-step:above-actions`), so ALL matching factories run — they are no
 * longer overwritten or deduped by a single-value lookup.
 *
 * @param {object} bp3
 * @param {string} instanceId
 * @param {string} slotName
 * @returns {function[]}
 */
function factoriesFor(bp3, instanceId, slotName) {
  const reg = bp3[REGISTRY_KEY];
  const specific = reg.get(`${instanceId}|${slotName}`) || [];
  const wildcard = reg.get(`*|${slotName}`) || [];
  if (!specific.length) return wildcard;
  if (!wildcard.length) return specific;
  return specific.concat(wildcard);
}

/**
 * Invoke ONE factory against ONE slot node, guarding against running the same
 * factory into the same node twice. Each node tracks the factories already run
 * on it in a Set (NODE_RUN_KEY); a freshly (re)mounted node is a brand-new
 * element with an empty set, so revisiting a step naturally re-runs every
 * factory. The guard is per-factory (not per-slot), so multiple add-ons sharing
 * a slot don't block each other.
 *
 * @param {Element}  node
 * @param {string}   instanceId
 * @param {string}   slotName
 * @param {function} factory
 * @param {object}   handle      The instance handle (state/bus/api).
 */
function invokeFactory(node, instanceId, slotName, factory, handle) {
  let runSet = node[NODE_RUN_KEY];
  if (!runSet) {
    runSet = node[NODE_RUN_KEY] = new Set();
  }
  if (runSet.has(factory)) return;
  runSet.add(factory);
  try {
    factory({
      node,
      instanceId,
      slotName,
      state: handle.state,
      bus: handle.bus,
      api: handle.api,
      serviceId: node.dataset.bpV3ServiceId ? parseInt(node.dataset.bpV3ServiceId, 10) : null,
    });
  } catch (e) {
    // eslint-disable-next-line no-console
    console.error('[bp-v3] slot factory error', { instanceId, slotName, error: e });
  }
}

/**
 * Run a single factory against every matching slot container currently in the
 * DOM (used at registration time and on form-mounted).
 *
 * @param {object}   bp3        The registry root.
 * @param {string}   instanceId
 * @param {string}   slotName
 * @param {function} factory
 */
function runFactoryForExistingNodes(bp3, instanceId, slotName, factory) {
  const handle = bp3.instances && bp3.instances[instanceId];
  if (!handle) return;
  const shell = handle.mountNode || document.getElementById('bp-v3-form-' + instanceId);
  if (!shell) return;
  const selector = `.bp-v3-slot[data-bp-v3-slot="${slotName}"][data-bp-v3-instance="${instanceId}"]`;
  const nodes = shell.querySelectorAll(selector);
  for (const node of nodes) {
    invokeFactory(node, instanceId, slotName, factory, handle);
  }
}

/**
 * Install (or reuse) a MutationObserver on this instance's shell that
 * watches for new slot containers and (a) fires `bp-v3:slot-ready`,
 * (b) runs any registered factories.
 *
 * @param {string} instanceId
 */
function installShellObserver(instanceId) {
  const bp3 = ensureRegistry();
  const handle = bp3.instances[instanceId];
  if (!handle || !handle.mountNode) return;
  const obsKey = bp3[OBSERVERS_KEY];
  if (obsKey.has(instanceId)) return; // already installed

  const observer = new MutationObserver((mutations) => {
    for (const m of mutations) {
      for (const added of m.addedNodes) {
        if (added.nodeType !== 1) continue;
        // Check added subtree for slot containers.
        const slots = added.matches && added.matches('.bp-v3-slot')
          ? [added]
          : Array.from(added.querySelectorAll ? added.querySelectorAll('.bp-v3-slot') : []);
        for (const slotNode of slots) {
          const slotName = slotNode.getAttribute('data-bp-v3-slot');
          const slotInstance = slotNode.getAttribute('data-bp-v3-instance');
          if (!slotName || slotInstance !== instanceId) continue;
          // Fire ready event.
          if (handle.bus) {
            handle.bus.emit('bp-v3:slot-ready', { instanceId, slotName, node: slotNode });
          }
          // Run EVERY registered factory for this instance AND the `*` wildcard.
          // The node is brand-new, so invokeFactory's per-factory guard lets
          // each one run exactly once.
          const factories = factoriesFor(bp3, instanceId, slotName);
          for (const factory of factories) {
            invokeFactory(slotNode, instanceId, slotName, factory, handle);
          }
        }
      }
    }
  });
  observer.observe(handle.mountNode, { childList: true, subtree: true });
  obsKey.set(instanceId, observer);
}

/**
 * Install the public `renderInSlot` API on the global registry.
 *
 * Add-ons call this once with a factory; the factory runs against every
 * currently-mounted slot AND every future re-mount of the same slot
 * (e.g. when the user revisits the step).
 *
 * Usage:
 *   window.BookingPressFormV3.renderInSlot(instanceId, 'service-step:after-list', ({ node, state }) => {
 *     node.innerHTML = '<div>Hello from the add-on!</div>';
 *   });
 *
 * Use `'*'` as the instanceId to target every mounted instance.
 */
export function installSlotApi() {
  const bp3 = ensureRegistry();

  // Hook every instance that mounts so we can observe its shell.
  bp3.bus.on('bp-v3:form-mounted', (e) => {
    installShellObserver(e.instanceId);
    // Run any factories registered against this instance BEFORE the mount.
    for (const [key, factories] of bp3[REGISTRY_KEY].entries()) {
      const sep = key.indexOf('|');
      const iid = key.slice(0, sep);
      const slotName = key.slice(sep + 1);
      if (iid === e.instanceId || iid === '*') {
        for (const factory of factories) {
          runFactoryForExistingNodes(bp3, e.instanceId, slotName, factory);
        }
      }
    }
  });

  bp3.renderInSlot = function renderInSlot(instanceId, slotName, factory) {
    if (typeof factory !== 'function' || !slotName) return () => {};
    const target = String(instanceId || '*');
    const key = `${target}|${slotName}`;
    const reg = bp3[REGISTRY_KEY];

    // Append to the factory list for this key (multiple add-ons may share a
    // slot). Guard against registering the exact same factory twice.
    let list = reg.get(key);
    if (!list) {
      list = [];
      reg.set(key, list);
    }
    if (list.indexOf(factory) === -1) {
      list.push(factory);
    }

    if (target === '*') {
      // Wildcard — fires for every currently-mounted instance.
      for (const iid of Object.keys(bp3.instances || {})) {
        runFactoryForExistingNodes(bp3, iid, slotName, factory);
      }
    } else {
      runFactoryForExistingNodes(bp3, target, slotName, factory);
    }

    // Teardown removes only THIS factory (and drops the key when empty).
    return () => {
      const arr = reg.get(key);
      if (!arr) return;
      const i = arr.indexOf(factory);
      if (i !== -1) arr.splice(i, 1);
      if (arr.length === 0) reg.delete(key);
    };
  };
}
