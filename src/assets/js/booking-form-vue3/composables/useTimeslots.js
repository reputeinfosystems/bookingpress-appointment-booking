/**
 * useTimeslots — month-level timeslot cache with progressive loading.
 *
 * Released-form parity:
 *   - When the user enters the date/time step, ONE request fetches the
 *     entire current month's `working_details` (one row per date that has
 *     slots) via `/form-v3/month-details`.
 *   - A progressive walker pre-fetches a couple of months ahead so the
 *     calendar shows "filled in" days when the user pages forward.
 *   - Picking a date is then a pure cache read — no extra REST round-trip.
 *   - Navigating the calendar to an uncached month triggers a single
 *     fetch via `onMonthPage`.
 *
 * Disabled-date strategy:
 *   The backend's `v_calendar_blocked_dates` only lists explicit days-off
 *   (holidays) — it does NOT list weekdays that have no working hours
 *   (Saturdays / Sundays for a Mon–Fri service). To paint those greyed,
 *   we derive `disabledDatesForCalendar` as the *inverse* of
 *   `availableDates` over each loaded month: every day of a cached month
 *   that doesn't appear in `working_details` is treated as disabled. This
 *   means weekends, days-off, fully-booked days and past dates all paint
 *   the same way the released form paints them, with no backend change.
 *
 * Public reactive surface:
 *   - `isLoading`, `isInitialLoaded`, `error`            → refs
 *   - `blockedDates`, `disabledDatesForCalendar`         → computed<Date[]>
 *   - `blockedDatesYmd`, `availableDatesYmd`             → computed<Set<string>>
 *   - `currentSlots`, `buckets`                          → computed
 *
 * Methods:
 *   - `fetchMonth(year, month)`     — idempotent month fetch
 *   - `fetchInitial()`              — current month + a few ahead
 *   - `fetchForDate(ymd)`           — fetch the month that contains `ymd`
 *   - `isDateSelectable(ymd)`       — click-gate predicate
 *   - `invalidateForService(id)`    — drop cache for a service
 */
import { ref, computed } from 'vue';
import { isSelectedDayService } from '../utils/service.js';

/** "YYYY-MM-DD" → Date at local midnight, or null. */
function ymdToDate(ymd) {
  if (!ymd || typeof ymd !== 'string') return null;
  const m = ymd.match(/^(\d{4})-(\d{2})-(\d{2})/);
  if (!m) return null;
  return new Date(parseInt(m[1], 10), parseInt(m[2], 10) - 1, parseInt(m[3], 10));
}

