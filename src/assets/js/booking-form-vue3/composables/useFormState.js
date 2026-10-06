/**
 * useFormState — wraps the JSON-island initial state in a Vue 3 reactive proxy.
 *
 * One instance per form. Exposes the state object plus a tiny event bus
 * scoped to the instance (`emit(event, payload)` + `on(event, cb)`).
 */
import { reactive, ref } from 'vue';

/**
 * @param {object} initial  The per-instance object from `state.instances[id]`.
 * @returns {{ state:object, bus:{emit:Function, on:Function, off:Function}, ready:import('vue').Ref<boolean> }}
 */
export function useFormState(initial) {
  // Defensive clone — never mutate the JSON-island in place. Deep clone the
  // pieces that are arrays/objects we'll mutate; primitives are copied by
  // value automatically.
  const state = reactive(deepClone(initial || {}));

  // Ensure required nested keys exist so consumers don't have to null-check.
  state.appointment_step_form_data = state.appointment_step_form_data || {};
  state.services        = Array.isArray(state.services)        ? state.services        : [];
  state.categories      = Array.isArray(state.categories)      ? state.categories      : [];
  state.steps           = Array.isArray(state.steps)           ? state.steps           : [];
  state.customer_form_fields  = Array.isArray(state.customer_form_fields)  ? state.customer_form_fields  : [];
  state.customer_details_rule = state.customer_details_rule || {};
  state.customer_details_msg  = state.customer_details_msg || {};
  state.config          = state.config   || {};
  state.strings         = state.strings  || {};
  state.preselection    = state.preselection || {};

  // UI-only flags that the M5 state builder doesn't seed.
  state.isSubmitting = false;
  state.submitError  = '';
  state.submitOk     = false;
  state.redirectUrl  = '';

  // Post-booking confirmation flags. Default behaviour is to redirect to the
  // thank-you page, so these stay false/empty for a Lite-only render. Pro's
  // In-Built redirection feature sets `config.postBookingMode = 'inline'`,
  // which makes App.js flip `bookingComplete` (and set `bookingOutcome` to
  // 'success' / 'failed') instead of redirecting, revealing the inline
  // confirmation slot.
  state.bookingComplete = false;
  state.bookingOutcome  = '';

  // Per-instance event bus. Every emit is mirrored on the cross-instance
  // `window.BookingPressFormV3.bus` so add-ons can subscribe once globally
  // and receive events from every instance without managing per-instance
  // subscriptions. Per plan §3.3.
  const listeners = new Map();
  const bus = {
    emit(event, payload) {
      const set = listeners.get(event);
      if (set) {
        for (const cb of set) {
          try { cb(payload); } catch (e) { /* swallow */ }
        }
      }
      // Mirror to the global bus if it exists.
      if (typeof window !== 'undefined'
        && window.BookingPressFormV3
        && window.BookingPressFormV3.bus
        && window.BookingPressFormV3.bus !== bus) {
        try { window.BookingPressFormV3.bus.emit(event, payload); } catch (_e) { /* swallow */ }
      }
    },
    on(event, cb) {
      if (!listeners.has(event)) listeners.set(event, new Set());
      listeners.get(event).add(cb);
      return () => bus.off(event, cb);
    },
    off(event, cb) {
      const set = listeners.get(event);
      if (set) set.delete(cb);
    },
  };

  const ready = ref(true);
  return { state, bus, ready };
}

function deepClone(src) {
  if (src === null || typeof src !== 'object') return src;
  if (Array.isArray(src)) return src.map(deepClone);
  const out = {};
  for (const k of Object.keys(src)) out[k] = deepClone(src[k]);
  return out;
}
