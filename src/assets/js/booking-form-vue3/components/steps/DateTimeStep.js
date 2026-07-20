/**
 * DateTimeStep — V-Calendar date picker + bucketed time-slot grid.
 *
 * Markup mirrors the released `[bookingpress_form]` step-2 panel 1:1
 * (`.bpa-front-default-card` / `.bpa-front-dc--body` /
 *  `.bpa-front-module--date-and-time` / `.bpa-front--dt__wrapper` /
 *  `.bpa-front--dt__col` / `.bpa-front--dt__calendar` /
 *  `.bpa-front--dt__calendar-host.vc-light` / `.bpa-front--dt__time-slots`
 *  / `.bpa-front--dt__ts-heading` / `.bpa-front--dt__ts-body` /
 *  `.bpa-front--dt__ts-body--row` / `.bpa-front--dt-ts__sub-heading` /
 *  `.bpa-front--dt__ts-body--items` / `.bpa-front--dt__ts-body--item`).
 *
 * The calendar widget is the project's bundled V-Calendar v3 wrapper
 * (`window.BpVCalendar.mountDatePicker(host, props, handlers)`) — the
 * native `<input type="date">` fallback is gone.
 *
 * Time-slot rows are sourced from `useTimeslots.buckets.value` which the
 * composable already groups by morning/afternoon/evening/night using the
 * server-supplied boundary hours.
 *
 * All copy comes from `state.strings.*` so admin renames propagate.
 */
import { computed, inject, onBeforeUnmount, onMounted, ref, watch, nextTick } from 'vue';
import { isSelectedDayService, selectedDayServiceDateRange } from '../../utils/service.js?v=2';
import { useRovingTabindex } from '../../composables/useA11yNav.js?v=1';

const ICON_ERROR =
  '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">' +
    '<path d="M12 7c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1s-1-.45-1-1V8c0-.55.45-1 1-1zm-.01-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm1-3h-2v-2h2v2z"/>' +
  '</svg>';

const ICON_ARROW_LEFT =
  '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24">' +
    '<rect fill="none" height="24" width="24"/>' +
    '<path d="M9.71,18.29L9.71,18.29c0.39-0.39,0.39-1.02,0-1.41L5.83,13H21c0.55,0,1-0.45,1-1v0c0-0.55-0.45-1-1-1H5.83l3.88-3.88 c0.39-0.39,0.39-1.02,0-1.41l0,0c-0.39-0.39-1.02-0.39-1.41,0L2.71,11.3c-0.39,0.39-0.39,1.02,0,1.41l5.59,5.59 C8.68,18.68,9.32,18.68,9.71,18.29z"/>' +
  '</svg>';

const ICON_ARROW_RIGHT =
  '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24">' +
    '<rect fill="none" height="24" width="24"/>' +
    '<path d="M14.29,5.71L14.29,5.71c-0.39,0.39-0.39,1.02,0,1.41L18.17,11H3c-0.55,0-1,0.45-1,1v0c0,0.55,0.45,1,1,1h15.18l-3.88,3.88 c-0.39,0.39-0.39,1.02,0,1.41l0,0c0.39,0.39,1.02,0.39,1.41,0l5.59-5.59c0.39-0.39,0.39-1.02,0-1.41L15.7,5.71 C15.32,5.32,14.68,5.32,14.29,5.71z"/>' +
  '</svg>';

/** Convert "YYYY-MM-DD" → Date at local-midnight, or null. */
function ymdToDate(ymd) {
  if (!ymd || typeof ymd !== 'string') return null;
  const m = ymd.match(/^(\d{4})-(\d{2})-(\d{2})/);
  if (!m) return null;
  return new Date(parseInt(m[1], 10), parseInt(m[2], 10) - 1, parseInt(m[3], 10));
}

