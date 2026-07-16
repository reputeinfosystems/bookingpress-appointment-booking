/**
 * ServiceStep — category tabs + service grid.
 *
 * Markup mirrors the released `[bookingpress_form]` step-1 panel 1:1
 * (`.bpa-front-default-card` / `.bpa-front-dc--body` / `.bpa-front-dc--footer`
 *  / `.bpa-front-module--category` / `.bpa-front-cat-items` /
 *  `.bpa-front-ci-pill.el-tag.el-tag--light` / `.bpa-front-module--service` /
 *  `.bpa-front-module--service-items-row` / `.bpa-fm--si--col` /
 *  `.bpa-front-si-card` / `.bpa-front-si-card__left` / `.bpa-front-si__card-body`
 *  / `.bpa-front-si-cb__specs` / `.bpa-front-btn--primary` / `.bpa-front-tabs--foot`).
 *
 * All copy is sourced from `state.strings.*` (delivered by the backend
 * customize layer in `StateBuilder::compose_strings()`), so renaming a
 * label in the admin propagates straight to the form. The duration unit
 * letter (`m` / `h` / `d`) comes from the raw service row, matching the
 * released form's abbreviated rendering (e.g. "Duration: 30 m").
 *
 * Selection updates `state.appointment_step_form_data.selected_service` and
 * `selected_category`. Triggers `useTimeslots.invalidateForService()` so
 * cached timeslots from a previous service don't leak in.
 */
import { computed, inject, ref } from 'vue';
import { formatPrice as formatPriceUtil } from '../../utils/currency.js';
import { effectivePrice } from '../../utils/pricing.js?v=2';
import { syncSelectedServiceDuration } from '../../utils/service.js';
import { useRovingTabindex } from '../../composables/useA11yNav.js?v=1';

// --- Inline SVGs from the released template -------------------------------

/** Filled check-circle painted on selected service card and active category pill. */
const ICON_CHECKMARK =
  '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">' +
    '<path d="M0 0h24v24H0V0z" fill="none"/>' +
    '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM9.29 16.29 5.7 12.7c-.39-.39-.39-1.02 0-1.41.39-.39 1.02-.39 1.41 0L10 14.17l6.88-6.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-7.59 7.59c-.38.39-1.02.39-1.41 0z"/>' +
  '</svg>';

const ICON_MULTISERVICE_CHECKMARK =
  '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="2" width="20" height="20" rx="6" style="fill:var(--bpa-pt-main-green)"/><rect x="6" y="6" width="12" height="12" rx="6" style="fill:var( --bpa-pt-price-button-text-color )"/><g clip-path="url(#clip0_3047_24293)"><path d="M12 5C8.136 5 5 8.136 5 12C5 15.864 8.136 19 12 19C15.864 19 19 15.864 19 12C19 8.136 15.864 5 12 5ZM10.103 15.003L7.59 12.49C7.317 12.217 7.317 11.776 7.59 11.503C7.863 11.23 8.304 11.23 8.577 11.503L10.6 13.519L15.416 8.703C15.689 8.43 16.13 8.43 16.403 8.703C16.676 8.976 16.676 9.417 16.403 9.69L11.09 15.003C10.824 15.276 10.376 15.276 10.103 15.003Z" style="fill:var(--bpa-pt-main-green)"/></g><defs><clipPath id="clip0_3047_24293"><rect width="24" height="24" style="fill:var( --bpa-pt-price-button-text-color )"/></clipPath></defs></svg>';

const ICON_MULTISERVICE_PLUS =
  '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="0.499083" y="0.499083" width="19.0018" height="19.0018" rx="5.50092" stroke="#CFD6E6" stroke-width="0.998165"/><path d="M10 6.5V13.5" stroke="#727E95" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.49902 10H13.499" stroke="#727E95" stroke-linecap="round" stroke-linejoin="round"/></svg>';

