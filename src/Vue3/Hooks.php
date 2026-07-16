<?php
/**
 * Hooks — canonical declaration of all `bookingpress_form_v3_*` hook names.
 *
 * No logic. Class exists so any developer searching the codebase for a hook
 * name lands here first. Each constant has a PHPDoc describing when the hook
 * fires and what signature it accepts.
 *
 * **None of these constants are wired to `do_action` / `apply_filters` in M1.**
 * The fire sites land in M3-M6 as the corresponding services and components
 * are implemented. This file is a contract pin: changing a constant value
 * after M1 sign-off requires a contract revision (see §M0.14 in
 * LEGACY_BEHAVIOR_CONTRACT.md).
 *
 * @package BookingPress\Vue3
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.14
 */

namespace BookingPress\Vue3;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reserved hook names for the Vue3 greenfield path.
 *
 * Naming convention: all prefixed with `bookingpress_form_v3_`. Constants
 * named with the type prefix (`FILTER_*` or `ACTION_*`) so a static analyser
 * can lint usage at fire sites.
 */
class Hooks {

	// --- State-build filters (M5) ----------------------------------------

	/**
	 * Reshape the entire initial-state payload before it is serialized to JSON.
	 *
	 * Filter signature: `(array $state, array $atts, string $instance_id): array`
	 */
	const FILTER_INITIAL_STATE = 'bookingpress_form_v3_initial_state';

	const FILTER_PRO_INITIAL_STATE = 'bookingpress_complete_paument_v3_initial_state';

	/**
	 * Reshape the services payload during state build.
	 *
	 * Filter signature: `(array $services, array $context): array`
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.1
	 */
	const FILTER_SERVICES = 'bookingpress_form_v3_services';

	/**
	 * Reshape the categories payload during state build.
	 *
	 * Filter signature: `(array $categories, array $services, array $context): array`
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.2
	 */
	const FILTER_CATEGORIES = 'bookingpress_form_v3_categories';

	/**
	 * Reshape the customer-form-fields payload during state build (pre-order).
	 *
	 * Filter signature: `(array $fields, array $context): array`
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.8, §M0.11
	 */
	const FILTER_FORM_FIELDS = 'bookingpress_form_v3_form_fields';

	/**
	 * Reshape the customer-form-fields payload AFTER ordering.
	 *
	 * Filter signature: `(array $fields_ordered, array $context): array`
	 */
	const FILTER_FORM_FIELDS_ORDERED = 'bookingpress_form_v3_form_fields_ordered';

	/**
	 * Reshape the customer-form-fields validation-rules map.
	 *
	 * Filter signature: `(array $rules, array $fields, array $context): array`
	 */
	const FILTER_FORM_FIELDS_RULES = 'bookingpress_form_v3_form_fields_rules';

	/**
	 * Reshape the customer-form-fields error-messages map.
	 *
	 * Filter signature: `(array $messages, array $fields, array $context): array`
	 */
	const FILTER_FORM_FIELDS_MESSAGES = 'bookingpress_form_v3_form_fields_messages';

	/**
	 * Reshape the step descriptors array.
	 *
	 * Filter signature: `(array $steps, array $context): array`
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.9.B
	 */
	const FILTER_STEPS = 'bookingpress_form_v3_steps';

	/**
	 * Reshape the enabled payment methods list.
	 *
	 * Filter signature: `(array $methods, array $context): array`
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.10
	 */
	const FILTER_PAYMENT_METHODS = 'bookingpress_form_v3_payment_methods';

	/**
	 * Confirm the resolved service / staff / location triple post-selection.
	 *
	 * Filter signature: `(bool $is_valid, array $selection, array $context): bool`
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.1, §M0.3
	 */
	const FILTER_CHECK_SELECTED_SERVICE = 'bookingpress_form_v3_check_selected_service';

	// --- Timeslot / availability filters (M3) -----------------------------

	/**
	 * Reshape the full timeslot endpoint payload before it is returned to the client.
	 *
	 * Filter signature: `(array $payload, array $request): array`
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.4
	 */
	const FILTER_TIMESLOT_PAYLOAD = 'bookingpress_form_v3_timeslot_payload';

	/**
	 * Reshape the timeslot REQUEST (the `$request` array that flows through the
	 * grid build as `$context`) before either timeslot endpoint processes it.
	 *
	 * Server-side analog of the client `bookingpress_form_v3_timeslot_request`
	 * wp.hooks filter. Needed because the FIRST grid request of a page fires at
	 * Date & Time step mount — during the loader module's evaluation, BEFORE any
	 * add-on module (which imports the loader) has registered its client-side
	 * request filter. When Date & Time is the form's FIRST step, that initial
	 * request therefore carries none of the add-on flags; a consumer of THIS
	 * filter re-derives any flag knowable server-side (e.g. Pro derives
	 * `any_staff` when the staff step is hidden, and `serviceless_grid` when the
	 * sequence places Service after Date & Time). Applied on both the
	 * initial-payload and month-navigation paths — and NOT on the submit-time
	 * re-validation path, which intentionally runs without grid flags. Inert in
	 * Lite (no callback).
	 *
	 * Filter signature: `(array $request): array`
	 */
	const FILTER_TIMESLOT_REQUEST_CONTEXT = 'bookingpress_form_v3_timeslot_request_context';

