/**
 * App — root component for one form instance.
 *
 * Provides `state`, `api`, `readiness`, `nav`, `timeslots`, `submission`,
 * `bus` to descendants via Vue's `provide()`. Switches the rendered step
 * panel based on `state.currentTab`.
 *
 * Outer markup mirrors the released `[bookingpress_form]` shell so that
 * `booking-form.css` (`.bpa-frontend-vue3-root`, `.bpa-front-tabs`,
 * `.bpa-front-tabs--panel-body`, `.bpa-front-default-card`, ...) applies
 * unchanged. The step components are progressively migrated to render the
 * matching `.bpa-front-dc--body` / `.bpa-front-dc--footer` internals.
 */
// Bump `?v=` on these relative imports whenever the corresponding component
// file changes; needed because relative ES-module imports bypass WordPress's
// `?ver=` cache-buster.
import { computed, nextTick, provide, ref, watch } from 'vue';
import StepNav from './shared/StepNav.js?v=4';
import EmptyPlaceholder from './shared/EmptyPlaceholder.js?v=1';
import ServiceStep from './steps/ServiceStep.js?v=19';
import DateTimeStep from './steps/DateTimeStep.js?v=27';
import BasicDetailsStep from './steps/BasicDetailsStep.js?v=15';
import SummaryStep from './steps/SummaryStep.js?v=22';