/** Default placeholder "mountain" SVG painted when a service has no image. */
const ICON_SERVICE_PLACEHOLDER =
  '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">' +
    '<path d="M0 0h24v24H0V0z" fill="none"/>' +
    '<path d="M13.2 7.07L10.25 11l2.25 3c.33.44.24 1.07-.2 1.4-.44.33-1.07.25-1.4-.2-1.05-1.4-2.31-3.07-3.1-4.14-.4-.53-1.2-.53-1.6 0l-4 5.33c-.49.67-.02 1.61.8 1.61h18c.82 0 1.29-.94.8-1.6l-7-9.33c-.4-.54-1.2-.54-1.6 0z"/>' +
  '</svg>';

/** Right-arrow on the primary "Next: …" footer button. */
const ICON_ARROW_RIGHT =
  '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24">' +
    '<rect fill="none" height="24" width="24"/>' +
    '<path d="M14.29,5.71L14.29,5.71c-0.39,0.39-0.39,1.02,0,1.41L18.17,11H3c-0.55,0-1,0.45-1,1v0c0,0.55,0.45,1,1,1h15.18l-3.88,3.88 c-0.39,0.39-0.39,1.02,0,1.41l0,0c0.39,0.39,1.02,0.39,1.41,0l5.59-5.59c0.39-0.39,0.39-1.02,0-1.41L15.7,5.71 C15.32,5.32,14.68,5.32,14.29,5.71z"/>' +
  '</svg>';

/** Left-arrow on the borderless "Go back" footer button. Only rendered when
 *  the step schema gives Service a previous step (e.g. Pro's "Booking form
 *  sequence" puts Basic Details first) — Lite's default order keeps Service
 *  first, so no back button shows there. */
const ICON_ARROW_LEFT =
  '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24">' +
    '<rect fill="none" height="24" width="24"/>' +
    '<path d="M9.71,18.29L9.71,18.29c0.39-0.39,0.39-1.02,0-1.41L5.83,13H21c0.55,0,1-0.45,1-1v0c0-0.55-0.45-1-1-1H5.83l3.88-3.88 c0.39-0.39,0.39-1.02,0-1.41l0,0c-0.39-0.39-1.02-0.39-1.41,0L2.71,11.3c-0.39,0.39-0.39,1.02,0,1.41l5.59,5.59 C8.68,18.68,9.32,18.68,9.71,18.29z"/>' +
  '</svg>';