	/**
	 * Resolve the maximum bookable date — the calendar's upper selectable bound
	 * and the month-walker's stop date.
	 *
	 * Fired wherever Lite computes the booking-window ceiling: the initial
	 * timeslot payload's `max_available_date` + walker stop
	 * ({@see \BookingPress\Vue3\Services\TimeslotService::get_initial_payload})
	 * and the `config.maxDate` seed
	 * ({@see \BookingPress\Vue3\State\StateBuilder}). Lite seeds the default of
	 * today + 1 year and registers no callback, so a Lite-only install keeps the
	 * fixed one-year window. Pro's "Period available for booking in advance
	 * (Days)" (general setting `period_available_for_booking`, 1..1095) hooks this
	 * to return `today + period` — which may be SOONER than the Lite default
	 * (narrower window) or LATER (up to ~3 years), so this must replace the base
	 * rather than only narrow it. A per-service Service Expiration Date is applied
	 * separately ({@see self::FILTER_TIMESLOT_PAYLOAD}) and narrows this when it
	 * is sooner, giving an effective max of `min(today + period, expiration)`.
	 *
	 * Filter signature: `(string $max_date, string $today, array $context): string`
	 * where `$max_date` / `$today` / the return are all `Y-m-d`.
	 */
	const FILTER_MAX_BOOKING_DATE = 'bookingpress_form_v3_max_booking_date';

	/**
	 * Reshape the per-day timeslot data (e.g. inject custom availability rules).
	 *
	 * Filter signature: `(array $rows, string $date, array $context, int $service_id): array`
	 *
	 * The `$service_id` arg mirrors {@see self::FILTER_SLOT_CAPACITY} so a
	 * consumer can resolve per-service availability rules (e.g. Pro's "Minimum
	 * time required before booking") without depending on the `$context` shape,
	 * which is the full REST request on the initial-payload path but empty on
	 * the month-navigation path.
	 */
	const FILTER_TIMESLOT_DATA = 'bookingpress_form_v3_timeslot_data';

	/**
	 * Resolve the working *schedule* for a service/date before slots are
	 * generated — the Vue3 analog of the legacy
	 * `bookingpress_retrieve_pro_modules_timeslots` priority chain.
	 *
	 * Fired inside `AvailabilityService::get_timings_for_date()` BEFORE the
	 * bookable window / break gaps / off-state are committed (and BEFORE
	 * {@see self::FILTER_SLOT_WINDOW}). Unlike FILTER_SLOT_WINDOW — which only
	 * *reshapes* an already general-sourced window — this filter lets a consumer
	 * REPLACE the day's schedule SOURCE per service: open a day the general
	 * settings leave closed (`window` may arrive null), mark a day off, or carry
	 * break gaps the single-window model can't express. Pro's per-service "Shift
	 * Management" (holidays / special days / working hours) hooks this; a future
	 * per-staff shift feature hooks the SAME filter at a LOWER priority number so
	 * it resolves first.
	 *
	 * `$schedule` shape:
	 *   [
	 *     'is_off' => bool,   // true → date is unavailable (greyed; returns []).
	 *     'window' => ['start' => 'HH:MM:SS', 'end' => 'HH:MM:SS'] | null,
	 *     'breaks' => [ ['start' => 'HH:MM:SS', 'end' => 'HH:MM:SS'], … ],
	 *     'source' => 'default', // short-circuit marker; see below.
	 *   ]
	 *
	 * SHORT-CIRCUIT CONVENTION: Lite seeds `source => 'default'`. A consumer that
	 * resolves the schedule sets `source` to its own token (e.g. `'service'`,
	 * `'staff'`) so a LATER (lower-priority-number runs earlier in WP, so
	 * "later" = higher number) callback can bail when `source !== 'default'` —
	 * mirroring the legacy "first callback to produce data wins" behaviour. This
	 * is what lets staff (higher priority) override service (priority 11) which
	 * overrides the Lite general default.
	 *
	 * Lite registers no callback, so a Lite-only install is unchanged (the
	 * default schedule = general weekday window + company-holiday/past off-state,
	 * no breaks).
	 *
	 * Filter signature: `(array $schedule, int $service_id, string $date, array $context): array`
	 */
	const FILTER_DAY_SCHEDULE = 'bookingpress_form_v3_day_schedule';

	/**
	 * Reshape the bookable working-hours window for a service/date before slots
	 * are generated.
	 *
	 * Fired inside `AvailabilityService::get_timings_for_date()` AFTER the
	 * weekday working window is resolved and BEFORE `build_slots()` runs, so a
	 * consumer can narrow (or shift) the window per service — e.g. Pro's
	 * "Buffer Time" pushes the start forward by the before-buffer and pulls the
	 * end in by the after-buffer, which naturally shifts the whole generated
	 * grid (first slot starts later, last slot ends sooner). The slot *step*
	 * (duration vs `default_time_slot`) is unchanged.
	 *
	 * Lite registers no callback, so a Lite-only install is unchanged.
	 *
	 * Filter signature: `(array $window, int $service_id, string $date, array $context): array`
	 * where `$window = ['start' => 'HH:MM:SS', 'end' => 'HH:MM:SS']`.
	 */
	const FILTER_SLOT_WINDOW = 'bookingpress_form_v3_slot_window';

