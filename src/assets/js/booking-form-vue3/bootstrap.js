/**
 * bootstrap.js — entry module for the Vue3 form.
 *
 * Initializes `window.BookingPressFormV3`, reads the JSON-island data, and
 * dispatches `mountBookingFormInstance()` for every instance carried on
 * the current page (multi-instance support).
 *
 * @see docs/migration/BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md §1
 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md
 */
import { mountBookingFormInstance } from 'bookingpress-form-v3';
import { installSlotApi } from './utils/slots.js?v=3';
import { formatPrice } from './utils/currency.js';
import { formatDate, formatTime } from './utils/datetime.js?v=2';

// Side-effect import: `bp-vcalendar.js` is an IIFE bundle that populates
// `window.BpVCalendar` (the DatePicker mount bridge used by DateTimeStep).
// WordPress declares `bookingpress-vcalendar` as a script-module dependency
// of `bookingpress-form-v3`, but a dependency only guarantees the file is
// pre-loaded — the IIFE never executes unless something `import`s the
// handle. Importing it here for its side-effect ensures
// `window.BpVCalendar.mountDatePicker` is callable by the time Step 2
// mounts.
import 'bookingpress-vcalendar';

// Same pattern for `bookingpress-ui.js` — populates `window.BookingPressUI`
// (the Vue plugin that registers BpUiTelInput, BpUiButton, BpUiSelect, etc.).
// The app.js mount path calls `app.use(window.BookingPressUI)` so these
// components are available in step templates by their kebab-case tags.
import 'bookingpress-ui';

(function initRegistry() {
  if (typeof window === 'undefined') return;
  if (!window.BookingPressFormV3) {
    window.BookingPressFormV3 = { instances: {} };
  }
  if (!window.BookingPressFormV3.instances) {
    window.BookingPressFormV3.instances = {};
  }
  // Expose the M6 mount helper for add-ons that may want to remount.
  window.BookingPressFormV3.mountBookingFormInstance = mountBookingFormInstance;

  // Expose the canonical price formatter on the registry so Pro / add-on
  // modules can render amounts with the active currency configuration without
  // re-importing Lite internals (Lite remains the single source of truth for
  // the formatting rules). Mirrors the util signature:
  //   window.BookingPressFormV3.formatPrice(state.config, amount)
  if (typeof window.BookingPressFormV3.formatPrice !== 'function') {
    window.BookingPressFormV3.formatPrice = formatPrice;
  }

  // Same pattern for the canonical date / time label formatters. Add-ons
  // (e.g. the Cart step) render date/time labels from the raw canonical
  // "YYYY-MM-DD" / "HH:MM" values stored in the working selection and must
  // re-apply the admin Date format + 12/24-hour time format — exactly what the
  // Summary step does via these utils. Exposing them here keeps Lite the single
  // source of truth for the formatting rules. Mirrors the util signatures:
  //   window.BookingPressFormV3.formatDate(state.config, ymd)
  //   window.BookingPressFormV3.formatTime(state.config, hhmm)
  if (typeof window.BookingPressFormV3.formatDate !== 'function') {
    window.BookingPressFormV3.formatDate = formatDate;
  }
  if (typeof window.BookingPressFormV3.formatTime !== 'function') {
    window.BookingPressFormV3.formatTime = formatTime;
  }

  // Tiny global event bus shared across instances. Per-instance buses live
  // on the instance handle; this is the cross-instance broadcast channel.
  if (!window.BookingPressFormV3.bus) {
    const listeners = new Map();
    window.BookingPressFormV3.bus = {
      emit(event, payload) {
        const set = listeners.get(event);
        if (!set) return;
        for (const cb of set) { try { cb(payload); } catch (_e) {} }
      },
      on(event, cb) {
        if (!listeners.has(event)) listeners.set(event, new Set());
        listeners.get(event).add(cb);
        return () => window.BookingPressFormV3.bus.off(event, cb);
      },
      off(event, cb) {
        const set = listeners.get(event);
        if (set) set.delete(cb);
      },
    };
  }

  // M9: install the slot-renderer API (`renderInSlot` + auto-rerender via
  // MutationObserver). Add-ons call:
  //   window.BookingPressFormV3.renderInSlot(instanceId, slotName, factory)
  installSlotApi();

  // Add-on registry — add-ons call `registerAddon(name, factory)` to get
  // a hook called per instance after mount. Each factory receives `{ instanceId,
  // state, api, bus }`.
  if (!window.BookingPressFormV3.addons) window.BookingPressFormV3.addons = new Map();
  if (typeof window.BookingPressFormV3.registerAddon !== 'function') {
    window.BookingPressFormV3.registerAddon = function (name, factory) {
      if (!name || typeof factory !== 'function') return;
      window.BookingPressFormV3.addons.set(String(name), factory);
      // Run for already-mounted instances.
      for (const handle of Object.values(window.BookingPressFormV3.instances)) {
        try { factory({ instanceId: handle.instanceId, state: handle.state, api: handle.api, bus: handle.bus }); } catch (_e) {}
      }
    };
  }
})();