export default {
  name: 'ServiceStep',
  setup() {
    const state     = inject('state');
    const nav       = inject('nav');
    const timeslots = inject('timeslots');
    const bus       = inject('bus');

    const selectedCategory = computed({
      get() { return String(state.appointment_step_form_data.selected_category || ''); },
      set(v) { state.appointment_step_form_data.selected_category = String(v); },
    });

    const selectedService = computed({
      get() { return String(state.appointment_step_form_data.selected_service || ''); },
      set(v) {
        const prev = state.appointment_step_form_data.selected_service;
        state.appointment_step_form_data.selected_service = String(v);
        const svc = state.services.find(s => parseInt(s.serviceId, 10) === parseInt(v, 10));
        syncSelectedServiceDuration(state, svc || null);
        // Reset downstream date+time when switching service.
        //
        // GENERIC seam: skip the reset when `config.preserveDatetimeOnServiceChange`
        // is set. That flag is for the Booking Form Sequence placing the Service
        // step AFTER Date & Time — there the date/time are ALREADY chosen (from a
        // serviceless grid) and must survive the service pick; the duration-aware
        // end time is recomputed by the Pro layer from the now-known service.
        // Inert in Lite (flag unset → resets exactly as before).
        if (String(prev) !== String(v) && !(state.config && state.config.preserveDatetimeOnServiceChange)) {
          state.appointment_step_form_data.selected_date = '';
          state.appointment_step_form_data.selected_end_date = '';
          state.appointment_step_form_data.selected_start_time = '';
          state.appointment_step_form_data.selected_end_time = '';
          state.appointment_step_form_data.selected_actual_date = '';
          if (prev) timeslots.invalidateForService(prev);
        }
        if (svc) {
          state.appointment_step_form_data.selected_category = String(svc.categoryId || '');
          // Per plan §3.3 — pre-select cancel hook + post-select notice.
          let cancelled = false;
          bus && bus.emit('bp-v3:before-select-service', {
            instanceId: state.instanceId,
            serviceId: parseInt(v, 10),
            cancel() { cancelled = true; },
          });
          if (cancelled) {
            state.appointment_step_form_data.selected_service = String(prev || '');
            const prevSvc = state.services.find(s => parseInt(s.serviceId, 10) === parseInt(prev, 10));
            syncSelectedServiceDuration(state, prevSvc || null);
            return;
          }
          bus && bus.emit('bp-v3:after-select-service', {
            instanceId: state.instanceId,
            service: svc,
          });
        }
      },
    });

    // Services that belong to the active category (categoryId === 0 is the
    // "All" pseudo-row injected by ServiceCatalogService). This is the raw,
    // base list before any optional add-on transform — the empty-state in the
    // template keys off THIS list so "no services in this category" still
    // shows the message, while an add-on narrowing the list to zero does not.
    const categoryServices = computed(() => {
      const cid = parseInt(selectedCategory.value || 0, 10);
      if (!cid) return state.services;
      return state.services.filter(s => parseInt(s.categoryId, 10) === cid);
    });

    // The list the grid actually renders. Generic, reusable extension seam:
    // add-ons may further narrow/transform the visible services through the
    // `bookingpress_form_v3_visible_services` JS filter (the client-side
    // analog of the PHP `FILTER_SERVICES` hook) — e.g. Pro's service search.
    // Lite ships no filter callback, so on a Lite-only install this returns
    // the category list unchanged and the form behaves exactly as before.
    //
    // The filter callbacks read their own inputs (a search keyword, etc.) which
    // live outside this computed's natural dependencies, so add-ons signal a
    // recompute by bumping `state.serviceListFilterTick`; reading it here makes
    // that bump a tracked dependency. No REST/AJAX is involved — purely a
    // client-side transform of the already-loaded list.
    const filteredServices = computed(() => {
      // eslint-disable-next-line no-unused-expressions
      state.serviceListFilterTick; // tracked recompute signal for add-on filters
      let list = categoryServices.value;
      const hooks = (typeof window !== 'undefined' && window.wp && window.wp.hooks) || null;
      if (hooks && typeof hooks.applyFilters === 'function') {
        const out = hooks.applyFilters('bookingpress_form_v3_visible_services', list, {
          state,
          instanceId: state.instanceId,
        });
        if (Array.isArray(out)) list = out;
      }
      return list;
    });

    // Tab name of the step that comes after `service` — composes the
    // "Next: <Date & Time>" label on the primary button. Sourced from
    // the step schema so admin-side renames flow through automatically.
    const nextStepName = computed(() => {
      const current = state.steps.find(s => s.id === state.currentTab);
      if (!current || !current.next_step) return '';
      const next = state.steps.find(s => s.id === current.next_step);
      return (next && next.tab_name) || '';
    });

    // Whether this step has a *visible* previous step in the (possibly
    // Pro-reordered) schema. Lite's default order keeps Service first, so this
    // is false and the back button never renders. Pro's "Booking form
    // sequence" can place Basic Details before Service, in which case Service
    // gains a back button. Visibility-aware via nav: a hidden previous step
    // (is_display_step=0 stays in the array) must not count, or goPrev()
    // would be a no-op.
    const hasPrev = nav.hasPrev;

    function selectService(serviceId) {
      selectedService.value = String(serviceId);

      // Released-form parity: clicking a service auto-advances to the next
      // step (the legacy `selectDate(..., 'true')` path with
      // is_move_to_next === 'true'). Skip the advance if a pre-select
      // cancel hook rolled back the selection (selectedService getter
      // reflects the post-rollback value).
      if (String(selectedService.value) === String(serviceId)) {
        // GENERIC seam: an add-on may hold the user on the Service step for
        // THIS pick while keeping the selection (the legacy
        // `is_move_to_next = false` path — e.g. Pro's Service Extras drawer
        // opens over the step and owns the Continue). The flag is set
        // synchronously by a `bp-v3:after-select-service` listener and
        // consumed per pick. Inert in Lite (never set → advances as before).
        if (state.serviceAdvanceHold) {
          state.serviceAdvanceHold = false;
          return;
        }
        nav.goNext();
      }
    }

    function selectCategory(catId) {
      selectedCategory.value = String(catId);
    }

    function formatPrice(p) {
      return formatPriceUtil(state.config, p);
    }

    // Effective per-service price shown in the grid. Default = the service's
    // own `servicePrice`; a Pro filter (the Staff Member module) overrides it
    // with the SELECTED staff's price for that service via the generic
    // `bookingpress_form_v3_effective_price` seam — the SAME seam the Summary
    // total uses. So when the Staff step precedes Service in the sequence and a
    // staff is already chosen, the grid shows that staff's price instead of the
    // base price. Reading `selected_staff_member_id` here makes a staff change a
    // tracked render dependency (mirrors SummaryStep's total). Inert in Lite
    // (no filter registered → returns the base price unchanged).
    function serviceDisplayPrice(svc) {
      // eslint-disable-next-line no-unused-expressions
      state.appointment_step_form_data.selected_staff_member_id;
      // `'grid'` context — the per-service card price reflects only the service /
      // staff price, NOT order-total add-ons (extras, ×quantity, coupon). Those
      // opt out of 'grid' so they show only on the Summary total.
      return effectivePrice(state, svc.servicePrice, svc.serviceId, 'grid');
    }

    function categoryClass(catId) {
      return (
        'bpa-front-ci-pill el-tag el-tag--light bpa_focusable' +
        (String(catId) === selectedCategory.value ? ' __bpa-is-active' : '')
      );
    }

    // Generic multi-select awareness — inert in Lite (`selected_services` is empty
    // unless a multi-select add-on, e.g. Multi Service Booking, populates it). When
    // populated, EVERY selected service card shows the selected state + checkmark
    // (legacy multi-service parity), not just the single `selected_service`.
    function bpaSelectedServiceIds() {
      const arr = state.appointment_step_form_data && state.appointment_step_form_data.selected_services;
      return Array.isArray(arr) ? arr.map((x) => String(parseInt(x, 10))) : [];
    }
    function isServiceSelected(svc) {
      const id = String(svc.serviceId);
      if (id === selectedService.value) return true;
      return bpaSelectedServiceIds().indexOf(id) !== -1;
    }
    const selectedServicesCount = computed(() => bpaSelectedServiceIds().length);
    const multiServiceEnabled = computed(() => !!(state.config && state.config.multiServiceEnabled));
    const selectedCountLabel = computed(() => (state.strings && state.strings.ms_selected_label) || 'selected');

    function serviceItemClass(svc) {
      const cls = ['bpa-front-module--service-item'];
      if (isServiceSelected(svc)) cls.push('__bpa-is-selected');
      if (svc.serviceDescription) cls.push('__bpa-is-description-enable');
      return cls.join(' ');
    }

    // Toast error surfaced when the user clicks "Next" without picking
    // a service — released-form parity.
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
      // Validate: a service must be selected. If not, surface the
      // configured `no_service_selected_for_the_booking` message instead
      // of silently no-op'ing on a disabled button.
      if (!selectedService.value) {
        setError(state.strings.no_service_selected_error);
        return;
      }
      const hooks = (typeof window !== 'undefined' && window.wp && window.wp.hooks)
        ? window.wp.hooks
        : null;
      const featureError = hooks && typeof hooks.applyFilters === 'function'
        ? String(hooks.applyFilters(
          'bookingpress_form_v3_service_step_validation_error',
          '',
          { state }
        ) || '')
        : '';
      if (featureError) {
        setError(featureError);
        return;
      }
      clearError();
      nav.goNext();
    }

    function prev() {
      clearError();
      nav.goPrev();
    }

    // --- Keyboard navigation (APG listbox pattern, roving tabindex) --------
    //
    // Categories: horizontal single-select listbox. Arrow keys move focus,
    // Enter/Space activates (explicit activation — selecting re-filters the
    // grid, so selection deliberately does NOT follow focus while browsing).
    const catRov = useRovingTabindex({
      count: () => state.categories.length,
      selectedIndex: () =>
        state.categories.findIndex((c) => String(c.categoryId) === selectedCategory.value),
      onActivate: (i) => {
        const cat = state.categories[i];
        if (cat) selectCategory(cat.categoryId);
      },
    });

    // Services: grid-flowing listbox. Left/Right step ±1, Up/Down move by
    // one visual row (column count measured from the live layout), Home/End
    // jump to first/last. Enter/Space selects and auto-advances (same as
    // click — consistent interaction model).
    const svcRov = useRovingTabindex({
      grid: true,
      count: () => filteredServices.value.length,
      selectedIndex: () => filteredServices.value.findIndex((s) => isServiceSelected(s)),
      onActivate: (i) => {
        const svc = filteredServices.value[i];
        if (svc) selectService(svc.serviceId);
      },
    });

    return {
      catRov,
      svcRov,
      state,
      selectedCategory,
      selectedService,
      categoryServices,
      filteredServices,
      nextStepName,
      hasPrev,
      selectService,
      selectCategory,
      formatPrice,
      serviceDisplayPrice,
      categoryClass,
      serviceItemClass,
      isServiceSelected,
      selectedServicesCount,
      multiServiceEnabled,
      selectedCountLabel,
      next,
      prev,
      nav,
      errorMsg,
      ICON_CHECKMARK,
      ICON_MULTISERVICE_CHECKMARK,
      ICON_MULTISERVICE_PLUS,
      ICON_SERVICE_PLACEHOLDER,
      ICON_ARROW_RIGHT,
      ICON_ARROW_LEFT,
    };
  },
  template: `
    <div class="bpa-front-default-card">
      <!-- Validation toast — mirrors the released form's
           .bpa-front-toast-notification element. Shows when the user
           clicks Next without picking a service. -->
      <div v-if="errorMsg" class="bpa-front-toast-notification --bpa-error" :aria-label="errorMsg" role="alert">
        <div class="bpa-front-tn-body">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 7c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1s-1-.45-1-1V8c0-.55.45-1 1-1zm-.01-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm1-3h-2v-2h2v2z"/></svg>
          <p>{{ errorMsg }}</p>
        </div>
      </div>

      <div class="bpa-front-dc--body">
        <!-- The before-list slot is the mount point Pro's "Enable Searchbox"
             feature renders its service-search input into (see the Pro
             SearchBoxFeature module). Lite ships no UI here. -->
        <div class="bp-v3-slot" data-bp-v3-slot="service-step:before-list" :data-bp-v3-instance="state.instanceId"></div>

        <div v-if="state.categories.length > 0" class="bpa-front-module-container bpa-front-module--category">
          <div class="bpa-front-module-heading" role="heading" aria-level="2" tabindex="-1" data-bp-step-heading :id="'bp-v3-cat-heading-' + state.instanceId">{{ state.strings.category_heading }}</div>
          <div class="bpa-front-cat-items-wrapper">
            <div class="bpa-front-cat-items" role="listbox" aria-orientation="horizontal" :aria-labelledby="'bp-v3-cat-heading-' + state.instanceId">
              <span
                v-for="(cat, ci) in state.categories"
                :key="cat.categoryId"
                :class="categoryClass(cat.categoryId)"
                role="option"
                :aria-selected="String(cat.categoryId) === selectedCategory ? 'true' : 'false'"
                :tabindex="catRov.tabindexFor(ci)"
                :ref="(el) => catRov.setItemRef(el, ci)"
                @focus="catRov.onItemFocus(ci)"
                @keydown="catRov.onKeydown($event, ci)"
                @click="selectCategory(cat.categoryId)"
              >
                <div class="bpa-front-ci-item-title">{{ cat.categoryName }}</div>
                <span v-if="String(cat.categoryId) === selectedCategory" aria-hidden="true" v-html="ICON_CHECKMARK"></span>
              </span>
            </div>
          </div>
        </div>

        <div class="bpa-front-module-container bpa-front-module--service">
          <div class="bpa-front-module-heading" role="heading" aria-level="2" tabindex="-1" data-bp-step-heading :id="'bp-v3-svc-heading-' + state.instanceId">
            <span>{{ state.strings.service_heading }}</span>
            <span v-if="multiServiceEnabled" class="bpa-front-si__selected-count">{{ selectedServicesCount }} {{ selectedCountLabel }}</span>
          </div>
          <!-- Empty state keys off categoryServices (pre-add-on-filter): when
               the category itself has no services, show the message. When an
               add-on filter (e.g. Pro service search) narrows the list to zero,
               render neither the message nor the grid. -->
          <div v-if="categoryServices.length === 0" class="bpa-front-empty">
            {{ state.strings.no_service_text }}
          </div>
          <div v-else-if="filteredServices.length > 0" class="bpa-front-module--service-items-row" data-group="services" role="listbox" :aria-labelledby="'bp-v3-svc-heading-' + state.instanceId" :aria-multiselectable="multiServiceEnabled ? 'true' : null">
            <div
              v-for="(svc, si) in filteredServices"
              :key="svc.serviceId"
              class="bpa-fm--si--col"
              role="presentation"
            >
              <div :class="serviceItemClass(svc)" role="presentation">
                <div
                  class="bpa-front-si-card bpa_focusable"
                  role="option"
                  :aria-selected="isServiceSelected(svc) ? 'true' : 'false'"
                  :tabindex="svcRov.tabindexFor(si)"
                  :ref="(el) => svcRov.setItemRef(el, si)"
                  @focus="svcRov.onItemFocus(si)"
                  @keydown="svcRov.onKeydown($event, si)"
                  @click="selectService(svc.serviceId)"
                >
                  <div v-if="!multiServiceEnabled && isServiceSelected(svc)" class="bpa-front-si-card--checkmark-icon" aria-hidden="true" v-html="ICON_CHECKMARK"></div>
                  <div v-if="multiServiceEnabled" class="bpa-front-si-card--checkmark-icon-multiservice" aria-hidden="true">
                    <div v-if="isServiceSelected(svc)" v-html="ICON_MULTISERVICE_CHECKMARK"></div>
                    <div v-else v-html="ICON_MULTISERVICE_PLUS"></div>
                  </div>
                  <div class="bpa-front-si-card__left" v-if="svc.avatarUrl">
                    <img :src="svc.avatarUrl" :alt="svc.serviceName">
                  </div>
                  <div class="bpa-front-si-card__left" v-else>
                    <div class="bpa-front-si__default-img" aria-hidden="true" v-html="ICON_SERVICE_PLACEHOLDER"></div>
                  </div>
                  <div class="bpa-front-si__card-body">
                    <div class="bpa-front-si__card-body--heading">{{ svc.serviceName }}</div>
                    <p v-if="svc.serviceDescription && state.config.displayServiceDescription" class="--bpa-is-desc" v-html="svc.serviceDescription"></p>
                    <div class="bpa-front-si-cb__specs">
                      <div v-if="state.config.displayServiceDuration" class="bpa-front-si-cb__specs-item">
                        <p>{{ state.strings.service_duration_label }} <strong>{{ svc.serviceDurationVal }} {{ svc.serviceDurationUnit }}</strong></p>
                      </div>
                      <div v-if="serviceDisplayPrice(svc) > 0 && state.config.displayServicePrice" class="bpa-front-si-cb__specs-item">
                        <p>{{ state.strings.service_price_label }} <strong class="--is-service-price">{{ formatPrice(serviceDisplayPrice(svc)) }}</strong></p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bp-v3-slot" data-bp-v3-slot="service-step:after-list" :data-bp-v3-instance="state.instanceId"></div>
      </div>

      <div class="bpa-front-dc--footer">
        <div class="bpa-front-tabs--foot">
          <!-- Back button — only when the (Pro-reordered) schema gives this
               step a previous step. Lite's default keeps Service first, so it
               stays hidden and the footer is unchanged. -->
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