	/**
	 * Override the slot DURATION (minutes) used to generate the day's grid,
	 * after it is read from the service row and BEFORE slots are built.
	 *
	 * Fired in `AvailabilityService::get_timings_for_date()` once `$duration_min`
	 * has been resolved from the service. By default Lite returns it unchanged,
	 * so a Lite-only install generates slots exactly as before (each slot the
	 * service's own duration long, stepped per the "show time as per service
	 * duration" setting).
	 *
	 * The seam exists for the Vue3 analog of the legacy "serviceless" grid: when
	 * the Booking Form Sequence places the Service step AFTER Date & Time, the
	 * grid must be built WITHOUT a chosen service, so the slot length cannot come
	 * from a service. Pro's service-after-datetime feature returns the global
	 * "Default service duration" (`default_time_slot_step`) instead — the same
	 * value the legacy form used for that case — so the grid steps at the global
	 * slot length and the (hidden) end time is start + that length. The real
	 * service duration is applied once the visitor picks a service.
	 *
	 * Returning <= 0 yields no slots for the date (treated like a closed day).
	 *
	 * Filter signature: `(int $duration_min, int $service_id, string $date, array $context): int`
	 *
	 * Lite registers no callback, so a Lite-only install is unchanged.
	 */
	const FILTER_SLOT_DURATION = 'bookingpress_form_v3_slot_duration';

	/**
	 * Reshape the booked time-ranges that block slots on a date, before they are
	 * applied to per-slot availability/capacity.
	 *
	 * Fired inside `AvailabilityService::build_slots()` right after the booked
	 * ranges are fetched and BEFORE the per-slot overlap loop, so a consumer can
	 * extend/pad how an existing booking blocks neighbouring slots without
	 * changing the capacity math (the loop sums each overlapping range's
	 * `count`). Pro's "Buffer Time" extends every range symmetrically by the
	 * BOOKED service's `(before + after)` buffer, so a booking blocks slots that
	 * would not leave enough buffer around it.
	 *
	 * Each range is `['start_ts' => int, 'end_ts' => int, 'count' => int,
	 * 'service_id' => int]`; `service_id` is the service the booking belongs to
	 * (the viewing service in the non-shared case, the actual booked service in
	 * the shared-timeslot case) so a consumer can apply per-booked-service rules.
	 *
	 * The `$context` arg is the caller context (the REST request on the initial /
	 * month timeslot path, empty otherwise), so a consumer can resolve
	 * per-request rules — e.g. Pro's per-staff capacity REPLACES the ranges with
	 * only the selected staff's bookings, so each staff's capacity counts only
	 * that staff's bookings on the slot.
	 *
	 * Lite registers no callback, so a Lite-only install is unchanged.
	 *
	 * Filter signature: `(array $ranges, int $service_id, string $date, array $context): array`
	 */
	const FILTER_BOOKED_RANGES = 'bookingpress_form_v3_booked_ranges';

	/**
	 * Override the date-range booking-overlap decision for day services.
	 *
	 * Fired after Lite checks whether a day-service start/end range overlaps an
	 * existing booking for the same main service. Pro Staff uses this to narrow
	 * the overlap decision to the selected concrete staff member, while Lite and
	 * plain day services keep the original service-level overlap result.
	 *
	 * Filter signature: `(bool $has_overlap, int $service_id, string $start_date, string $end_date, array $context): bool`
	 */
	const FILTER_DAY_SERVICE_BOOKING_OVERLAP = 'bookingpress_form_v3_day_service_booking_overlap';

	/**
	 * Reshape the disabled-dates list before it is unioned into the timeslot payload.
	 *
	 * Filter signature: `(array $dates, int $service_id, array $context): array`
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.7
	 */
	const FILTER_DISABLED_DATES = 'bookingpress_form_v3_disabled_dates';

	/**
	 * Override the transient TTL for the per-token timeslot cache.
	 *
	 * Filter signature: `(int $ttl_seconds): int`
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.5
	 */
	const FILTER_CACHE_TTL = 'bookingpress_form_v3_cache_ttl';

	/**
	 * Resolve the per-slot booking capacity for a service/date.
	 *
	 * Lite has no multi-capacity UI, so it applies this filter with a default
	 * of `1` (one booking per slot) — leaving Lite-only behaviour unchanged.
	 * Pro 6.0+ registers a callback that returns the service's configured
	 * `max_capacity` service-meta value, so each slot's `remaining_capacity`
	 * (and therefore `is_available`) reflects the real capacity and the
	 * "Slots left" counter reduces as bookings are made.
	 *
	 * The capacity feeds straight into `AvailabilityService::build_slots()`:
	 * `remaining_capacity = max( 0, capacity - booked )`.
	 *
	 * Filter signature: `(int $capacity, array $service, int $service_id, string $date, array $context): int`
	 */
	const FILTER_SLOT_CAPACITY = 'bookingpress_form_v3_slot_capacity';

