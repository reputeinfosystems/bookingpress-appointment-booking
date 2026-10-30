/**
 * bootstrap.js — entry module for the Vue 3 cancellation confirmation.
 *
 * Initializes `window.BookingPressCancellationConfirmationV3`, reads the
 * JSON-island data emitted by `CancellationConfirmation::filter_module_data()`,
 * and mounts every instance carried on the current page.
 *
 * Also installs the add-on registry (`registerAddon`) — same lifecycle as
 * `window.BookingPressMyBookingsV3.registerAddon`.
 */
import { mountCancellationConfirmationInstance } from 'bookingpress-cancellation-confirmation-v3';

(function initRegistry() {
  if (typeof window === 'undefined') return;
  if (!window.BookingPressCancellationConfirmationV3) {
    window.BookingPressCancellationConfirmationV3 = { instances: {} };
  }
  if (!window.BookingPressCancellationConfirmationV3.instances) {
    window.BookingPressCancellationConfirmationV3.instances = {};
  }
  window.BookingPressCancellationConfirmationV3.mountCancellationConfirmationInstance = mountCancellationConfirmationInstance;

  // Add-on registry — factories receive `{ instanceId, api, state }`:
  //   api.registerBeforeCancel(fn) / api.setError(message) / api.clearError()
  // Run immediately for already-mounted instances, then per future mount.
  if (!window.BookingPressCancellationConfirmationV3.addons) {
    window.BookingPressCancellationConfirmationV3.addons = new Map();
  }
  if (typeof window.BookingPressCancellationConfirmationV3.registerAddon !== 'function') {
    window.BookingPressCancellationConfirmationV3.registerAddon = function (name, factory) {
      if (!name || typeof factory !== 'function') return;
      window.BookingPressCancellationConfirmationV3.addons.set(String(name), factory);
      for (const handle of Object.values(window.BookingPressCancellationConfirmationV3.instances)) {
        if (!handle || !handle.api) continue;
        try { factory({ instanceId: handle.instanceId, api: handle.api, state: handle.config }); } catch (_e) {}
      }
    };
  }
})();

function readModuleData() {
  if (typeof document === 'undefined') return null;
  const node = document.getElementById('wp-script-module-data-bookingpress-cancellation-confirmation-v3-loader');
  if (!node) return null;
  try {
    return JSON.parse(node.textContent || '{}');
  } catch (err) {
    // eslint-disable-next-line no-console
    console.warn('[bp-cc-v3] failed to parse module data island', err);
    return null;
  }
}

const moduleData = readModuleData();
const instances = (moduleData && moduleData.instances) || {};

for (const [id, initialState] of Object.entries(instances)) {
  try {
    const handle = mountCancellationConfirmationInstance(id, initialState);
    if (handle && handle.api && window.BookingPressCancellationConfirmationV3.addons) {
      for (const [name, factory] of window.BookingPressCancellationConfirmationV3.addons.entries()) {
        try { factory({ instanceId: id, api: handle.api, state: handle.config, name }); } catch (_e) {}
      }
    }
  } catch (err) {
    // eslint-disable-next-line no-console
    console.error('[bp-cc-v3] failed to mount instance', id, err);
  }
}

export {};
