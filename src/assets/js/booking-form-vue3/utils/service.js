/**
 * Service selection helpers for the Vue3 booking form.
 */

export function selectedService(state) {
  const fd = (state && state.appointment_step_form_data) || {};
  const id = parseInt(fd.selected_service || 0, 10);
  if (!id || !Array.isArray(state && state.services)) return null;
  return state.services.find((svc) => parseInt(svc.serviceId, 10) === id) || null;
}

export function selectedServiceDurationUnit(state) {
  const fd = (state && state.appointment_step_form_data) || {};
  if (
    Array.isArray(fd.selected_services) &&
    fd.selected_services.length >= 2 &&
    Object.prototype.hasOwnProperty.call(fd, 'multi_service_duration_unit')
  ) {
    return String(fd.multi_service_duration_unit || '');
  }
  const svc = selectedService(state);
  if (svc && Object.prototype.hasOwnProperty.call(svc, 'serviceDurationUnit')) {
    return String(svc.serviceDurationUnit || '');
  }
  return String(fd.selected_service_duration_unit || '');
}

export function selectedServiceDurationValue(state) {
  const fd = (state && state.appointment_step_form_data) || {};
  if (
    Array.isArray(fd.selected_services) &&
    fd.selected_services.length >= 2 &&
    fd.multi_service_duration_unit === 'd'
  ) {
    return Math.max(0, parseInt(fd.multi_service_duration_days || 0, 10) || 0);
  }
  const svc = selectedService(state);
  if (svc && Object.prototype.hasOwnProperty.call(svc, 'serviceDurationVal')) {
    return Math.max(0, parseInt(svc.serviceDurationVal || 0, 10) || 0);
  }
  return Math.max(0, parseInt(fd.selected_service_duration || 0, 10) || 0);
}

export function isSelectedDayService(state) {
  return selectedServiceDurationUnit(state) === 'd';
}

function parseYmdLocal(ymd) {
  const s = String(ymd || '');
  const m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
  if (!m) return null;
  const d = new Date(parseInt(m[1], 10), parseInt(m[2], 10) - 1, parseInt(m[3], 10));
  return Number.isNaN(d.getTime()) ? null : d;
}

function formatYmdLocal(d) {
  const y = d.getFullYear();
  const mo = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${mo}-${day}`;
}

export function selectedDayServiceDateRange(state) {
  if (!isSelectedDayService(state)) return [];
  const fd = (state && state.appointment_step_form_data) || {};
  const start = parseYmdLocal(fd.selected_date);
  if (!start) return [];

  const duration = Math.max(1, parseInt(selectedServiceDurationValue(state) || 1, 10) || 1);
  const days = [];
  for (let i = 0; i < duration; i++) {
    const d = new Date(start.getFullYear(), start.getMonth(), start.getDate());
    d.setDate(d.getDate() + i);
    days.push(formatYmdLocal(d));
  }
  return days;
}

export function syncSelectedServiceDuration(state, svc) {
  if (!state || !state.appointment_step_form_data) return;
  const fd = state.appointment_step_form_data;
  if (!svc) {
    fd.selected_service_duration = '';
    fd.selected_service_duration_unit = '';
    return;
  }
  fd.selected_service_duration = String(parseInt(svc.serviceDurationVal || 0, 10) || 0);
  fd.selected_service_duration_unit = String(svc.serviceDurationUnit || '');
}

export function dayServiceEndDate(startYmd, durationDays) {
  const s = String(startYmd || '');
  const start = parseYmdLocal(startYmd);
  if (!start) return '';
  const duration = Math.max(1, parseInt(durationDays || 1, 10) || 1);
  if (duration === 1) return s;
  const d = new Date(start.getFullYear(), start.getMonth(), start.getDate());
  d.setDate(d.getDate() + duration - 1);
  return formatYmdLocal(d);
}
