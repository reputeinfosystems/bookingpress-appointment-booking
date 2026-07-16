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

for (const [id, initialState] of Object.entries(instances)) {
  try {
    const handle = mountBookingFormInstance(id, initialState);
    // Run pre-registered add-ons against the freshly-mounted instance.
    if (handle && window.BookingPressFormV3 && window.BookingPressFormV3.addons) {
      for (const [name, factory] of window.BookingPressFormV3.addons.entries()) {
        try { factory({ instanceId: id, state: handle.state, api: handle.api, bus: handle.bus, name }); } catch (_e) {}
      }
    }
  } catch (err) {
    // eslint-disable-next-line no-console
    console.error('[bp-v3] failed to mount instance', id, err);
  }
}

export {};