	/**
	 * Resolve whether booking capacity is SHARED across overlapping ("crossed")
	 * timeslots on a date — the Vue3 analog of the legacy "Share Capacity
	 * between timeslots" general setting.
	 *
	 * Only meaningful when "Show time as per service duration" is OFF: the slot
	 * step is then the (smaller) "Default time slot step", so generated slots
	 * overlap one another (e.g. a 30-min service on a 10-min step yields
	 * 09:00–09:30, 09:10–09:40, 09:20–09:50, …). When an existing booking lands
	 * on such a grid this filter decides how its neighbours behave:
	 *
	 *   - return TRUE  → every slot whose window OVERLAPS the booking has its
	 *                    remaining capacity reduced, the crossed neighbours
	 *                    included. This is the Lite default (no callback → stays
	 *                    TRUE) and matches the legacy Lite fallback of `'true'`.
	 *   - return FALSE → only the slot whose window EXACTLY matches the booking
	 *                    is reduced; the crossed neighbours are DROPPED from the
	 *                    grid entirely (they would physically conflict with the
	 *                    booking), mirroring the legacy `unset()` of crossed
	 *                    slots.
	 *
	 * Fired inside `AvailabilityService::build_slots()` before the per-slot
	 * overlap loop. Lite registers no callback, so a Lite-only install is
	 * unchanged. Pro's "Share Capacity between timeslots" setting (general
	 * setting `share_quanty_between_timeslots`, default OFF) hooks this to return
	 * the real configured value.
	 *
	 * Filter signature: `(bool $share, int $service_id, string $date): bool`
	 */
	const FILTER_SHARE_CAPACITY = 'bookingpress_form_v3_share_capacity';

	// --- Submit / payment filters and actions (M3) ------------------------

	/**
	 * Reshape the submit payload server-side before validation runs.
	 *
	 * Filter signature: `(array $payload, array $context): array`
	 */
	const FILTER_SUBMIT_PAYLOAD = 'bookingpress_form_v3_submit_payload';

	/**
	 * Stage-1 pre-write hook — reshape the `bookingpress_entries` row data
	 * before it is inserted, so Pro / add-ons can persist extra columns that
	 * already exist on the entries table (e.g. the Staff Member module's
	 * `bookingpress_staff_*` columns). Fires inside
	 * `SubmissionService::stage1_insert_entry()` for BOTH the inline-finalize
	 * and the PayPal pre-flight paths, so the entry becomes the single source of
	 * truth a later confirm (where the original payload may no longer be
	 * available) can read back.
	 *
	 * Lite registers no callback, so a Lite-only install inserts the same entry
	 * data as before.
	 *
	 * Filter signature: `(array $entry_data, array $payload): array`
	 */
	const FILTER_SUBMIT_ENTRY = 'bookingpress_form_v3_submit_entry';

	/**
	 * Post-entry-insert action — the Vue3 analog of the legacy
	 * `bookingpress_after_entry_data_insert`. Fires inside
	 * {@see \BookingPress\Vue3\Services\SubmissionService::stage1_insert_entry()}
	 * immediately AFTER the pending `bookingpress_entries` row is inserted, while
	 * the full submit payload is still available. This is the seam for add-ons that
	 * must persist RELATED side-table rows keyed by the entry id (e.g. the Multi
	 * Service add-on's clubbed per-service rows in `bookingpress_multi_service_entry`)
	 * — work that MUST happen at submit time because the later gateway-confirm path
	 * (PayPal / Stripe) re-enters `finalize_booking()` with an EMPTY payload, so
	 * `ACTION_AFTER_BOOKING` cannot see the original selection there.
	 *
	 * Fires once per line item (so a cart/order writes one per item). Lite registers
	 * no callback, so a Lite-only install is unaffected.
	 *
	 * Action signature: `do_action( …, int $entry_id, array $payload, array $entry_data )`
	 */
	const ACTION_AFTER_ENTRY_INSERT = 'bookingpress_form_v3_after_entry_insert';

	/**
	 * Pre-write hook — opportunity to add columns to the appointment_bookings
	 * data array (built from the stage-1 entry inside
	 * `SubmissionService::finalize_booking()`).
	 *
	 * Filter signature: `(array $appointment_data, array $payload): array`
	 */
	const FILTER_SUBMIT_PRE = 'bookingpress_form_v3_submit_pre';

	/**
	 * Pre-write hook for the payment transaction row (`bookingpress_payment_transactions`,
	 * the legacy `payment_logs` table) built from the stage-1 entry inside
	 * `SubmissionService::finalize_booking()`, just before it is inserted. Lets
	 * Pro / add-ons persist extra columns that already exist on the table (e.g.
	 * the coupon / tax / deposit columns the legacy admin payment views read back
	 * from this row, NOT from appointment_bookings). The third argument is the
	 * full stage-1 entry row, so a callback can copy values it already persisted
	 * on the entry (works for the inline AND PayPal-confirm paths, where the
	 * original submit payload may be empty).
	 *
	 * Lite registers no callback, so a Lite-only install inserts the same payment
	 * row as before.
	 *
	 * Filter signature: `(array $payment_data, array $payload, array $entry): array`
	 */
	const FILTER_SUBMIT_PAYMENT = 'bookingpress_form_v3_submit_payment';

