/**
 * useSubmission — submit pipeline.
 *
 * Reads the form_data, calls `api.submit()`, handles the envelope
 * (redirect_url / error / readiness_failed), updates UI flags.
 */
import { ref } from 'vue';
import { effectivePrice } from '../utils/pricing.js?v=2';
import { payableAmount } from '../utils/payable.js?v=1';

export function useSubmission(state, readiness, api, bus) {
  const isSubmitting = ref(false);
  const submitError  = ref('');
  const submitOk     = ref(false);
  const redirectUrl  = ref('');
  const failedGates  = ref([]);

  async function submit() {
    submitError.value = '';
    submitOk.value    = false;
    redirectUrl.value = '';
    failedGates.value = [];

    if (!readiness.canSubmit.value) {
      submitError.value = 'Please complete every required step before submitting.';
      return false;
    }

    isSubmitting.value = true;
    state.isSubmitting = true;
    try {
      // Build the submit payload. We send the full appointment_step_form_data
      // plus the resolved service price (the server re-computes and rejects
      // mismatches; this is purely a sanity hint).
      const selectedId = parseInt(state.appointment_step_form_data.selected_service || 0, 10);
      const svc = state.services.find(s => parseInt(s.serviceId, 10) === selectedId);
      const full = svc ? effectivePrice(state, svc.servicePrice, svc.serviceId) : 0;
      // The amount charged NOW: the order total by default, or a deposit when a
      // partial-payment feature is active (server applies the identical filter
      // and anti-tampers against this). Inert in Lite (returns `full`).
      const price = payableAmount(state, full, selectedId);

      const payload = {
        ...state.appointment_step_form_data,
        service_price_without_currency: price,
      };

      // Per plan §3.3 — give add-ons a chance to mutate the payload or
      // cancel the submission entirely.
      let cancelled = false;
      bus && bus.emit('bp-v3:before-submit', {
        instanceId: state.instanceId,
        payload,
        cancel() { cancelled = true; },
      });
      if (cancelled) {
        submitError.value = 'Submission was cancelled by an add-on.';
        return false;
      }

      const resp = await api.submit(payload);

      if (resp.status === 200 && resp.ok && resp.data) {
        const env = resp.data;
        if (env.variant === 'redirect_url' && env.redirect_data) {
          submitOk.value    = true;
          redirectUrl.value = env.redirect_data;
          state.submitOk     = true;
          state.redirectUrl  = env.redirect_data;
          bus && bus.emit('bp-v3:after-submit', { instanceId: state.instanceId, response: env, ok: true });
          // The client may want to redirect; we expose the URL and let App
          // perform the redirect after a short delay.
          return true;
        }
        if (env.variant === 'pending_payment') {
          // PayPal (and other off-site gateways) — server has staged the
          // booking as pending and is waiting for the gateway flow to
          // complete. We DO NOT treat this as a final success here: the
          // caller (SummaryStep.bookViaPayPal etc.) is responsible for
          // running the gateway-specific round-trip before flipping the
          // user into the "Thank You" state. Returning the envelope as
          // the resolved value lets the caller pick up `entry_id` /
          // `gateway` and call `api.paypalValidate(...)`.
          bus && bus.emit('bp-v3:after-submit', { instanceId: state.instanceId, response: env, ok: false, pendingPayment: true });
          return env;
        }
        // Unknown success variant — surface as error.
        submitError.value = env.error_message || 'Unexpected response from server.';
        bus && bus.emit('bp-v3:after-submit', { instanceId: state.instanceId, response: env, ok: false });
        return false;
      }

      // Error envelope.
      const err = resp.error || { code: 'bp_v3_unknown', message: 'Submission failed.' };
      submitError.value = err.message || err.code || 'Submission failed.';
      state.submitError  = submitError.value;
      if (resp.data && Array.isArray(resp.data.failed_gates)) {
        failedGates.value = resp.data.failed_gates;
      }
      bus && bus.emit('bp-v3:after-submit', {
        instanceId: state.instanceId,
        response: { status: resp.status, error: err, data: resp.data },
        ok: false,
      });
      return false;
    } catch (e) {
      submitError.value = e.message || 'Network error.';
      state.submitError  = submitError.value;
      return false;
    } finally {
      isSubmitting.value = false;
      state.isSubmitting = false;
    }
  }

  return { isSubmitting, submitError, submitOk, redirectUrl, failedGates, submit };
}