function readModuleData() {
  if (typeof document === 'undefined') return null;
  const node = document.getElementById('wp-script-module-data-bookingpress-form-v3-loader');
  if (!node) return null;
  try {
    return JSON.parse(node.textContent || '{}');
  } catch (err) {
    // eslint-disable-next-line no-console
    console.warn('[bp-v3] failed to parse module data island', err);
    return null;
  }
}

const moduleData = readModuleData();
const instances = (moduleData && moduleData.instances) || {};
const count = Object.keys(instances).length;

// eslint-disable-next-line no-console
console.info(`[bp-v3] loader ready (instances: ${count})`);

function instanceRoot(instanceId, scope = document) {
  if (!scope || typeof scope.querySelector !== 'function') return null;
  return scope.querySelector(`[data-bp-v3-instance="${instanceId}"]`)
    || scope.querySelector(`[data-instance="${instanceId}"]`)
    || scope.querySelector(`#bookingpress-form-vue3-${instanceId}`)
    || null;
}

function runAddons(id, handle) {
  if (!handle || !window.BookingPressFormV3 || !window.BookingPressFormV3.addons) return;
  for (const [name, factory] of window.BookingPressFormV3.addons.entries()) {
    try {
      factory({
        instanceId: id,
        state: handle.state,
        api: handle.api,
        bus: handle.bus,
        name,
      });
    } catch (_e) {}
  }
}

/**
 * Mount (or remount) an instance on a specific DOM node.
 *
 * Elementor renders popup templates in a hidden source container and creates
 * the live modal from that markup when the popup opens. DOM copies retain the
 * rendered HTML but not Vue's event listeners, so the live popup must own a
 * fresh Vue app. On subsequent opens, seed the replacement app from the
 * current reactive state so the visitor's selections are preserved.
 */
function mountInstance(id, initialState, mountNode = null) {
  const registry = window.BookingPressFormV3 && window.BookingPressFormV3.instances;
  const current = registry && registry[id];

  if (current && mountNode && current.mountNode === mountNode && mountNode.__vue_app__) {
    return current;
  }

  const stateSeed = current && current.state ? current.state : initialState;
  if (current && current.app && typeof current.app.unmount === 'function') {
    try { current.app.unmount(); } catch (_e) {}
  }

  const handle = mountBookingFormInstance(id, stateSeed, mountNode);
  runAddons(id, handle);
  return handle;
}

function isHiddenElementorPopupTemplate(root) {
  return !!(
    root
    && typeof root.closest === 'function'
    && root.closest('[data-elementor-type="popup"]')
    && !root.closest('.elementor-popup-modal')
  );
}

for (const [id, initialState] of Object.entries(instances)) {
  try {
    const root = instanceRoot(id);
    if (isHiddenElementorPopupTemplate(root)) {
      // Mount only after Elementor creates/reveals the live modal. Mounting
      // this hidden source node would attach listeners to markup that is not
      // the markup the visitor interacts with.
      // eslint-disable-next-line no-console
      console.info('[bp-v3] deferred Elementor popup instance', id);
      continue;
    }
    mountInstance(id, initialState, root);
  } catch (err) {
    // eslint-disable-next-line no-console
    console.error('[bp-v3] failed to mount instance', id, err);
  }
}

if (typeof window !== 'undefined') {
  window.addEventListener('elementor/popup/show', (event) => {
    const detailInstance = event && event.detail && event.detail.instance;
    const popupRoot = detailInstance
      && detailInstance.$element
      && detailInstance.$element[0];
    const scope = popupRoot && typeof popupRoot.querySelectorAll === 'function'
      ? popupRoot
      : document;
    const roots = scope.querySelectorAll(
      '.bpa-frontend-main-container.bpa-frontend-vue3[data-bp-v3-instance],'
      + '.bpa-frontend-main-container.bpa-frontend-vue3[data-instance]'
    );

    for (const root of roots) {
      const id = root.getAttribute('data-bp-v3-instance')
        || root.getAttribute('data-instance')
        || '';
      if (!id || !instances[id]) continue;
      try {
        mountInstance(id, instances[id], root);
      } catch (err) {
        // eslint-disable-next-line no-console
        console.error('[bp-v3] failed to mount Elementor popup instance', id, err);
      }
    }
  });
}

export {};