	/**
	 * Expand a single submit into an ordered list of per-item ("line item")
	 * payloads — the generic seam that lets ONE checkout write MORE THAN ONE
	 * appointment (e.g. the Cart add-on books several appointments at once).
	 *
	 * Fired at the very top of {@see \BookingPress\Vue3\Services\SubmissionService::submit()}
	 * and {@see \BookingPress\Vue3\Services\SubmissionService::stage1_for_paypal()},
	 * right after {@see self::FILTER_SUBMIT_PAYLOAD}. Each returned item is a
	 * payload shaped EXACTLY like a single-item submit (`selected_service`,
	 * `selected_date`, `selected_start_time`/`_end_time`, `selected_staff_member_id`,
	 * extras keys, …) so every per-feature {@see self::FILTER_SUBMIT_ENTRY} /
	 * {@see self::FILTER_SUBMIT_PRE} callback (Staff, Service Extras, Multiple
	 * Quantity, …) finds the keys it already reads and persists its columns PER
	 * appointment. Lite then loops `stage1_insert_entry()` once per item, so N
	 * items become N `bookingpress_entries` rows.
	 *
	 * Lite seeds the default `[$payload]` (a single item) and registers no
	 * callback, so a Lite-only install inserts exactly one entry exactly as
	 * before — byte-identical single-booking behaviour.
	 *
	 * Filter signature: `(array $line_items, array $payload, array $context): array`
	 */
	const FILTER_SUBMIT_LINE_ITEMS = 'bookingpress_form_v3_submit_line_items';

	/**
	 * Supply extra columns shared by every row of a multi-appointment ORDER
	 * (e.g. the Cart add-on's `bookingpress_order_id` + `bookingpress_is_cart`),
	 * merged into the entries / appointment_bookings / payment_transactions row
	 * data just before it is inserted.
	 *
	 * Fired in `stage1_insert_entry()` (phase `entry`),
	 * `build_appointment_row_from_entry()` (phase `appointment`) and
	 * `build_payment_row_from_entry()` (phase `payment`). Because every repo
	 * insert passes through `filter_to_existing_columns`, any returned column the
	 * table does not have is silently dropped — so a Lite-only install (whose
	 * tables lack the order columns) is provably unaffected.
	 *
	 * `$context` = `['phase'=>'entry'|'appointment'|'payment', 'order_id'=>int,
	 * 'item_index'=>int, 'item_count'=>int, 'entry'=>array]` (the keys present
	 * depend on the phase).
	 *
	 * Lite returns `[]` (no extra columns) and registers no callback.
	 *
	 * Filter signature: `(array $columns, array $context): array`
	 */
	const FILTER_SUBMIT_ORDER_COLUMNS = 'bookingpress_form_v3_submit_order_columns';

	/**
	 * Decide whether {@see \BookingPress\Vue3\Services\SubmissionService::finalize_booking()}
	 * writes ONE payment_transactions row per booking (Lite default) or ONE
	 * shared payment row covering MANY bookings (a multi-appointment order).
	 *
	 * Fired once at the top of `finalize_booking()` after the entry is loaded.
	 *   - `['mode'=>'per_booking']` (Lite default): the entry is materialised as a
	 *     single appointment_bookings row + a single payment_transactions row and
	 *     the return envelope is identical to today.
	 *   - `['mode'=>'shared', 'order_id'=>int]`: Lite loads EVERY entry of the
	 *     order (`EntryRepository::find_by_order()`), writes one
	 *     appointment_bookings row per entry, computes ONE invoice id, writes ONE
	 *     payment_transactions row (`appointment_booking_ref=0`) and back-links all
	 *     bookings to that single payment id + shared booking id. `ACTION_AFTER_BOOKING`
	 *     fires per booking; the email send is gated by {@see self::FILTER_SUBMIT_NOTIFICATION_PLAN}.
	 *
	 * `$context` = `['entry'=>array, 'payment_payload'=>array]`.
	 *
	 * Lite registers no callback, so a Lite-only install stays single-payment.
	 *
	 * Filter signature: `(array $group, array $context): array`
	 */
	const FILTER_SUBMIT_PAYMENT_GROUP = 'bookingpress_form_v3_submit_payment_group';

	/**
	 * Gate the post-booking email notification send so a multi-appointment order
	 * notifies ONCE (legacy cart parity — `bookingpress_send_only_first_appointment_notification`),
	 * not once per appointment.
	 *
	 * Fired inside `finalize_booking()` just before the notification send.
	 *   - `['send'=>true, 'booking_id'=>int]` (Lite default): send for the booking
	 *     as today.
	 *   - `['send'=>false]`: suppress (e.g. for the non-first appointments of an order).
	 *
	 * `$context` = `['booking_id'=>int, 'entry'=>array, 'is_first'=>bool, 'order_id'=>int]`.
	 *
	 * Lite registers no callback, so a Lite-only install sends exactly one
	 * notification as before.
	 *
	 * Filter signature: `(array $plan, array $context): array`
	 */
	const FILTER_SUBMIT_NOTIFICATION_PLAN = 'bookingpress_form_v3_submit_notification_plan';

