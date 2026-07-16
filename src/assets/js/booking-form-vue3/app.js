/**
 * app.js — Vue 3 application root for one form instance.
 *
 * Exports `mountBookingFormInstance(instanceId, initialState)` which:
 *   1. Creates the per-instance composables (state, api, readiness, nav,
 *      timeslots, submission, bus).
 *   2. Builds a Vue 3 app from the App component with those composables
 *      injected via props.
 *   3. Mounts onto the shell `<div id="bp-v3-form-{instanceId}">`.
 *   4. Registers the instance under `window.BookingPressFormV3.instances[id]`
 *      so add-ons can find it.
 */
import { createApp } from 'vue';

// Relative imports resolve to URLs without WP's `?ver=` cache-buster, so a
// query string is appended manually. Bump the `?v=` suffix when you edit any
// of the imported files to force a fresh fetch even when the browser has
// cached an earlier copy under a no-query URL.
import App from './components/App.js?v=32';
import { createApiClient } from './api/client.js?v=2';
import { useFormState } from './composables/useFormState.js?v=3';
import { useReadiness } from './composables/useReadiness.js?v=4';
import { useStepNavigation } from './composables/useStepNavigation.js?v=4';
import { useTimeslots } from './composables/useTimeslots.js?v=13';
import { useSubmission } from './composables/useSubmission.js?v=6';

// eslint-disable-next-line no-console
console.info('[bp-v3] app module loaded');

/**
 * Mount a single Vue 3 form instance.
 *
 * @param {string} instanceId
 * @param {object} initialState
 * @returns {object} The mounted-instance handle (also stored on the global registry).
 */
export function mountBookingFormInstance(instanceId, initialState) {
  // The shortcode root carries `data-instance="<id>"` (matching the released
  // markup) AND `data-bp-v3-instance="<id>"` (back-compat for any add-on that
  // wrote against the M5 path). Either selector finds the same node.
  const mountSel = `[data-instance="${instanceId}"], [data-bp-v3-instance="${instanceId}"]`;
  const mountNode =
    document.querySelector(`[data-instance="${instanceId}"]`) ||
    document.querySelector(`[data-bp-v3-instance="${instanceId}"]`) ||
    document.querySelector(`#bookingpress-form-vue3-${instanceId}`) ||
    document.querySelector(`#bookingpress-form-vue3-bpv3_${instanceId}`) ||
    document.querySelector(`#bp-v3-form-${instanceId}`);
  if (!mountNode) {
    // eslint-disable-next-line no-console
    console.warn('[bp-v3] mount node missing:', mountSel);
    return null;
  }

  // Compose the per-instance world.
  const { state, bus } = useFormState(initialState);

  const api = createApiClient({
    restRoot:      (initialState.rest && initialState.rest.root) || '',
    instanceId:    instanceId,
    wpRestNonce:   (initialState.nonces && initialState.nonces.wpRestNonce)   || '',
    formNonce:     (initialState.nonces && initialState.nonces.formNonce)     || '',
    instanceToken: (initialState.nonces && initialState.nonces.instanceToken) || '',
  });

  const readiness  = useReadiness(state);
  const nav        = useStepNavigation(state, readiness, bus);
  const timeslots  = useTimeslots(state, api, bus);
  const submission = useSubmission(state, readiness, api, bus);

  // Wrap with explicit value-access for the consumer (.value on refs).
  const handle = { instanceId, state, api, readiness, nav, timeslots, submission, bus, app: null, mountNode };

  const app = createApp(App, { instanceId, state, api, readiness, nav, timeslots, submission, bus });
  app.config.errorHandler = (err, _vm, info) => {
    // eslint-disable-next-line no-console
    console.error('[bp-v3] runtime error', { instanceId, info, error: err });
    bus.emit('bp-v3:error', { instanceId, error: err, info });
  };

  // Register the BookingPress UI plugin if it loaded — exposes
  // `<bp-ui-tel-input>` (the country-flag phone field used in Basic Details)
  // and many other components. The bootstrap module side-effect-imports
  // `bookingpress-ui` so `window.BookingPressUI` should already exist by
  // the time we mount; guard defensively just in case.
  if (typeof window !== 'undefined' && window.BookingPressUI && typeof window.BookingPressUI.install === 'function') {
    try { app.use(window.BookingPressUI); }
    catch (e) {
      // eslint-disable-next-line no-console
      console.warn('[bp-v3] BookingPressUI install failed', e);
    }
  }

  handle.app = app;

  app.mount(mountNode);

  // Register the handle so add-ons can interact.
  if (!window.BookingPressFormV3) window.BookingPressFormV3 = { instances: {} };
  if (!window.BookingPressFormV3.instances) window.BookingPressFormV3.instances = {};
  window.BookingPressFormV3.instances[instanceId] = handle;

  // Per plan §3.3. The bus auto-mirrors to the global
  // `window.BookingPressFormV3.bus`, so a single emit reaches both.
  bus.emit('bp-v3:form-mounted', { instanceId, state });
  return handle;
}

export default { mountBookingFormInstance };
