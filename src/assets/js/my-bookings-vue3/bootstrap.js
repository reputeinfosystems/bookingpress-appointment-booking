/**
 * bootstrap.js — entry module for the Vue3 My Bookings scaffold (MB-1A).
 *
 * Initializes `window.BookingPressMyBookingsV3`, reads the JSON-island data
 * emitted by `MyBookings::filter_module_data()`, and mounts every instance
 * carried on the current page (multi-instance support).
 *
 * Also installs the add-on registry (`registerAddon`) Pro / add-on script
 * modules use to extend the My Bookings app with tabs and row actions —
 * mirrors `window.BookingPressFormV3.registerAddon` in the booking form.
 */
import { mountMyBookingsInstance } from 'bookingpress-my-bookings-v3';

// Side-effect import: `bookingpress-ui.min.js` is an IIFE that populates
// `window.BookingPressUI` (the Element Plus-based Vue plugin). A module
// dependency only guarantees the file is pre-loaded; importing it here for its
// side-effect ensures `window.BookingPressUI` exists before mount so
// `app.use(window.BookingPressUI)` can register the bp-ui-* components.
import 'bookingpress-ui';

(function initRegistry() {
  if (typeof window === 'undefined') return;
  if (!window.BookingPressMyBookingsV3) {
    window.BookingPressMyBookingsV3 = { instances: {} };
  }
  if (!window.BookingPressMyBookingsV3.instances) {
    window.BookingPressMyBookingsV3.instances = {};
  }
  window.BookingPressMyBookingsV3.mountMyBookingsInstance = mountMyBookingsInstance;

  // Add-on registry — Pro/add-on modules call `registerAddon(name, factory)`
  // to be handed each mounted instance's extension api. Factories receive
  // `{ instanceId, api, state }`:
  //   api.registerTab({ id, title, icon, component, order })
  //   api.registerRowAction({ id, component, placement, order })
  //   api.reloadAppointments(page) / api.notifySuccess(message)
  // Same lifecycle as the booking form registry: run immediately for
  // already-mounted instances, then per future mount (loop below).
  if (!window.BookingPressMyBookingsV3.addons) {
    window.BookingPressMyBookingsV3.addons = new Map();
  }
  if (typeof window.BookingPressMyBookingsV3.registerAddon !== 'function') {
    window.BookingPressMyBookingsV3.registerAddon = function (name, factory) {
      if (!name || typeof factory !== 'function') return;
      window.BookingPressMyBookingsV3.addons.set(String(name), factory);
      // Run for already-mounted instances.
      for (const handle of Object.values(window.BookingPressMyBookingsV3.instances)) {
        if (!handle || !handle.api) continue;
        try { factory({ instanceId: handle.instanceId, api: handle.api, state: handle.config }); } catch (_e) {}
      }
    };
  }
})();

function readModuleData() {
  if (typeof document === 'undefined') return null;
  const node = document.getElementById('wp-script-module-data-bookingpress-my-bookings-v3-loader');
  if (!node) return null;
  try {
    return JSON.parse(node.textContent || '{}');
  } catch (err) {
    // eslint-disable-next-line no-console
    console.warn('[bp-mb-v3] failed to parse module data island', err);
    return null;
  }
}

const moduleData = readModuleData();
const instances = (moduleData && moduleData.instances) || {};
const count = Object.keys(instances).length;

// eslint-disable-next-line no-console
console.info(`[bp-mb-v3] loader ready (instances: ${count})`);

for (const [id, initialState] of Object.entries(instances)) {
  try {
    const handle = mountMyBookingsInstance(id, initialState);
    // Run pre-registered add-ons against the freshly-mounted instance.
    if (handle && handle.api && window.BookingPressMyBookingsV3.addons) {
      for (const [name, factory] of window.BookingPressMyBookingsV3.addons.entries()) {
        try { factory({ instanceId: id, api: handle.api, state: handle.config, name }); } catch (_e) {}
      }
    }
  } catch (err) {
    // eslint-disable-next-line no-console
    console.error('[bp-mb-v3] failed to mount instance', id, err);
  }
}

export {};