	/**
	 * Reshape the submit RESPONSE envelope after the service resolves it, just
	 * before the REST controller maps it to HTTP semantics. Fires for every
	 * variant (`redirect_url` / `pending_payment` / `error`), so a consumer can
	 * enrich the envelope on both success and failure.
	 *
	 * Lite does NOT register a callback — the envelope passes through unchanged,
	 * so a Lite-only install behaves exactly as before. Pro 6.0+ uses this to
	 * attach the In-Built redirection payload (rendered thank-you / failed HTML
	 * and the `is_transaction_completed` flag) so the client can show the inline
	 * confirmation screen instead of redirecting.
	 *
	 * Filter signature: `(array $envelope, array $payload): array`
	 */
	const FILTER_SUBMIT_ENVELOPE = 'bookingpress_form_v3_submit_envelope';

	/**
	 * Last-moment double-booking GUARD — re-validate slot availability for ONE
	 * appointment immediately before its `appointment_bookings` row is inserted.
	 *
	 * Fired inside `SubmissionService::write_one_booking_from_entry()` (the single
	 * insert choke point shared by the single-booking and the cart/recurring ORDER
	 * paths) AFTER `FILTER_SUBMIT_PRE`, so `$appt_data` already carries the post-PRE
	 * truth (staff id, extra-members, corrected price, duration). Because the order
	 * path inserts occurrences sequentially, earlier occurrences are already in the
	 * table when a later one's guard runs — so a series cannot overbook itself.
	 *
	 *   - `['prevent'=>false]` (Lite default): proceed with the insert as today.
	 *   - `['prevent'=>true, 'message'=>string, 'code'=>int]`: VETO — Lite skips the
	 *     insert and the caller drops this booking (single path → error envelope;
	 *     order path → skip this occurrence and book the survivors). The consumer is
	 *     responsible for any refund / conflict-record / email side-effects (these
	 *     are genuinely Pro; Lite stays inert).
	 *
	 * `$context` = `['payload'=>array, 'gateway'=>string, 'transaction_id'=>string,
	 *               'paid_amount'=>float, 'currency'=>string]` — carries what a Pro
	 * refund needs even on the separate gateway-confirm request (where `payload` is
	 * empty). Legacy parity: `bookingpress_confirm_booking()`'s
	 * `bookingpress_is_appointment_booked(..., $prevent_double_booking=true)` check.
	 *
	 * Lite registers no callback → a Lite-only install never prevents (unchanged).
	 *
	 * Filter signature: `(array $guard, array $appt_data, array $entry, array $context): array`
	 */
	const FILTER_SUBMIT_BOOKING_GUARD = 'bookingpress_form_v3_submit_booking_guard';

	/**
	 * Submit INTERCEPT — give a feature the chance to fully handle a submit and
	 * return its own response envelope BEFORE the standard booking pipeline
	 * (validation, price check, stage-1 entry insert) runs.
	 *
	 * Fired at the top of `SubmissionService::submit()` right after
	 * `FILTER_SUBMIT_PAYLOAD`. Return `null` (Lite default — no callback) to run
	 * the normal booking-form pipeline unchanged; return an envelope array to
	 * short-circuit with it.
	 *
	 * Consumer: Pro's Complete Payment page submits with an existing
	 * `appointment_id` and settles the REMAINING balance of that appointment
	 * instead of creating a new booking — Pro handles that whole flow here
	 * (see `BookingPressPro\Vue3\CompletePaymentSubmissionFeature`).
	 *
	 * Filter signature: `(null|array $envelope, array $payload, SubmissionService $service): null|array`
	 */
	const FILTER_SUBMIT_INTERCEPT = 'bookingpress_form_v3_submit_intercept';

	/**
	 * Finalize INTERCEPT — give a feature the chance to fully handle
	 * `SubmissionService::finalize_booking()` for one entry and return its own
	 * response envelope INSTEAD of the standard "insert appointment + payment
	 * rows" path.
	 *
	 * Fired first thing in `finalize_booking()`, before the entry is even
	 * loaded. Return `null` (Lite default — no callback) to run the normal
	 * finalize unchanged; return an envelope array to short-circuit with it.
	 *
	 * Consumer: Pro's Complete Payment — a pending complete-payment entry must
	 * UPDATE its existing appointment/payment rows (clear the payment token,
	 * approve, settle) rather than insert new ones, with its own idempotency
	 * marker + in-progress lock (see
	 * `BookingPressPro\Vue3\CompletePaymentSubmissionFeature`).
	 *
	 * Filter signature: `(null|array $envelope, int $entry_id, array $payment_payload, SubmissionService $service): null|array`
	 */
	const FILTER_FINALIZE_INTERCEPT = 'bookingpress_form_v3_finalize_intercept';

