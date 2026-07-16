/**
 * useReadiness — client-side mirror of `ValidationService::pure_gate_*` (PHP).
 *
 * These predicates are advisory — the server re-runs every gate at submit
 * time (per §M0.9.C, "client + server duplication is by design"). The
 * client uses them to enable/disable buttons and to compute step
 * navigability.
 */
import { computed } from 'vue';
import { effectivePrice } from '../utils/pricing.js?v=2';
import { payableAmount } from '../utils/payable.js?v=1';
import { isSelectedDayService } from '../utils/service.js';

export function useReadiness(state) {
  const gateService = computed(() => {
    const id = parseInt(state.appointment_step_form_data.selected_service || 0, 10);
    if (!id) return false;
    return state.services.some(s => parseInt(s.serviceId, 10) === id);
  });

  const gateDatetime = computed(() => {
    const d = String(state.appointment_step_form_data.selected_date || '');
    const t = String(state.appointment_step_form_data.selected_start_time || '');
    if (!/^\d{4}-\d{2}-\d{2}$/.test(d)) return false;
    if (isSelectedDayService(state)) return true;
    if (!/^\d{2}:\d{2}(:\d{2})?$/.test(t)) return false;
    return true;
  });

  const gateBasicDetails = computed(() => {
    // Generic Pro seam: when an add-on owns the Basic Details field rendering
    // (it sets `config.suppressDefaultBasicFields`), Lite cannot reason about
    // the field shapes (containers / repeater / extra types) — so it defers to
    // the reactive validity flag the add-on maintains. Defaults to valid until
    // the add-on says otherwise. Inert on a Lite-only render (flag unset).
    if (state.config && state.config.suppressDefaultBasicFields) {
      return state.proBasicDetailsValid !== false;
    }
    for (const f of state.customer_form_fields) {
      if (!f.fieldRequired) continue;
      const key = f.vModelValue;
      if (!key) continue;
      const v = state.appointment_step_form_data[key];
      if (v == null || v === '' || (Array.isArray(v) && v.length === 0)) return false;
    }
    return true;
  });

  const gatePayment = computed(() => {
    // Zero-payable bypass: when there is nothing to charge (a free service, or a
    // feature that drops the amount payable to 0 — e.g. a redeemed Service
    // Package), no gateway needs to be chosen, mirroring the Summary's own
    // `total > 0` gate that hides the payment block. The paid-booking path below
    // is unchanged. Mirrors the server FILTER_PAYABLE_AMOUNT via the same util.
    const fd = state.appointment_step_form_data;
    const sid = parseInt(fd.selected_service || 0, 10);
    if (sid) {
      const svc = (state.services || []).find(s => parseInt(s.serviceId, 10) === sid);
      const full = svc ? effectivePrice(state, svc.servicePrice, svc.serviceId) : 0;
      if (payableAmount(state, full, sid) <= 0) return true;
    }

    const sel = String(state.appointment_step_form_data.selected_payment_method || '');
    if (!sel) return false;
    const methods = (state.config && Array.isArray(state.config.payment_methods)) ? state.config.payment_methods : [];
    return methods.some(m => m.id === sel);
  });

  const gateTerms = computed(() => {
    const termsField = state.customer_form_fields.find(f => f.fieldName === 'terms_and_conditions');
    if (!termsField || !termsField.fieldRequired) return true;
    const v = state.appointment_step_form_data.appointment_terms_conditions;
    return Array.isArray(v) && v.includes(true);
  });

  // Step-level: can the user navigate INTO this step right now?
  function canEnterStep(stepDescriptor) {
    if (!stepDescriptor || !Array.isArray(stepDescriptor.entry_gates)) return true;
    for (const g of stepDescriptor.entry_gates) {
      if (g === 'service') { if (!gateService.value) return false; continue; }
      if (g === 'datetime') { if (!gateDatetime.value) return false; continue; }
      if (g === 'basic_details') { if (!gateBasicDetails.value) return false; continue; }
      // Generic gate for a Pro-injected step (e.g. Staff Member): the gate
      // token equals a step id, and that step may declare a `gate_field` — an
      // `appointment_step_form_data` key that must be non-empty for the gate to
      // pass. This lets Pro add a gating step without Lite knowing the feature.
      // Lite ships no such steps, so this branch is never hit on a Lite render.
      const gateStep = state.steps.find((s) => s.id === g);
      if (gateStep && gateStep.gate_field) {
        const v = state.appointment_step_form_data[gateStep.gate_field];
        if (v == null || v === '' || (Array.isArray(v) && v.length === 0)) return false;
      }
    }
    return true;
  }

  const canRenderSummary = computed(() => gateService.value && gateDatetime.value);

  const canSubmit = computed(() => {
    return gateService.value && gateDatetime.value && gateBasicDetails.value
      && gatePayment.value && gateTerms.value;
  });

  return {
    gateService, gateDatetime, gateBasicDetails, gatePayment, gateTerms,
    canEnterStep, canRenderSummary, canSubmit,
  };
}