/** Date → "YYYY-MM-DD" using LOCAL fields. */
function dateToYmd(d) {
  if (!(d instanceof Date) || isNaN(d.getTime())) return '';
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

/** Number of months to pre-fetch ahead of `today` on entering step 2. */
const PROGRESSIVE_AHEAD = 2;

/**
 * Generic seam: let an add-on reshape the outgoing timeslot/month request body
 * before it is posted (the request-side analog of the PHP FILTER_TIMESLOT_PAYLOAD
 * response filter). Inert in Lite — no callback is registered, so the body is
 * returned untouched. Pro's "Show time as per client timezone" hooks this to add
 * `client_timezone` / `client_timezone_offset` / `client_dst` so the server can
 * convert the slot grid into the visitor's timezone.
 *
 * @param {object} body  The request body about to be posted.
 * @param {object} state The instance form state (for instanceId / context).
 * @param {string} kind  'initial' | 'month'.
 * @returns {object} The (possibly reshaped) body.
 */
function applyTimeslotRequestFilter(body, state, kind) {
  const hooks = (typeof window !== 'undefined' && window.wp && window.wp.hooks) || null;
  if (hooks && typeof hooks.applyFilters === 'function') {
    const out = hooks.applyFilters('bookingpress_form_v3_timeslot_request', body, {
      state,
      instanceId: state && state.instanceId,
      kind,
    });
    if (out && typeof out === 'object') return out;
  }
  return body;
}

/**
 * Let availability-changing add-ons contribute a compact, stable cache-key
 * suffix. The request filter and this signature filter receive the same state,
 * so an add-on can key every request-shaping input without core knowing its
 * private state model. Inert when no add-on registers a callback.
 */
function applyTimeslotCacheSignature(state) {
  const hooks = (typeof window !== 'undefined' && window.wp && window.wp.hooks) || null;
  if (!hooks || typeof hooks.applyFilters !== 'function') return '';
  const out = hooks.applyFilters('bookingpress_form_v3_timeslot_cache_signature', '', {
    state,
    instanceId: state && state.instanceId,
  });
  return typeof out === 'string' ? out : '';
}

export function useTimeslots(state, api, bus) {
  const isLoading       = ref(false);
  const isInitialLoaded = ref(false);
  /** True while a cross-month navigation fetch is in flight — UI uses
   *  this to display the same full-step loader the initial fetch shows. */
  const isFetchingNavMonth = ref(false);
  const error           = ref('');

  // Single plain-object reactive store. Shape:
  //   { [cacheKey]: { [`${year}-${MM}`]: payload } }
  // `cacheKey` is normally the service id. Day services add request-shaping
  // inputs (staff, quantity, cart-held items) so date-level availability cannot
  // reuse a month that was calculated against a different capacity context.
  // Replacing the whole object on every merge guarantees Vue picks up the
  // change in computed deps (more reliable than reactive(new Map())).
  const monthsByService = ref({});

  /** In-flight requests, keyed `${sid}|${YYYY-MM}` to de-dupe. */
  const inflight = new Map();

  // Effective service id the slot grid loads for. Normally the chosen service.
  // GENERIC seam: a Pro feature can set `config.slotServiceDriverId` so the grid
  // can load BEFORE a service is picked — needed when the Booking Form Sequence
  // places the Service step AFTER Date & Time (the grid is then built from the
  // selected staff's / global shift, with a global slot length, independent of
  // any service). When the driver is set it WINS over `selected_service`, so the
  // grid stays the same serviceless "carrier" grid even after the visitor later
  // picks a service (legacy service-after-datetime parity). Inert in Lite: the
  // driver is unset, so this is exactly `selected_service` as before.
  function slotServiceId() {
    const driver = parseInt((state.config && state.config.slotServiceDriverId) || 0, 10);
    if (driver > 0) return driver;
    return parseInt(state.appointment_step_form_data.selected_service || 0, 10);
  }

  function dayServiceCartCacheSignature() {
    if (!isSelectedDayService(state)) return '';
    const fd = state.appointment_step_form_data || {};
    const parts = [];
    const items = Array.isArray(fd.cart_items) ? fd.cart_items : [];
    const editIdx = parseInt(fd.cart_item_edit_index, 10);
    items.forEach((item, idx) => {
      if (idx === editIdx || !item || !item.selected_date) return;
      const unit = String(item.selected_service_duration_unit || '');
      if (unit !== 'd') return;
      parts.push([
        parseInt(item.selected_service || 0, 10) || 0,
        String(item.selected_date || ''),
        String(item.selected_end_date || ''),
        parseInt(item.selected_staff_member_id || 0, 10) || 0,
        parseInt(item.bookingpress_selected_bring_members || 0, 10) || 1,
        String(item.selected_start_time || ''),
        String(item.selected_end_time || ''),
      ].join(':'));
    });
    const quantity = parseInt(fd.bookingpress_selected_bring_members || 0, 10) || 0;
    if (!parts.length && quantity <= 0) return '';
    return `qty:${quantity}|cart:${parts.join(',')}`;
  }

  function timeslotCacheKey(sid) {
    const base = String(parseInt(sid, 10) || '');
    if (!base) return '';
    const fd = state.appointment_step_form_data || {};
    const suffix = dayServiceCartCacheSignature();
    const addonSignature = applyTimeslotCacheSignature(state);
    const contextSuffix = [suffix, addonSignature].filter(Boolean).join('|');
    if (isSelectedDayService(state) && !fd.is_any_staff_selected) {
      const staffId = parseInt(fd.selected_staff_member_id || 0, 10);
      if (staffId > 0) return `${base}|staff:${staffId}${contextSuffix ? '|' + contextSuffix : ''}`;
    }
    return `${base}${contextSuffix ? '|' + contextSuffix : ''}`;
  }

  function getServiceMap() {
    const sid = slotServiceId();
    const cacheKey = sid ? timeslotCacheKey(sid) : '';
    return (cacheKey && monthsByService.value[cacheKey]) || {};
  }

  // ---- Computed: blocked / available / disabled -----------------------------

  const blockedDatesYmd = computed(() => {
    const set = new Set();
    const sm  = getServiceMap();
    for (const ymKey in sm) {
      const p = sm[ymKey];
      if (p && Array.isArray(p.v_calendar_blocked_dates)) {
        for (const d of p.v_calendar_blocked_dates) {
          set.add(String(d).slice(0, 10));
        }
      }
    }
    return set;
  });

  const availableDatesYmd = computed(() => {
    const set = new Set();
    const sm  = getServiceMap();
    for (const ymKey in sm) {
      const p  = sm[ymKey];
      const wd = (p && p.working_details) || {};
      for (const k in wd) {
        if (Array.isArray(wd[k]) && wd[k].length > 0) set.add(k);
      }
    }
    return set;
  });

  const blockedDates = computed(() => {
    const out = [];
    blockedDatesYmd.value.forEach((ymd) => { const d = ymdToDate(ymd); if (d) out.push(d); });
    return out;
  });

  /**
   * Inverse-of-available over every loaded month. This is what we pass to
   * V-Calendar as `disabledDates` — it covers weekends, holidays, past
   * dates, and fully-booked days in a single derivation.
   */
  const disabledDatesForCalendar = computed(() => {
    const out = [];
    const sm  = getServiceMap();
    const available = availableDatesYmd.value;
    for (const ymKey in sm) {
      const [y, m] = ymKey.split('-').map((x) => parseInt(x, 10));
      if (!y || !m) continue;
      const lastDay = new Date(y, m, 0).getDate();
      for (let day = 1; day <= lastDay; day++) {
        const ymd = `${y}-${String(m).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        if (!available.has(ymd)) {
          out.push(new Date(y, m - 1, day));
        }
      }
    }
    return out;
  });

  // ---- Computed: slots for the selected date --------------------------------

  const currentDayRows = computed(() => {
    const date = String(state.appointment_step_form_data.selected_date || '');
    if (!date) return [];
    return dayRowsForDate(date);
  });

  function dayRowsForDate(date) {
    date = String(date || '');
    if (!date) return [];
    const sm = getServiceMap();
    for (const ymKey in sm) {
      const wd = (sm[ymKey] && sm[ymKey].working_details) || {};
      if (Array.isArray(wd[date])) return wd[date];
    }
    return [];
  }

  function dayCapacityLabel(ymd) {
    if (!state.config || !state.config.showSlotCapacity) return '';
    const fd = state.appointment_step_form_data || {};
    if (fd.is_any_staff_selected) return '';
    const rows = dayRowsForDate(ymd);
    for (const row of rows) {
      if (!row || !row.is_day_service || row.remaining_capacity == null) continue;
      const remaining = parseInt(row.remaining_capacity, 10);
      if (isNaN(remaining)) continue;
      return String(remaining) + ' ' + (state.strings.slot_left_text || '');
    }
    return '';
  }

  const currentSlots = computed(() => {
    if (isSelectedDayService(state)) return [];
    return currentDayRows.value;
  });

  const buckets = computed(() => {
    const cfg = state.config || {};
    const a = parseInt(cfg.afternoonStartHour || 12, 10);
    const e = parseInt(cfg.eveningStartHour   || 17, 10);
    const n = parseInt(cfg.nightStartHour     || 21, 10);

    const out = { morning: [], afternoon: [], evening: [], night: [] };
    for (const slot of currentSlots.value) {
      if (slot.is_overnight) { out.night.push(slot); continue; }
      const h = parseInt(String(slot.start_time || '0:0').slice(0, 2), 10);
      if (h < a) out.morning.push(slot);
      else if (h < e) out.afternoon.push(slot);
      else if (h < n) out.evening.push(slot);
      else out.night.push(slot);
    }
    // "Hide time slot grouping" (customize toggle, surfaced by a Pro feature
    // via config.hideTimeslotGrouping; unset in Lite): collapse the four
    // buckets into ONE heading-less list, same order as the grouped render
    // (morning→night concatenated, overnight slots last) — mirrors the legacy
    // flat branch in Pro's appointment_booking_form.php (v-else of the
    // hide_time_slot_grouping check). DateTimeStep suppresses the sub-heading
    // for the 'all' bucket key.
    if (cfg.hideTimeslotGrouping) {
      return { all: out.morning.concat(out.afternoon, out.evening, out.night) };
    }
    return out;
  });

  // ---- Fetch primitives ----------------------------------------------------

  /**
   * Merge a month payload into the reactive store. Replaces `monthsByService`
   * with a structurally-shared shallow clone so Vue tracks the change.
   */
  function mergeMonth(cacheKey, ymKey, payload) {
    const cur  = monthsByService.value;
    const next = { ...cur };
    next[cacheKey]  = { ...(next[cacheKey] || {}), [ymKey]: payload };
    monthsByService.value = next;
  }

  /**
   * Fetch month payload (idempotent + dedupes concurrent requests).
   *
   * @param {number} year
   * @param {number} month  1-12
   * @returns {Promise<object|null>}
   */
  function fetchMonth(year, month) {
    const sid = slotServiceId();
    if (!sid) return Promise.resolve(null);
    const cacheKey    = timeslotCacheKey(sid);
    const ymKey       = `${year}-${String(month).padStart(2, '0')}`;
    const sm          = monthsByService.value[cacheKey] || {};
    if (sm[ymKey]) return Promise.resolve(sm[ymKey]);

    const inflightKey = `${cacheKey}|${ymKey}`;
    if (inflight.has(inflightKey)) return inflight.get(inflightKey);

    isLoading.value = true;
    error.value     = '';

    const from = `${ymKey}-01`;
    const monthBody = applyTimeslotRequestFilter({
      service_id:     sid,
      from_date:      from,
      selected_year:  year,
      selected_month: month,
    }, state, 'month');
    const req  = api.monthDetails(monthBody).then((resp) => {
      inflight.delete(inflightKey);
      if (!resp.ok) {
        error.value = (resp.error && resp.error.message) || 'Failed to load timeslots.';
        return null;
      }
      mergeMonth(cacheKey, ymKey, resp.data || {});
      bus && bus.emit('bp-v3:after-month-load', {
        instanceId: state.instanceId, year, month,
        slots: (resp.data && resp.data.working_details) || {},
      });
      return resp.data;
    }).catch((e) => {
      inflight.delete(inflightKey);
      error.value = e && e.message ? e.message : 'Network error.';
      return null;
    }).finally(() => {
      if (inflight.size === 0) isLoading.value = false;
    });

    inflight.set(inflightKey, req);
    return req;
  }

  /**
   * Initial fetch on entering step 2.
   *
   * Released-form parity:
   *   1. POST `/form-v3/timeslots` with `selected_date = state.config.today`
   *      (the SERVER date, not the device clock). The backend walks
   *      month-by-month starting from today and returns the first month
   *      that has at least one available date, plus a `next_month_date`
   *      cursor pointing at the first day of the FOLLOWING month and
   *      the absolute `max_available_date` (today + 365 days).
   *   2. Cache that month's payload, then auto-select the earliest
   *      available date so the slot panel renders immediately.
   *   3. Fire-and-forget the next `PROGRESSIVE_AHEAD` month-details
   *      requests using the cursor so the calendar progressively lights
   *      up as the responses land.
   *
   * `isInitialLoaded` flips true only after step 2 resolves — the UI
   * hides the calendar / slots behind a full-step loader until then.
   */
  async function fetchInitial() {
    const sid = slotServiceId();
    if (!sid) return;
    const cacheKey = timeslotCacheKey(sid);

    // Re-entering the step for a DIFFERENT availability context (service/staff
    // changed → new cache key, or the cache was invalidated) must show the same
    // full-step loader the very first visit shows — otherwise the stale
    // "no timeslot available" empty-state flashes while the reload is in flight.
    // A revisit with cached months keeps `isInitialLoaded` true (no loader).
    const cachedMonths = monthsByService.value[cacheKey] || {};
    if (Object.keys(cachedMonths).length === 0) {
      isInitialLoaded.value = false;
    }

    isLoading.value = true;
    error.value     = '';

    const todayYmd = (state.config && state.config.today) || dateToYmd(new Date());

    try {
      const initialBody = applyTimeslotRequestFilter({
        service_id:    sid,
        category_id:   parseInt(state.appointment_step_form_data.selected_category || 0, 10),
        selected_date: todayYmd,
      }, state, 'initial');
      const resp = await api.timeslots(initialBody);

      if (!resp.ok) {
        error.value = (resp.error && resp.error.message) || 'Failed to load timeslots.';
        return;
      }

      const data       = resp.data || {};
      const foundMonth = String(data.found_month || '');
      const maxDate    = String(data.max_available_date || '');

      // Keep the calendar's hard upper bound in lock-step with the server's
      // per-service maximum bookable date. Lite ships `max_available_date` as
      // today + 1 year (identical to the initial `config.maxDate`), so this is
      // a no-op for a Lite-only install; Pro can narrow it per service (e.g.
      // the "Service Expiration Date" caps it), and the calendar/date-gates all
      // read `config.maxDate`, so syncing here is what makes the cap visible.
      if (maxDate && state.config) {
        state.config.maxDate = maxDate;
      }

      if (foundMonth) {
        mergeMonth(cacheKey, foundMonth, data);

        // Auto-select the earliest available date if none picked yet —
        // mirrors the released form's behaviour.
        const cur = String(state.appointment_step_form_data.selected_date || '');
        if (!cur) {
          const available = Array.from(availableDatesYmd.value).sort();
          if (available.length > 0) {
            state.appointment_step_form_data.selected_date = available[0];
          }
        }
      }

      // Progressive walker: queue up to PROGRESSIVE_AHEAD subsequent
      // months in the background using the cursor. Each fetch is
      // fire-and-forget; the calendar's `:disabledDates` watcher will
      // re-evaluate cells once each response lands.
      let cursor = String(data.next_month_date || '');
      for (let i = 0; i < PROGRESSIVE_AHEAD && cursor; i++) {
        const y = parseInt(cursor.slice(0, 4), 10);
        const m = parseInt(cursor.slice(5, 7), 10);
        if (!y || !m) break;
        fetchMonth(y, m); // not awaited — runs in parallel

        // Advance the cursor locally to the next month's first day, stop
        // when we cross the max-available-date envelope.
        const nm = (m % 12) + 1;
        const ny = y + (m === 12 ? 1 : 0);
        const next = `${ny}-${String(nm).padStart(2, '0')}-01`;
        cursor = (!maxDate || next <= maxDate) ? next : '';
      }
    } catch (e) {
      error.value = e && e.message ? e.message : 'Network error.';
    } finally {
      isLoading.value     = false;
      isInitialLoaded.value = true;
    }
  }

  /**
   * Predicate for the `onUpdate` click-gate. Strict by default — every
   * date is rejected unless its month is loaded AND it appears in
   * `availableDatesYmd`. Mirrors the released-form spec: "by default
   * all days are disabled, only available dates from the response are
   * clickable".
   */
  function isDateSelectable(ymd) {
    if (!ymd) return false;
    const sid = slotServiceId();
    if (!sid) return false;
    const cacheKey = timeslotCacheKey(sid);
    const year  = parseInt(ymd.slice(0, 4), 10);
    const month = parseInt(ymd.slice(5, 7), 10);
    const ymKey = `${year}-${String(month).padStart(2, '0')}`;
    const sm    = monthsByService.value[cacheKey] || {};

    // Month not yet fetched — reject the click. The DOM sweep paints
    // these cells `.is-disabled` so they shouldn't be clickable in the
    // first place, but the gate is the authoritative fence.
    if (!sm[ymKey]) return false;

    // Month loaded — must be in the available set (Sat/Sun fall through
    // because they're absent from `working_details`, and any explicit
    // blocked day will also be missing).
    if (!availableDatesYmd.value.has(ymd)) return false;

    // Past the configured max date?
    const max = String((state.config && state.config.maxDate) || '');
    if (max && ymd > max) return false;

    return true;
  }

  /**
   * Reactive set of `YYYY-MM` month keys currently cached for the
   * selected service. The DOM sweep uses this to decide whether to
   * enforce the strict disabled-gate on a given day or leave it alone
   * (overflow days from an as-yet-unfetched neighbouring month must NOT
   * be marked disabled — they paint as regular cells until their month
   * lands).
   */
  const loadedMonthsYmKey = computed(() => {
    const set = new Set();
    const sm  = getServiceMap();
    for (const ymKey in sm) set.add(ymKey);
    return set;
  });

  function isMonthLoaded(ymd) {
    const s = String(ymd || '');
    if (s.length < 7) return false;
    return loadedMonthsYmKey.value.has(s.slice(0, 7));
  }

  function invalidateForService(serviceId) {
    const sid = parseInt(serviceId, 10);
    if (!sid) return;
    const next = { ...monthsByService.value };
    const prefix = String(sid) + '|';
    let changed = false;
    for (const key in next) {
      if (key === String(sid) || key.indexOf(prefix) === 0) {
        delete next[key];
        changed = true;
      }
    }
    if (!changed) return;
    monthsByService.value = next;
  }

  /** Back-compat / lazy-load alias: ensure the month containing `ymd` is cached. */
  function fetchForDate(ymd) {
    const s = String(ymd || '');
    if (!s) return Promise.resolve(null);
    const year  = parseInt(s.slice(0, 4), 10);
    const month = parseInt(s.slice(5, 7), 10);
    if (!year || !month) return Promise.resolve(null);
    return fetchMonth(year, month);
  }

  /**
   * Fetch a month when the user navigates to it via the calendar — set
   * `isFetchingNavMonth` so the UI can paint the full-step loader, then
   * fire-and-forget pre-fetch the next two months once the target month
   * lands. Mirrors the released form's progressive walker behavior
   * inside the date/time step.
   *
   * If the target month is already cached this is a no-op (no loader,
   * no extra fetches).
   */
  async function fetchMonthForNav(year, month) {
    const sid = slotServiceId();
    if (!sid || !year || !month) return;
    const cacheKey = timeslotCacheKey(sid);

    const ymKey = `${year}-${String(month).padStart(2, '0')}`;
    const sm    = monthsByService.value[cacheKey] || {};
    if (sm[ymKey]) return; // cache hit — nothing to do

    // Find the max-available envelope from any already-cached payload.
    let maxDate = '';
    for (const k in sm) {
      const p = sm[k];
      if (p && typeof p.max_available_date === 'string' && p.max_available_date > maxDate) {
        maxDate = p.max_available_date;
      }
    }
    if (!maxDate && state.config && state.config.maxDate) {
      maxDate = String(state.config.maxDate);
    }

    isFetchingNavMonth.value = true;
    try {
      await fetchMonth(year, month);
    } finally {
      isFetchingNavMonth.value = false;
    }

    // Background pre-fetch the next two months from this navigation
    // point — capped by `max_available_date`. Each is fire-and-forget
    // so they don't gate the UI.
    const next1Month = (month % 12) + 1;
    const next1Year  = year + (12 === month ? 1 : 0);
    const next2Month = (next1Month % 12) + 1;
    const next2Year  = next1Year + (12 === next1Month ? 1 : 0);

    const within = (y, m) => {
      if (!maxDate) return true;
      const probe = `${y}-${String(m).padStart(2, '0')}-01`;
      return probe <= maxDate;
    };
    if (within(next1Year, next1Month)) fetchMonth(next1Year, next1Month);
    if (within(next2Year, next2Month)) fetchMonth(next2Year, next2Month);
  }

  return {
    isLoading, isInitialLoaded, isFetchingNavMonth, error,
    blockedDates, blockedDatesYmd,
    availableDatesYmd, disabledDatesForCalendar,
    loadedMonthsYmKey, isMonthLoaded,
    currentDayRows, currentSlots, buckets,
    fetchMonth, fetchMonthForNav, fetchInitial, fetchForDate,
    isDateSelectable, invalidateForService, dayRowsForDate, dayCapacityLabel,
  };
}