	/**
	 * Complete Payment payable breakdown for a stage-1 entry.
	 *
	 * Backs the public gateway-facing helpers
	 * `SubmissionService::complete_payment_payable_for_entry()` /
	 * `complete_payment_payable_breakdown()`, which every gateway (Lite PayPal +
	 * the Stripe / Square add-ons) consults to decide whether an entry is a
	 * pending Complete Payment (charge the remaining balance) or a normal
	 * booking-form entry (charge the staged amount, on `null`).
	 *
	 * Lite registers no callback → always `null` on a Lite-only install (the
	 * Complete Payment feature — and its `bookingpress_complete_payment_token`
	 * column — exist only in Pro). Pro returns the breakdown array
	 * `{due, coupon_discount, gift_discount, tip, final, final_with_currency,
	 * appointment_id, payment_id}` for a pending Complete Payment entry.
	 *
	 * Filter signature: `(null|array $breakdown, int $entry_id): null|array`
	 */
	const FILTER_COMPLETE_PAYMENT_BREAKDOWN = 'bookingpress_form_v3_complete_payment_breakdown';

	/**
	 * PayPal `return` URL for a Complete Payment entry — the page the buyer
	 * lands on after paying at PayPal. Applied in
	 * `PaymentService::paypal_redirect_prepare()` ONLY when the entry is a
	 * pending Complete Payment (which requires Pro), so a Lite-only install
	 * never reaches it. Pro returns the complete-payment page URL carrying the
	 * appointment's `bkp_pay` token.
	 *
	 * Filter signature: `(string $url, int $entry_id): string`
	 */
	const FILTER_COMPLETE_PAYMENT_RETURN_URL = 'bookingpress_form_v3_complete_payment_return_url';

	/**
	 * PayPal IPN idempotency refinement — decide whether an appointment row
	 * that matches the IPN's entry means the entry is ALREADY finalized.
	 *
	 * On a Lite-only install an appointment row exists for an entry ONLY after
	 * finalize, so a match always means "finalized" (`true`, the default — the
	 * retried IPN is acknowledged without creating a duplicate booking). With
	 * Pro's Complete Payment a PENDING complete-payment appointment also
	 * matches by entry id; Pro's callback re-checks the row's
	 * `bookingpress_complete_payment_token` and returns `false` for it so the
	 * IPN can still finalize that payment.
	 *
	 * Filter signature: `(bool $finalized, int $entry_id, int $appointment_booking_id): bool`
	 */
	const FILTER_PAYPAL_IPN_ENTRY_FINALIZED = 'bookingpress_form_v3_paypal_ipn_entry_finalized';

	/**
	 * Captcha gate — Lite math-question or Pro reCAPTCHA / hCaptcha.
	 *
	 * Filter signature: `(bool $is_valid, array $payload): bool`
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.9.C `gate.captcha`
	 */
	const FILTER_CAPTCHA = 'bookingpress_form_v3_captcha';

	/**
	 * Pre-flight action immediately before a PayPal order is created.
	 *
	 * Action signature: `do_action( …, array $payload, array $context )`
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.10
	 */
	const ACTION_PAYPAL_VALIDATE = 'bookingpress_form_v3_paypal_validate';

	/**
	 * Post-capture action after PayPal confirms an order.
	 *
	 * Action signature: `do_action( …, int $entry_id, array $payload )`
	 */
	const ACTION_PAYPAL_CONFIRM = 'bookingpress_form_v3_paypal_confirm';

	/**
	 * Final post-write action — fires after the booking row is inserted.
	 *
	 * Action signature: `do_action( …, int $appointment_booking_id, int $entry_id, array $payload )`
	 *
	 * Add-ons that need to persist custom-field values to their own tables
	 * hook this action and read from `$payload`.
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.10, §M0.11
	 */
	const ACTION_AFTER_BOOKING = 'bookingpress_form_v3_after_booking';

	/**
	 * Stage-2 failure action — fires when the entries row has been inserted
	 * but the appointment_bookings + payment_transactions stage failed.
	 *
	 * Action signature: `do_action( …, int $entry_id, \Throwable $error, array $payload )`
	 *
	 * Add-ons use this to surface their own cleanup or alerting. The pending
	 * entry remains in the database until the legacy
	 * `bookingpress_clear_pending_entries` cron prunes it (after 24h).
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.10
	 */
	const ACTION_AFTER_FAILED_PAYMENT = 'bookingpress_form_v3_after_failed_payment';

	// --- Summary / pricing filters (M3) -----------------------------------

	/**
	 * Reshape the entire summary payload before it is sent to the client.
	 *
	 * Filter signature: `(array $payload, array $context): array`
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.12
	 */
	const FILTER_SUMMARY_PAYLOAD = 'bookingpress_form_v3_summary_payload';

	/**
	 * Decorate the summary total (taxes, coupons, deposits).
	 *
	 * Filter signature: `(float $total, array $breakdown, array $context): float`
	 */
	const FILTER_SUMMARY_TOTAL = 'bookingpress_form_v3_summary_total';