/** Convert Date → "YYYY-MM-DD" using local fields. */
function dateToYmd(d) {
  if (!(d instanceof Date) || isNaN(d.getTime())) return '';
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

export default {
  name: 'DateTimeStep',
  setup() {
    const state     = inject('state');
    const nav       = inject('nav');
    const timeslots = inject('timeslots');

    const calendarHost = ref(null);
    const vcalendarBridge = { app: null, vm: null };

    const today   = (state.config && state.config.today)   || dateToYmd(new Date());
    const maxDate = (state.config && state.config.maxDate) || '';

    const selectedDate = computed({
      get() { return String(state.appointment_step_form_data.selected_date || ''); },
      set(v) {
        state.appointment_step_form_data.selected_date = String(v);
        state.appointment_step_form_data.selected_start_time = '';
        state.appointment_step_form_data.selected_end_time   = '';
        // Clear the overnight "real" date carried from a previously picked slot
        // (Pro) so a fresh date with no slot picked falls back to the calendar
        // date on the Summary. Inert for Lite (the field is never set there).
        state.appointment_step_form_data.selected_actual_date = '';
        // Legacy `.__sm` parity: picking a day on mobile returns the user
        // to the time-slots view. No-op on desktop (state isn't read there).
        displayResponsiveCalendar.value = '0';
        // Lazy month load — cheap cache hit when the month was already
        // fetched by `fetchInitial` or the progressive walker. Triggers a
        // fetch only when the user picked a date in an uncached month.
        if (v) timeslots.fetchForDate(v);
      },
    });

    const selectedStart = computed(() => String(state.appointment_step_form_data.selected_start_time || ''));
    const isDayService = computed(() => isSelectedDayService(state));

    // --- Mobile `.__sm` view toggle (legacy parity) -------------------------
    //
    // On phones (≤576px) and tablets (768–991px) the legacy form shows the
    // time-slots list first, with a full-width "date trigger" button on top
    // showing the selected date; tapping it swaps the slots for the calendar,
    // and picking a day swaps back. Desktop CSS ignores these classes and
    // always shows both columns. `booking-form.css` already ships the
    // `.bpa-sm-show-slots` / `.bpa-sm-show-calendar` visibility rules and the
    // `.bpa-front--dt__ts-sm-back-btn` styling — this state just drives them.
    //   '0' → slots view (date-trigger button on top)
    //   '1' → calendar view
    const displayResponsiveCalendar = ref('0');

    const dtWrapperClass = computed(() => {
      // Day services render no slots column at all, so on mobile the
      // calendar must stay visible — pin the calendar-view class.
      if (isDayService.value) return 'bpa-sm-show-calendar';
      return displayResponsiveCalendar.value === '1' ? 'bpa-sm-show-calendar' : 'bpa-sm-show-slots';
    });

    /** Human-readable label for the mobile date-trigger button
     *  ("July 6, 2026"), falling back to the raw YMD when Intl is
     *  unavailable. Mirrors the intermediate form's formatter. */
    const selectedDateLabel = computed(() => {
      const raw = String(state.appointment_step_form_data.selected_date || '');
      if (!raw) return '';
      const ymd = raw.slice(0, 10);
      const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(ymd);
      if (!m) return raw;
      const d = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
      if (isNaN(d.getTime())) return raw;
      // WP locales are underscore-form (`en_US`); Intl wants BCP 47.
      const locale = String((state.config && state.config.locale) || 'en').replace(/_/g, '-');
      const opts = { year: 'numeric', month: 'long', day: 'numeric' };
      try {
        return new Intl.DateTimeFormat(locale, opts).format(d);
      } catch (_) {
        try { return new Intl.DateTimeFormat('en', opts).format(d); } catch (__) { return ymd; }
      }
    });

    function openResponsiveCalendar() {
      displayResponsiveCalendar.value = '1';
      // Legacy parity: the `.__sm` module recreated its calendar on every
      // open (v-if). Here the host stays in the DOM, but on phones the
      // initial mount ran inside a display:none column — V-Calendar v3
      // bails / mis-measures on hidden containers — so remount now that
      // the column is visible. Desktop never reaches this (button hidden).
      nextTick(() => {
        unmountCalendar();
        mountCalendar();
      });
    }

    // Previous-step / next-step names — power the "Go Back" + "Next: Basic Details" labels.
    const prevStepName = computed(() => {
      const cur = state.steps.find(s => s.id === state.currentTab);
      if (!cur || !cur.previous_step) return '';
      const prev = state.steps.find(s => s.id === cur.previous_step);
      return (prev && prev.tab_name) || '';
    });
    const nextStepName = computed(() => {
      const cur = state.steps.find(s => s.id === state.currentTab);
      if (!cur || !cur.next_step) return '';
      const next = state.steps.find(s => s.id === cur.next_step);
      return (next && next.tab_name) || '';
    });

    // Whether a *visible* previous step exists (nav walks past hidden
    // descriptors, e.g. a hidden-first Service step). Gates the back button:
    // when Date & Time is the first visible step there is nowhere to go back
    // to and the button must not render.
    const hasPrev = nav.hasPrev;

    // Whether a slot can be picked. Defaults to the slot's own availability; an
    // add-on (e.g. Waiting List) may override via the generic, inert-in-Lite
    // `bookingpress_form_v3_slot_selectable` filter so a FULL slot can still be
    // selected (as a waiting slot). With no callback this is exactly
    // `slot.is_available`, so normal available/disabled behaviour is unchanged.
    function slotSelectable(slot) {
      let selectable = !!(slot && slot.is_available);
      const hooks = (typeof window !== 'undefined' && window.wp && window.wp.hooks) || null;
      if (hooks && typeof hooks.applyFilters === 'function') {
        selectable = !!hooks.applyFilters('bookingpress_form_v3_slot_selectable', selectable, {
          slot, state, instanceId: state.instanceId,
        });
      }
      return selectable;
    }

    // Optional decoration text for a slot (e.g. Waiting List badge / position).
    // Empty by default — Lite renders no badge. Generic, inert-in-Lite seam.
    function slotBadge(slot) {
      const hooks = (typeof window !== 'undefined' && window.wp && window.wp.hooks) || null;
      if (hooks && typeof hooks.applyFilters === 'function') {
        const out = hooks.applyFilters('bookingpress_form_v3_slot_badge', '', {
          slot, state, instanceId: state.instanceId,
        });
        if (typeof out === 'string') return out;
      }
      return '';
    }

    function slotItemClass(slot, idx) {
      let cls = ['bpa-front--dt__ts-body--item', 'bpa_focusable'];
      const isDisabled = !slotSelectable(slot);
      const isSelected = String(slot.start_time) === selectedStart.value;
      if (isDisabled) cls.push('__bpa-is-disabled');
      else if (isSelected) cls.push('__bpa-is-selected');
      // Staggered fade-in: the released form's PHP backend stamps
      // `bpa-front--ts-item-<n>` (1..19) on each slot, and
      // `bookingpress_front.css` defines matching animation-delay rules.
      // Cap at 19 to align with the released ceiling — slots beyond that
      // share the last delay value (still animated, just less visible).
      const n = Math.min(((idx || 0) % 19) + 1, 19);
      cls.push('bpa-front--ts-item-' + n);
      // Generic, inert-in-Lite class seam — lets an add-on tag a slot (e.g. the
      // Waiting List `bpa-is-waiting-slot` style). No callback → array returned
      // unchanged, so Lite classes are exactly as before.
      const hooks = (typeof window !== 'undefined' && window.wp && window.wp.hooks) || null;
      if (hooks && typeof hooks.applyFilters === 'function') {
        const out = hooks.applyFilters('bookingpress_form_v3_slot_class', cls, {
          slot, state, instanceId: state.instanceId,
        });
        if (Array.isArray(out)) cls = out;
      }
      return cls.join(' ');
    }

    // Compose the slot's display time. The clock format (12h/24h/WP) is already
    // baked into formatted_start_time/_end_time server-side. Two GENERIC,
    // add-on-overridable display knobs decide the rest, defaulting to Lite's
    // released "start - end" rendering when unset:
    //   - config.slotShowEndTime (bool, default true) — show the end time.
    //   - config.slotTimeSeparator (string, default ' - ') — start/end joiner.
    // Pro's "Time slot styling" feature sets these via FILTER_INITIAL_STATE
    // (e.g. ' to ' separator, or start-only); Lite ships neither, so a
    // Lite-only install renders exactly as before.
    function slotTimeLabel(slot) {
      const start = (slot && slot.formatted_start_time) || '';
      if (state.config.slotShowEndTime === false) return start;
      const sep = state.config.slotTimeSeparator || ' - ';
      return start + sep + ((slot && slot.formatted_end_time) || '');
    }

    function pickSlot(slot) {
      // Selectability runs through the generic seam: by default this is
      // `slot.is_available` (unchanged), but an add-on (Waiting List) may allow a
      // FULL slot to be picked as a waiting slot.
      if (!slotSelectable(slot)) return;
      state.appointment_step_form_data.selected_start_time = String(slot.start_time);
      state.appointment_step_form_data.selected_end_time   = String(slot.end_time);
      // Generic, inert-in-Lite action: lets an add-on react to the picked slot
      // (e.g. Waiting List sets `is_waiting_list` from `slot.is_waiting_slot`).
      {
        const hooks = (typeof window !== 'undefined' && window.wp && window.wp.hooks) || null;
        if (hooks && typeof hooks.doAction === 'function') {
          hooks.doAction('bookingpress_form_v3_slot_picked', slot, { state, instanceId: state.instanceId });
        }
      }
      // Overnight (Pro): a slot whose window runs into the next day carries its
      // real start date in `store_service_date` (e.g. a 01:00–02:00 slot picked
      // under 15 Jun is really 16 Jun). Persist it so the Summary shows the day
      // the appointment actually falls on; cleared for plain slots so they fall
      // back to the calendar date. Inert for Lite (slots have no
      // `store_service_date`). The backend re-derives this independently.
      state.appointment_step_form_data.selected_actual_date = String(slot.store_service_date || '');

      // Released-form parity: clicking a slot auto-advances to the next
      // step (Basic Details), mirroring the auto-advance on service
      // selection. Clear any pending error toast on the way out.
      clearError();
      nav.goNext();
    }

    // Toast error surfaced when the user clicks Next without picking a
    // date or a time slot — released-form parity.
    const errorMsg = ref('');
    let errorTimer = 0;

    function setError(msg) {
      errorMsg.value = String(msg || '');
      if (errorTimer) clearTimeout(errorTimer);
      if (errorMsg.value) {
        errorTimer = setTimeout(() => { errorMsg.value = ''; errorTimer = 0; }, 5000);
      }
    }

    function clearError() {
      if (errorTimer) clearTimeout(errorTimer);
      errorTimer = 0;
      errorMsg.value = '';
    }

    function next() {
      if (!selectedDate.value) {
        setError(state.strings.no_appointment_date_error);
        return;
      }
      if (isDayService.value) {
        clearError();
        nav.goNext();
        return;
      }
      if (!selectedStart.value) {
        setError(state.strings.no_appointment_time_error);
        return;
      }
      clearError();
      nav.goNext();
    }

    function prev() {
      clearError();
      nav.goPrev();
    }

    // --- Keyboard navigation for the time-slot radiogroup ------------------
    //
    // The slots render bucketed (morning/afternoon/evening/night) but form a
    // SINGLE radiogroup, so the roving tabindex runs over the flattened list:
    // Arrow keys walk chronologically across bucket boundaries, Home/End jump
    // to the first/last slot of the day, Enter/Space picks (and advances —
    // same as click). Full/disabled slots stay focusable so screen-reader
    // users hear them announced as unavailable instead of finding a gap.
    const flatSlots = computed(() => {
      const out = [];
      const b = timeslots.buckets.value || {};
      for (const k of Object.keys(b)) {
        const arr = Array.isArray(b[k]) ? b[k] : [];
        for (const s of arr) out.push(s);
      }
      return out;
    });

    /** bucket key → index of its first slot in `flatSlots`. */
    const bucketOffsets = computed(() => {
      const offsets = {};
      const b = timeslots.buckets.value || {};
      let acc = 0;
      for (const k of Object.keys(b)) {
        offsets[k] = acc;
        acc += (Array.isArray(b[k]) ? b[k].length : 0);
      }
      return offsets;
    });

    function slotGlobalIndex(bucket, i) {
      return (bucketOffsets.value[bucket] || 0) + i;
    }

    const slotRov = useRovingTabindex({
      count: () => flatSlots.value.length,
      selectedIndex: () =>
        flatSlots.value.findIndex((s) => String(s.start_time) === selectedStart.value),
      onActivate: (i) => {
        const slot = flatSlots.value[i];
        if (slot) pickSlot(slot); // pickSlot() itself refuses unavailable slots.
      },
    });

    const tsHeadingId = 'bp-v3-ts-heading-' + state.instanceId;
    function bucketHeadingId(bucket) {
      return 'bp-v3-ts-bucket-' + state.instanceId + '-' + bucket;
    }

    // --- V-Calendar mini-app lifecycle --------------------------------------

    let bridgeRetryTimer = null;
    let bridgeRetryDeadline = 0;
    let disabledMarkerObserver = null;
    let disabledSweepRaf = 0;

    /**
     * VCalendar v3 applies `.is-disabled` inconsistently for future days
     * outside its initially-rendered window (this comes from v3 dropping
     * the v2 `available-dates` whitelist). The legacy form sidesteps this
     * by manually stamping `.is-disabled` on every `.vc-day-content`
     * whose YMD is missing from `availableDatesYmd` (or present in
     * `blockedDatesYmd`). We mirror that here so weekends, holidays,
     * past dates and fully-booked days all paint with the released
     * disabled-cell colour.
     */
    /**
     * V-Calendar v3 renders its prev/next month arrows (and the month title
     * button) with icon-only content — no accessible name, which is an axe
     * "button-name" critical. Stamp localizable labels; idempotent, re-run
     * by the same MutationObserver as the disabled sweep so re-renders
     * (month transitions) keep the labels.
     */
    function labelCalendarChrome() {
      const host = calendarHost.value;
      if (!host) return;
      const prevBtn = host.querySelector('.vc-arrow.vc-prev');
      const nextBtn = host.querySelector('.vc-arrow.vc-next');
      const prevLabel = (state.strings && state.strings.previous_month_text) || 'Previous month';
      const nextLabel = (state.strings && state.strings.next_month_text) || 'Next month';
      if (prevBtn && prevBtn.getAttribute('aria-label') !== prevLabel) prevBtn.setAttribute('aria-label', prevLabel);
      if (nextBtn && nextBtn.getAttribute('aria-label') !== nextLabel) nextBtn.setAttribute('aria-label', nextLabel);
    }

    function normalizeDisabledDayMarkers() {
      const host = calendarHost.value;
      if (!host) return;
      labelCalendarChrome();

      const availSet  = timeslots.availableDatesYmd.value || new Set();
      const blockSet  = timeslots.blockedDatesYmd.value   || new Set();
      const selectedYmd = String(state.appointment_step_form_data.selected_date || '');
      const maxYmd    = String((state.config && state.config.maxDate) || '');
      const selectedRangeYmd = new Set(selectedDayServiceDateRange(state));
      const selectedStartIsSelectable = !!selectedYmd && timeslots.isDateSelectable(selectedYmd);

      const days = host.querySelectorAll('.vc-day');
      for (let i = 0; i < days.length; i++) {
        const day = days[i];
        
        const contentEls = day.querySelectorAll('.vc-day-content');
        if (!contentEls.length) continue;

        const contentElsContent = contentEls.innerHTML;
        if( contentElsContent ){
          contentEls.innerHTML = `<span>${contentElsContent}</span>`;
        }

        // YMD: try `data-date` first (set by VCalendar in some configs),
        // fall back to the cell's `id-YYYY-MM-DD` class.
        let ymd = day.getAttribute('data-date') || '';
        if (!ymd) {
          const cls = day.className || '';
          const m = cls.match(/\bid-(\d{4}-\d{2}-\d{2})\b/);
          if (m) ymd = m[1];
        }
        if (!ymd) continue;
        ymd = ymd.slice(0, 10);

        // Released-form spec: every date is disabled by default; only
        // dates whose MONTH has been fetched AND that appear in
        // `availableDatesYmd` are enabled. Overflow days from a
        // neighbouring month that's still being fetched stay disabled
        // until that month's payload lands, at which point this sweep
        // re-runs (via the `availableDatesYmd` watcher) and lights them
        // up. The only exception is a date inside the already-selected
        // day-service range; that range is validated by the selected
        // start date and may span visible overflow days.
        const monthLoaded = timeslots.isMonthLoaded(ymd);

        // `monthLoaded` + `availableDatesYmd` + the `maxDate` window are
        // authoritative. We intentionally do NOT honour V-Calendar's own
        // `.vc-disabled` / `aria-disabled` markers — V-Calendar v3 tags
        // overflow days (e.g. June 1-6 shown at the bottom of the May
        // view) with those markers at first render, and would lock them
        // disabled even after the June payload lands. Past-dates are
        // excluded from `availableDatesYmd` by the backend; the
        // `maxDate` upper bound is enforced HERE in lock-step with the
        // click-gate in `isDateSelectable`. Without this, days inside a
        // loaded month that fall past `maxDate` (e.g. May 22-31 when
        // today is May 21 and the booking window ends a year later)
        // would have working_details rows from the backend and pass
        // the `availSet.has(ymd)` check, rendering as enabled cells
        // even though the click-gate refuses them.
        let shouldDisable = false;
        if (blockSet.has(ymd)) shouldDisable = true;
        if (!monthLoaded) shouldDisable = true;
        if (monthLoaded && !availSet.has(ymd)) shouldDisable = true;
        if (maxYmd && ymd > maxYmd) shouldDisable = true;

        const isDayRangeDate =
          isDayService.value &&
          selectedStartIsSelectable &&
          selectedRangeYmd.has(ymd) &&
          !blockSet.has(ymd) &&
          (!maxYmd || ymd <= maxYmd);
        if (isDayRangeDate) shouldDisable = false;
        const isSelected = !shouldDisable && selectedYmd && (ymd === selectedYmd || isDayRangeDate);
        const capacityLabel = (!shouldDisable && isDayService.value && timeslots.dayCapacityLabel)
          ? String(timeslots.dayCapacityLabel(ymd) || '')
          : '';

        let labelEl = day.querySelector(':scope > .vc-day-content > .bpa-front-dt__day-slot-label');
        if (capacityLabel) {
          if (!labelEl) {
            labelEl = document.createElement('span');
            labelEl.className = 'bpa-front-dt__day-slot-label';
            labelEl.setAttribute('aria-hidden', 'true');
            day.querySelector('.vc-day-content').appendChild(labelEl);
          }
          if (!day.classList.contains('__bp-v3-has-capacity-label')) day.classList.add('__bp-v3-has-capacity-label');
          if (labelEl.textContent !== capacityLabel) labelEl.textContent = capacityLabel;
        } else if (labelEl && labelEl.parentNode) {
          labelEl.parentNode.removeChild(labelEl);
          if (day.classList.contains('__bp-v3-has-capacity-label')) day.classList.remove('__bp-v3-has-capacity-label');
        } else if (!capacityLabel && day.classList.contains('__bp-v3-has-capacity-label')) {
          day.classList.remove('__bp-v3-has-capacity-label');
        }

        for (let c = 0; c < contentEls.length; c++) {
          const el = contentEls[c];
          if (shouldDisable) {
            if (!el.classList.contains('is-disabled')) el.classList.add('is-disabled');
            if (el.getAttribute('aria-disabled') !== 'true') el.setAttribute('aria-disabled', 'true');
          } else {
            if (el.classList.contains('is-disabled')) el.classList.remove('is-disabled');
            if (el.getAttribute('aria-disabled') === 'true') el.setAttribute('aria-disabled', 'false');
          }

          // Selected-marker stamp. Authoritative class we control — used
          // by CSS to apply the green pill + white text regardless of
          // V-Calendar v3's internal class structure. Today's neutraliser
          // is scoped `:not(.__bp-v3-selected)` so the selected styling
          // wins when today is the chosen date.
          if (isSelected) {
            if (!el.classList.contains('__bp-v3-selected')) el.classList.add('__bp-v3-selected');
          } else {
            if (el.classList.contains('__bp-v3-selected')) el.classList.remove('__bp-v3-selected');
          }
        }
      }
    }

    /** Wire a MutationObserver that re-runs the sweep on every calendar render. */
    function ensureDisabledMarkerObserver() {
      const host = calendarHost.value;
      if (!host || disabledMarkerObserver) return;
      if (typeof MutationObserver !== 'function') return;
      disabledMarkerObserver = new MutationObserver(() => {
        if (disabledSweepRaf) return;
        const schedule = (typeof requestAnimationFrame === 'function')
          ? requestAnimationFrame : (cb) => setTimeout(cb, 0);
        disabledSweepRaf = schedule(() => {
          disabledSweepRaf = 0;
          normalizeDisabledDayMarkers();
        });
      });
      disabledMarkerObserver.observe(host, {
        childList:        true,
        subtree:          true,
        attributes:       true,
        attributeFilter:  ['class', 'aria-disabled'],
      });
      // Run once now so already-rendered cells get the markers.
      normalizeDisabledDayMarkers();
    }

    function teardownDisabledMarkerObserver() {
      if (disabledMarkerObserver) {
        try { disabledMarkerObserver.disconnect(); } catch (_e) { /* ignore */ }
        disabledMarkerObserver = null;
      }
      if (disabledSweepRaf && typeof cancelAnimationFrame === 'function') {
        try { cancelAnimationFrame(disabledSweepRaf); } catch (_e) { /* ignore */ }
      }
      disabledSweepRaf = 0;
    }

    function mountCalendar() {
      if (vcalendarBridge.vm) return;
      // Generic seam (inert in Lite): an add-on can replace the whole Date &
      // Time body with its own UI by flipping `state.customDateTimeActive`. When
      // set, the default calendar+grid wrapper is `v-if`-hidden, so V-Calendar
      // must NOT mount (it bails on hidden containers + the retry loop would spin).
      if (state.customDateTimeActive) return;
      const host = calendarHost.value;
      const Bridge = (typeof window !== 'undefined') ? window.BpVCalendar : null;

      // The bp-vcalendar.js IIFE sets `window.BpVCalendar` on load. The
      // bootstrap module side-effect-imports it, but a slow connection /
      // race against the user clicking Next immediately could still leave
      // it unset for a moment. Poll every 50ms for up to 3s, then give up.
      if (!host || !Bridge || typeof Bridge.mountDatePicker !== 'function') {
        if (!bridgeRetryDeadline) bridgeRetryDeadline = Date.now() + 3000;
        if (Date.now() < bridgeRetryDeadline) {
          bridgeRetryTimer = setTimeout(mountCalendar, 50);
        }
        return;
      }
      bridgeRetryDeadline = 0;

      const min = ymdToDate(today) || new Date();
      // Read `config.maxDate` fresh: `fetchInitial` syncs it from the timeslot
      // payload's `max_available_date` (which Pro may narrow per-service, e.g.
      // the "Service Expiration Date" cap) and resolves before this mount runs.
      const maxYmdNow = String((state.config && state.config.maxDate) || maxDate);
      const max = ymdToDate(maxYmdNow) || new Date(min.getFullYear() + 2, min.getMonth(), min.getDate());

      const initialProps = {
        mode:           'date',
        modelValue:     ymdToDate(selectedDate.value),
        minDate:        min,
        maxDate:        max,
        // VCalendar v3 paints these days as `.is-disabled` and the released
        // CSS greys them. We pass the inverse-of-available list so Sat /
        // Sun / holidays / past-dates / fully-booked days are all greyed
        // by the same rule. The onUpdate gate is the final fence.
        disabledDates:  timeslots.disabledDatesForCalendar.value.slice(),
        firstDayOfWeek: parseInt((state.config && state.config.firstDayOfWeek) || 1, 10) || 1,
        locale:         (state.config && state.config.locale) || undefined,
        isRequired:     true,
        masks: {
          // Match released parity: 3-letter weekdays, "Month YYYY" title.
          weekdays: 'WWW',
          title:    'MMMM YYYY',
        },
      };

      const handlers = {
        onUpdate(v) {
          if (!(v instanceof Date) || isNaN(v.getTime())) {
            // Reject empty / invalid emission — push the previously-accepted
            // date back into the picker.
            if (vcalendarBridge.vm) {
              vcalendarBridge.vm.p.modelValue = selectedDate.value
                ? ymdToDate(selectedDate.value)
                : null;
            }
            return;
          }
          const ymd = dateToYmd(v);
          // Server-supplied gates: must be available, not blocked, not past max.
          if (!timeslots.isDateSelectable(ymd)) {
            if (vcalendarBridge.vm) {
              vcalendarBridge.vm.p.modelValue = selectedDate.value
                ? ymdToDate(selectedDate.value)
                : null;
            }
            return;
          }
          if (ymd && ymd !== selectedDate.value) {
            selectedDate.value = ymd;
          }
          let visibleTimeslot = document.querySelector('.bpa-front--dt__time-slots');
          if( visibleTimeslot && visibleTimeslot.scrollTop > 0 ){
            visibleTimeslot.scrollTop = 0;
          }
        },
        // VCalendar v3 emits `did-move` when the user navigates between
        // months. Use `fetchMonthForNav` which gates the full-step
        // loader on the fetch and fire-and-forgets a 2-month look-ahead
        // after the navigated month lands.
        onMonthPage(page) {
          if (!page) return;
          const p = Array.isArray(page) ? page[0] : page;
          if (!p) return;
          const y = parseInt(p.year, 10);
          const m = parseInt(p.month, 10);
          if (y && m) timeslots.fetchMonthForNav(y, m);
        },
      };

      const mounted = Bridge.mountDatePicker(host, initialProps, handlers);
      if (!mounted) return;
      vcalendarBridge.app = mounted.app;
      vcalendarBridge.vm  = mounted.vm;

      // Now that the calendar is in the DOM, wire the disabled-marker
      // sweep so weekends/holidays/past-dates render with `.is-disabled`.
      ensureDisabledMarkerObserver();
    }

    function unmountCalendar() {
      if (bridgeRetryTimer) {
        clearTimeout(bridgeRetryTimer);
        bridgeRetryTimer = null;
      }
      bridgeRetryDeadline = 0;
      teardownDisabledMarkerObserver();
      if (vcalendarBridge.app && typeof vcalendarBridge.app.unmount === 'function') {
        try { vcalendarBridge.app.unmount(); } catch (_e) { /* ignore */ }
      }
      vcalendarBridge.app = null;
      vcalendarBridge.vm  = null;
    }

    onMounted(() => {
      // Released-form parity: kick off the initial fetch. The calendar
      // is hidden behind a loader (`!isInitialLoaded`) until this
      // resolves, then the wrapper is rendered by `v-if` and the
      // watcher below mounts the V-Calendar bridge onto the now-visible
      // host. Mounting earlier breaks because V-Calendar v3 bails out
      // on `display: none` containers.
      timeslots.fetchInitial();
    });

    // When the initial fetch resolves, the wrapper is rendered and the
    // calendar host becomes available — mount the bridge on the next
    // tick so the host has paint dimensions for V-Calendar to measure.
    //
    // When `isInitialLoaded` flips back to FALSE (the step reloads for a new
    // availability context — e.g. a Custom Service Duration change invalidates
    // the cache and refetches, or a service/staff change), the `v-if` wrapper
    // and its calendar host are DESTROYED. We must tear the bridge down here:
    // otherwise the stale `vcalendarBridge.vm` makes `mountCalendar()`
    // early-return when the wrapper (and a brand-new host element) re-renders,
    // leaving the calendar column blank until the whole step is remounted.
    watch(
      () => timeslots.isInitialLoaded.value,
      (loaded) => {
        if (loaded) nextTick(() => nextTick(mountCalendar));
        else unmountCalendar();
      },
      { immediate: true }
    );

    // Mount/unmount the calendar when the generic `customDateTimeActive` flag
    // toggles (an add-on taking over / releasing the Date & Time body). Inert in
    // Lite (the flag never changes from its unset default).
    watch(
      () => state.customDateTimeActive,
      (active) => {
        if (active) {
          unmountCalendar();
        } else if (timeslots.isInitialLoaded.value) {
          nextTick(() => nextTick(mountCalendar));
        }
      }
    );

    onBeforeUnmount(unmountCalendar);

    // Push date updates from the v-model side back into the calendar
    // (preselection from URL params, etc.). Also re-stamp the
    // `__bp-v3-selected` marker so the previously-selected cell loses
    // it and the newly-selected one gains it.
    watch(selectedDate, (next) => {
      if (vcalendarBridge.vm) {
        vcalendarBridge.vm.p.modelValue = next ? ymdToDate(next) : null;
      }
      nextTick(normalizeDisabledDayMarkers);
    });

    // Push fresh disabled-dates into the picker whenever a new month
    // lands. VCalendar v3 reads `vm.p.disabledDates` reactively. Also
    // re-run the manual `.is-disabled` sweep so newly-arrived months are
    // immediately reflected on cells already in the DOM.
    watch(
      () => timeslots.disabledDatesForCalendar.value,
      (next) => {
        if (vcalendarBridge.vm) {
          vcalendarBridge.vm.p.disabledDates = next.slice();
        }
        // Defer to next tick — gives VCalendar a chance to re-render first.
        nextTick(normalizeDisabledDayMarkers);
      },
      { deep: false }
    );

    // Re-run the sweep when the available/blocked sets change directly
    // (e.g. a lazy month-fetch lands after the user has already paged the
    // calendar to a different month).
    watch(
      [() => timeslots.availableDatesYmd.value, () => timeslots.blockedDatesYmd.value],
      () => { nextTick(normalizeDisabledDayMarkers); }
    );

    // Follow `config.maxDate` reactively. `fetchInitial` narrows it per-service
    // from the timeslot payload (e.g. Pro's "Service Expiration Date" cap), and
    // because the bridge mounts asynchronously after that fetch, push the new
    // upper bound into the live V-Calendar prop and re-run the disabled-cell
    // sweep so dates past the cap are greyed and navigation stops there.
    // Inert for a Lite-only install (the value never changes from its seed).
    watch(
      () => (state.config && state.config.maxDate) || '',
      (v) => {
        const d = ymdToDate(String(v || ''));
        if (d && vcalendarBridge.vm) {
          vcalendarBridge.vm.p.maxDate = d;
        }
        nextTick(normalizeDisabledDayMarkers);
      }
    );

    return {
      state,
      today,
      maxDate,
      selectedDate,
      selectedStart,
      isDayService,
      dtWrapperClass,
      selectedDateLabel,
      openResponsiveCalendar,
      prevStepName,
      nextStepName,
      slotItemClass,
      slotTimeLabel,
      slotSelectable,
      slotBadge,
      pickSlot,
      slotRov,
      slotGlobalIndex,
      tsHeadingId,
      bucketHeadingId,
      next,
      prev,
      timeslots,
      nav,
      hasPrev,
      calendarHost,
      errorMsg,
      ICON_ARROW_LEFT,
      ICON_ARROW_RIGHT,
      ICON_ERROR,
    };
  },
  template: `
    <div class="bpa-front-default-card">
      <!-- Validation toast — mirrors the released form's
           .bpa-front-toast-notification element. Surfaces when Next is
           pressed without a date or slot picked. -->
      <div v-if="errorMsg" class="bpa-front-toast-notification --bpa-error" :aria-label="errorMsg" role="alert">
        <div class="bpa-front-tn-body">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 7c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1s-1-.45-1-1V8c0-.55.45-1 1-1zm-.01-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm1-3h-2v-2h2v2z"/></svg>
          <p>{{ errorMsg }}</p>
        </div>
      </div>

      <div class="bpa-front-dc--body">
        <div class="bpa-front-module-container bpa-front-module--date-and-time">
          <div class="bpa-front-module-heading" role="heading" aria-level="2" tabindex="-1" data-bp-step-heading>{{ state.strings.datetime_step_name }}</div>

          <div class="bp-v3-slot" data-bp-v3-slot="datetime-step:above-calendar" :data-bp-v3-instance="state.instanceId"></div>

          <!-- Released-form parity: while the first month payload is being
               fetched, the entire date/time area is covered by the
               BookingPress animated logo loader. The calendar + slot
               panel only become visible once isInitialLoaded flips. -->
          <div v-if="!state.customDateTimeActive && !timeslots.isInitialLoaded.value" class="bpa-full-container-loader">
            <div class="bpa-front-loader-container">
              <div class="bpa-front-loader bpa-back-loader" role="status" aria-live="polite">
                <span class="screen-reader-text">{{ state.strings.no_service_text ? '' : '' }}</span>
              </div>
            </div>
          </div>

          <div v-if="!state.customDateTimeActive && timeslots.isInitialLoaded.value" class="bpa-front--dt__wrapper bpa-front--dt__wrapper--positioned" :class="dtWrapperClass">
            <!-- Cross-month navigation loader. Same visual treatment as
                 the initial loader, but overlaid on the calendar+slots
                 wrapper so V-Calendar isn't unmounted (preserves the
                 user's just-navigated-to month). -->
            <div v-if="timeslots.isFetchingNavMonth.value" class="bpa-full-container-loader bpa-full-container-loader--overlay">
              <div class="bpa-front-loader-container">
                <div class="bpa-front-loader bpa-back-loader" role="status" aria-live="polite"></div>
              </div>
            </div>
            <div class="bpa-front--dt__col bpa-front--dt__col--calendar bpa-front-dt-col__is-visible">
              <!-- V-Calendar v3 supplies its own APG date-grid keyboard model
                   (arrows move days, PageUp/PageDown months, Alt+PageUp/Down
                   years, Home/End week boundaries) and manages the tab stop
                   on the focusable day cell — so the wrapper is a labelled
                   group, NOT an extra tab stop (the old tabindex="0" was a
                   dead stop that trapped a Tab press with no behavior). -->
              <div class="bpa-front--dt__calendar" :class="isDayService ? 'bpa-front-v-cal__is-only-days' : ''" role="group" :aria-label="state.strings.calendar_text || 'Booking calendar'">
                <div class="bpa-front--dt__calendar-host vc-light" ref="calendarHost"></div>
              </div>
            </div>

            <div v-if="!isDayService" class="bpa-front--dt__col bpa-front--dt__col--slots bpa-front-dt-col__is-visible">
              <!-- Generic, inert-in-Lite slot at the TOP of the time-slots column.
                   An add-on (e.g. Custom Service Duration) mounts a control here —
                   e.g. the duration selector that must sit above the slot grid.
                   Empty in Lite (0-height), so layout is unchanged. Mounting here
                   natively removes the need for add-ons to physically relocate a
                   node into this Vue-controlled column (which raced the initial
                   timeslot fetch on the first visit). -->
              <div class="bp-v3-slot" data-bp-v3-slot="datetime-step:above-timeslots" :data-bp-v3-instance="state.instanceId"></div>
              <div class="bpa-front--dt__time-slots" :class="'undefined' != typeof state.appointment_step_form_data.enable_custom_service_duration && 'true' == state.appointment_step_form_data.enable_custom_service_duration ? 'bpa-front--dt__time-slots--has-custom-duration' : ''">
                <!-- Mobile/tablet-only "date trigger" button — legacy \`.__sm\`
                     parity. Shows the selected date; tapping swaps the slots
                     list for the calendar. Hidden on desktop by
                     booking-form.css (display:none outside the two legacy
                     \`.__sm\` breakpoints). -->
                <div class="bpa-front--dt__ts-sm-back-btn">
                  <button
                    type="button"
                    class="bpa-front-btn bpa_focusable"
                    :aria-label="selectedDateLabel || state.strings.select_date_text || 'Select date'"
                    @click="openResponsiveCalendar"
                  >
                    <span>
                      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>
                      <label class="bpa-front--dt__ts-sm-back-btn-label">{{ selectedDateLabel || state.strings.select_date_text || 'Select date' }}</label>
                    </span>
                  </button>
                </div>

                <div class="bpa-front--dt__ts-heading">
                  <div class="bpa-front-module-heading" role="heading" aria-level="3" :id="tsHeadingId">{{ state.strings.timeslot_text }}</div>
                </div>

                <p v-if="timeslots.error.value" class="bpa-front-error">{{ timeslots.error.value }}</p>

                <div v-if="timeslots.isLoading.value" class="bpa-front-loader-container" role="status">
                  <div class="bpa-front-loader"></div>
                </div>

                <template v-else-if="!selectedDate">
                  <div class="bpa-front__no-timeslots-body">
                    <p class="bpa-front-ntb__val">{{ state.strings.select_date_title }}</p>
                  </div>
                </template>

                <template v-else-if="timeslots.currentSlots.value.length === 0">
                  <div class="bpa-front__no-timeslots-body">
                    <p class="bpa-front-ntb__val">{{ state.strings.no_timeslot_available }}</p>
                  </div>
                </template>

                <template v-else>
                  <div class="bpa-front--dt__ts-body" role="radiogroup" :aria-labelledby="tsHeadingId">
                    <div
                      v-for="(slots, bucket) in timeslots.buckets.value"
                      :key="bucket"
                      v-show="slots.length > 0"
                      class="bpa-front--dt__ts-body--row"
                      :data-bucket="bucket"
                      role="group"
                      :aria-labelledby="bucket !== 'all' ? bucketHeadingId(bucket) : null"
                    >
                      <!-- 'all' = flat list when "Hide time slot grouping" is on
                           (config.hideTimeslotGrouping) — no Morning/…/Night heading. -->
                      <div v-if="bucket !== 'all'" class="bpa-front--dt-ts__sub-heading" :id="bucketHeadingId(bucket)">{{ state.strings[bucket + '_text'] }}</div>
                      <div class="bpa-front--dt__ts-body--items">
                        <div
                          v-for="(slot, sIdx) in slots"
                          :key="selectedDate + '|' + bucket + '|' + slot.start_time"
                          :class="slotItemClass(slot, sIdx)"
                          role="radio"
                          :aria-checked="selectedStart === slot.start_time ? 'true' : 'false'"
                          :aria-disabled="!slotSelectable(slot) ? 'true' : null"
                          :tabindex="slotRov.tabindexFor(slotGlobalIndex(bucket, sIdx))"
                          :ref="(el) => slotRov.setItemRef(el, slotGlobalIndex(bucket, sIdx))"
                          @focus="slotRov.onItemFocus(slotGlobalIndex(bucket, sIdx))"
                          @keydown="slotRov.onKeydown($event, slotGlobalIndex(bucket, sIdx))"
                          @click="pickSlot(slot)"
                        >
                          <span>{{ slotTimeLabel(slot) }}</span>
                          <!-- Optional add-on badge (e.g. Waiting List label / position).
                               Empty in Lite (no callback) → not rendered. -->
                          <span v-if="slotBadge(slot)" class="bpa-front--ts-slot-badge" :class="[('undefined' != typeof slot.is_waiting_slot && slot.is_waiting_slot ? 'bpa-front__waiting-counter' : '')]">{{ slotBadge(slot) }}</span>
                          <!-- Capacity counter — dormant in Lite (state.config.showSlotCapacity
                               is undefined). Pro flips it on via the FILTER_INITIAL_STATE seam
                               when "Hide Capacity Information" is disabled, and supplies the
                               configurable slot_left_text string. Shows remaining capacity per
                               slot, which reduces as bookings are made. -->
                          <span
                            v-if="state.config.showSlotCapacity && slot.remaining_capacity != null && ( 'undefined' == typeof slot.is_waiting_slot || !slot.is_waiting_slot )"
                            class="bpa-front--ts-capacity-counter"
                          >{{ slot.remaining_capacity }} {{ state.strings.slot_left_text }}</span>
                          <span v-if="slot.is_overnight" class="bpa-front--ts-next-day"> {{ state.strings.next_day_text || '+1 day' }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </div>

          <div class="bp-v3-slot" data-bp-v3-slot="datetime-step:below-timeslots" :data-bp-v3-instance="state.instanceId"></div>
        </div>
      </div>

      <div class="bpa-front-dc--footer">
        <div class="bpa-front-tabs--foot">
          <button
            v-if="hasPrev"
            type="button"
            class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--borderless bpa_focusable"
            :aria-label="state.strings.goback_button"
            @click="prev()"
          >
            <span v-html="ICON_ARROW_LEFT"></span>&nbsp;{{ state.strings.goback_button }}
          </button>
          <button
            type="button"
            class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--primary bpa_focusable"
            :aria-label="state.strings.next_button + ' ' + nextStepName"
            @click="next()"
          >
            {{ state.strings.next_button }}&nbsp;<strong>{{ nextStepName }}</strong>
            <span v-html="ICON_ARROW_RIGHT"></span>
          </button>
        </div>
      </div>
    </div>
  `,
};