export default {
  name: 'BookingFormApp',
  components: { StepNav, EmptyPlaceholder, ServiceStep, DateTimeStep, BasicDetailsStep, SummaryStep },
  props: {
    instanceId: { type: String, required: true },
    state:      { type: Object, required: true },
    api:        { type: Object, required: true },
    readiness:  { type: Object, required: true },
    nav:        { type: Object, required: true },
    timeslots:  { type: Object, required: true },
    submission: { type: Object, required: true },
    bus:        { type: Object, required: true },
  },
  setup(props) {
    provide('state',       props.state);
    provide('api',         props.api);
    provide('readiness',   props.readiness);
    provide('nav',         props.nav);
    provide('timeslots',   props.timeslots);
    provide('submission',  props.submission);
    provide('bus',         props.bus);

    const currentTab = computed(() => props.state.currentTab);

    // --- Keyboard / screen-reader step-transition support -----------------
    //
    // `liveMessage` feeds the visually-hidden `aria-live="polite"` region in
    // the template. Clearing before re-setting (with a small delay) forces
    // screen readers to announce even when the same text repeats.
    const liveMessage = ref('');
    let liveTimer = 0;
    function announce(msg) {
      liveMessage.value = '';
      if (liveTimer) clearTimeout(liveTimer);
      liveTimer = setTimeout(() => {
        liveMessage.value = String(msg || '');
        liveTimer = 0;
      }, 60);
    }
    provide('announce', announce);

    /** Root element ref — scopes all focus queries to THIS instance so
     *  multiple forms on one page never steal each other's focus. */
    const rootEl = ref(null);

    // On every step change: announce "Step X of N: <name>" politely and move
    // focus to the incoming step's heading (`[data-bp-step-heading]`,
    // stamped by each step component with tabindex="-1"). Announce-then-focus
    // keeps context-first reading order for screen reader users while giving
    // keyboard users a deterministic starting point — the next Tab lands on
    // the step's first interactive element.
    watch(currentTab, (to, from) => {
      if (!to || !from || to === from) return;
      const steps = visibleSteps.value;
      const idx   = steps.findIndex((s) => s.id === to);
      const step  = idx !== -1 ? steps[idx] : null;
      const tpl = (props.state.strings && props.state.strings.step_change_announcement)
        || 'Step %1$s of %2$s: %3$s';
      announce(
        tpl
          .replace('%1$s', String(idx + 1))
          .replace('%2$s', String(steps.length))
          .replace('%3$s', step ? String(step.tab_name || '') : '')
      );
      nextTick(() => {
        const root = rootEl.value;
        if (!root) return;
        const heading = root.querySelector(
          '.bpa-front-tabs--panel-body.__bpa-is-active [data-bp-step-heading]'
        );
        if (heading && typeof heading.focus === 'function') {
          try { heading.focus({ preventScroll: false }); } catch (_e) { heading.focus(); }
        }
      });
    });

    // Escape = "Go Back" for keyboard users (skip/back requirement). Only
    // when nothing interactive consumed the key first (open dropdowns and
    // popovers preventDefault their own Escape), never mid-submit, and only
    // when a previous step actually exists.
    function onRootKeydown(event) {
      if (event.key !== 'Escape' && event.key !== 'Esc') return;
      if (event.defaultPrevented) return;
      const t = event.target;
      if (t && typeof t.closest === 'function'
        && t.closest('.vti__dropdown.open, .vc-popover-content-wrapper, [aria-expanded="true"]')) {
        return;
      }
      if (props.submission.isSubmitting.value) return;
      const cur = props.nav.currentStep.value;
      if (!cur || !cur.previous_step) return;
      event.preventDefault();
      props.nav.goPrev();
    }

    // Generic "block the whole form" flag — the Vue 3 analog of the legacy
    // `bookingpress_display_no_service_placeholder`. When true the step tabs
    // are hidden and the `#bpa-front-data-empty-view` illustration shows
    // instead (released-form parity). Lite seeds it false (inert); a Pro
    // feature flips it via `FILTER_INITIAL_STATE` — e.g. the Staff Member
    // module blocks the form when it is active but no staff is available.
    const showEmptyPlaceholder = computed(
      () => !!(props.state.config && props.state.config.showEmptyPlaceholder)
    );

    // Built-in step ids that App.js renders with a dedicated component. Any
    // other display step in the schema (injected by a Pro feature via the
    // `FILTER_STEPS` seam — e.g. the Staff Member step) is rendered as a
    // generic `step:<id>` slot panel below, so the add-on owns the step UI
    // while Lite stays the source of truth for the step shell + sidebar +
    // navigation (all already data-driven off `state.steps`). Lite ships no
    // such extra steps, so this is inert on a Lite-only render.
    const BUILT_IN_STEP_IDS = ['service', 'datetime', 'basic_details', 'summary'];
    const extraStepPanels = computed(() => {
      const steps = Array.isArray(props.state.steps) ? props.state.steps : [];
      return steps.filter(
        (s) => s && s.is_display_step && BUILT_IN_STEP_IDS.indexOf(s.id) === -1
      );
    });

    // The released markup renders all four panel bodies in the DOM at once
    // and toggles `__bpa-is-active` on the one matching the current tab.
    // We keep that contract so the released CSS applies verbatim — but
    // gate the inner step component with v-if so its setup/effects only
    // run while the panel is active.
    const visibleSteps = computed(() => {
      // `nav.visibleSteps` is a ref<Array>; unwrap defensively.
      const v = props.nav.visibleSteps;
      const arr = v && typeof v === 'object' && 'value' in v ? v.value : v;
      return Array.isArray(arr) ? arr : [];
    });

    function panelClass(stepId) {
      const cls = ['bpa-front-tabs--panel-body'];
      if (stepId === currentTab.value) cls.push('__bpa-is-active');
      return cls.join(' ');
    }

    // Sidebar/step-nav layout class — mirrors the legacy template
    // (`core/views/frontend/appointment_booking_form.php:49`) ternary:
    //   $pos == 'left' ? 'bpa-front-tabs--left' : '--bpa-top'
    // Sourced from `state.config.tabsPosition` (default 'left'),
    // populated by StateBuilder from the `booking_form_tabs_position`
    // backend customize key. The `bpa-front-tabs` and
    // `bpa-front-tabs--vertical-left` base classes stay on the wrapper
    // unconditionally — legacy always emits them.
    const tabsLayoutClass = computed(() => {
      const pos = (props.state.config && props.state.config.tabsPosition) || 'left';
      return pos === 'left' ? 'bpa-front-tabs--left' : '--bpa-top';
    });

    // Final wrapper class string — keeps base tokens stable and only
    // toggles the position-suffix token.
    const tabsWrapperClass = computed(
      () => 'bpa-front-tabs bpa-front-tabs--vertical-left ' + tabsLayoutClass.value
    );

    // Post-booking behaviour. Lite default: when submit succeeds with a
    // redirect URL, navigate immediately so the user goes straight to the
    // legacy thank-you page (released-form parity).
    //
    // A Pro feature (In-Built redirection) can opt out of the redirect by
    // setting `state.config.postBookingMode = 'inline'`. In that mode App.js
    // does NOT redirect; the Pro layer — which can inspect the full submit
    // envelope on the `bp-v3:after-submit` bus event — decides whether the
    // booking actually completed and, if so, sets `state.bookingComplete`
    // (+ `state.bookingOutcome` = 'success' | 'failed'). App.js reacts by
    // hiding the steps and revealing the `confirmation:body` slot, which the
    // Pro slot factory fills with the inline thank-you / failed content.
    // `postBookingMode` is a neutral seam — Lite never sets it, so a
    // Lite-only render keeps redirecting exactly as before.
    watch(() => props.submission.submitOk.value, (ok) => {
      if (!ok) return;
      const inline = props.state.config && props.state.config.postBookingMode === 'inline';
      if (inline) {
        // Pro's after-submit handler owns the inline confirmation transition.
        return;
      }
      if (props.submission.redirectUrl.value) {
        window.location.href = props.submission.redirectUrl.value;
      }
    });

    return {
      currentTab, visibleSteps, panelClass, tabsWrapperClass, showEmptyPlaceholder,
      extraStepPanels, liveMessage, rootEl, onRootKeydown,
    };
  },
  template: `
    <div class="bpa-frontend-vue3-root" ref="rootEl" @keydown="onRootKeydown">
      <!-- Polite live region — announces step transitions ("Step 2 of 4:
           Date & Time") to screen readers. Visually hidden. -->
      <div class="bp-v3-sr-only" aria-live="polite" role="status">{{ liveMessage }}</div>

      <div class="bp-v3-slot" data-bp-v3-slot="root:before-steps" :data-bp-v3-instance="state.instanceId"></div>

      <!-- Blocked-form empty illustration. Mirrors the legacy
           '#bpa-front-data-empty-view' shown when
           'bookingpress_display_no_service_placeholder' is true. Hides the
           step tabs entirely. Dormant on a Lite-only render (the flag is
           seeded false); a Pro feature flips 'config.showEmptyPlaceholder'. -->
      <empty-placeholder v-if="showEmptyPlaceholder && !state.bookingComplete" />

      <div v-if="!state.bookingComplete && !showEmptyPlaceholder" :id="'bpa-front-tabs-' + state.instanceId" :class="tabsWrapperClass">
        <step-nav />

        <div :class="panelClass('service')">
          <service-step v-if="currentTab === 'service'" />
        </div>
        <div :class="panelClass('datetime')">
          <date-time-step v-if="currentTab === 'datetime'" />
        </div>
        <div :class="panelClass('basic_details')">
          <basic-details-step v-if="currentTab === 'basic_details'" />
        </div>
        <div :class="panelClass('summary')">
          <summary-step v-if="currentTab === 'summary'" />
        </div>

        <!-- Generic panels for Pro-injected steps (e.g. Staff Member). Each is
             a mount-point slot the Pro feature renders its step UI into. The
             panel uses the same '__bpa-is-active' visibility toggle as the
             built-in panels, so only the current step shows. Inert in Lite. -->
        <div v-for="step in extraStepPanels" :key="step.id" :class="panelClass(step.id)">
          <div class="bp-v3-slot" :data-bp-v3-slot="'step:' + step.id" :data-bp-v3-instance="state.instanceId"></div>
        </div>
      </div>

      <div
        v-if="state.bookingComplete"
        class="bpa-frontend-main-container bpa-front-confirmation"
        :class="'bpa-front-confirmation--' + (state.bookingOutcome || 'success')"
      >
        <div
          class="bp-v3-slot"
          data-bp-v3-slot="confirmation:body"
          :data-bp-v3-instance="state.instanceId"
        ></div>
      </div>

      <div class="bp-v3-slot" data-bp-v3-slot="root:after-steps" :data-bp-v3-instance="state.instanceId"></div>
    </div>
  `,
};