	/**
	 * Resolve the amount the customer pays NOW — the gateway charge — which may
	 * be LESS than the order total (`FILTER_SUMMARY_TOTAL`) when a partial-payment
	 * feature is active. Lite seeds it equal to the order total and registers no
	 * callback, so a Lite-only install charges the full total exactly as before.
	 *
	 * Fired in `SubmissionService::submit()` and `::stage1_for_paypal()` AFTER the
	 * order total is computed: the returned value is what the anti-tamper check
	 * compares against the client's `service_price_without_currency`, what is
	 * stored as the stage-1 entry's `bookingpress_service_price` (so every gateway
	 * — PayPal / Stripe / on-site — charges it without gateway-specific code), and
	 * the `paid_amount` recorded for the booking. The FULL order total and the
	 * remaining balance are recorded by the consumer via the `FILTER_SUBMIT_*`
	 * hooks. Pro's "Deposit" module hooks this to return the deposit when the
	 * visitor chooses to pay a deposit (and the gateway is not on-site).
	 *
	 * The client mirrors this through the `bookingpress_form_v3_payable_amount`
	 * wp.hooks filter so the submitted price hint matches the server.
	 *
	 * Filter signature: `(float $payable, array $context): float`
	 * where `$context = ['service_id'=>int,'form_data'=>array]`.
	 */
	const FILTER_PAYABLE_AMOUNT = 'bookingpress_form_v3_payable_amount';

	/**
	 * Register meta keys whose changes should trigger a summary recompute.
	 *
	 * Filter signature: `(array $meta_keys): array`
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.12
	 */
	const FILTER_PRICE_AFFECTING_FIELDS = 'bookingpress_form_v3_price_affecting_fields';

	// --- ServiceLocator DI filter (M3) ------------------------------------

	/**
	 * Single canonical filter for swapping any interface-bound service.
	 *
	 * Pro registers one callback that switches on the interface FQCN it
	 * wants to override. Lite's `ServiceLocator::get()` invokes this filter
	 * exactly once per resolution.
	 *
	 * Filter signature: `(object $default_instance, string $interface_fqcn): object`
	 *
	 * The callback must return an `instanceof $interface_fqcn`; if it
	 * doesn't, the locator throws
	 * `\BookingPress\Vue3\Exceptions\InvalidServiceImplementationException`.
	 *
	 * @see docs/migration/BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md §5a.2
	 */
	const FILTER_SERVICE = 'bookingpress_form_v3_service';

	// --- Cache invalidation actions (M3) ----------------------------------

	/**
	 * Purge `bp_v3_timeslots_*_v1` transients (admin Days Off save, etc.).
	 *
	 * Action signature: `do_action( …, ?int $service_id = null )`
	 *
	 * @see LEGACY_BEHAVIOR_CONTRACT.md §M0.5, §M0.7
	 */
	const ACTION_INVALIDATE_CACHE = 'bookingpress_form_v3_invalidate_cache';

	// --- Pro 6.0 reserved hook names (M9) ----------------------------------
	//
	// These names are reserved by Lite for Pro 6.0+ to fire. Lite itself
	// does NOT apply these filters — Pro registers a callback on
	// `Hooks::FILTER_SERVICE` to swap in its own AvailabilityService /
	// SubmissionService / etc. implementation, and that implementation is
	// what fires these. Reserved here so the canonical name lives in one
	// grep target.

	/**
	 * Pro-emitted: list of staff members for the form context.
	 *
	 * Filter signature: `(array $staff, array $context): array`
	 */
	const FILTER_PRO_STAFF_LIST = 'bookingpress_form_v3_pro_staff';

	/**
	 * Pro-emitted: list of locations for the form context.
	 *
	 * Filter signature: `(array $locations, array $context): array`
	 */
	const FILTER_PRO_LOCATIONS = 'bookingpress_form_v3_pro_locations';

	/**
	 * Pro-emitted: per-service availability filter (staff/location-aware).
	 *
	 * Filter signature: `(array $services, array $context): array`
	 */
	const FILTER_PRO_AVAILABLE_SERVICES = 'bookingpress_form_v3_pro_available_services';

	/**
	 * Pro-emitted: cart line items for the multi-service add-on.
	 *
	 * Filter signature: `(array $line_items, array $form_data): array`
	 */
	const FILTER_PRO_CART_ITEMS = 'bookingpress_form_v3_pro_cart_items';

	/**
	 * Pro-emitted action: a coupon was applied at the summary step.
	 *
	 * Action signature: `do_action( …, string $coupon_code, float $discount, array $context )`
	 */
	const ACTION_PRO_COUPON_APPLIED = 'bookingpress_form_v3_pro_coupon_applied';

	/**
	 * Pro-emitted action: a deposit was selected at the summary step.
	 *
	 * Action signature: `do_action( …, float $deposit_amount, float $remaining, array $context )`
	 */
	const ACTION_PRO_DEPOSIT_SELECTED = 'bookingpress_form_v3_pro_deposit_selected';

	// --- Asset / loader filters (M1, M5) ----------------------------------

	/**
	 * Reshape the JSON island the loader module reads
	 * (`script_module_data_bookingpress-form-v3-loader`).
	 *
	 * Filter signature: `(array $data): array`
	 */
	const FILTER_MODULE_DATA = 'bookingpress_form_v3_module_data';
}
