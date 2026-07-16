/**
 * StepNav — vertical-left sidebar listing each visible step with click
 * navigation gated by readiness.canEnterStep.
 *
 * Markup mirrors the released `.bpa-front-tab-menu` / `.bpa-front-tab-menu--item`
 * structure 1:1 so the released `booking-form.css` rules apply. Step labels
 * remain dynamic — they come from `step.tab_name`, which is sourced from the
 * customize-settings layer in StateBuilder (so backend renames automatically
 * propagate to the form).
 */
import { inject } from 'vue';

/**
 * Inline SVGs keyed by canonical step id. The released form renders these
 * exact paths inside `.bpa-front-tm--item-icon.material-icons-round`. Any
 * unknown step falls back to the `service` icon.
 */
const STEP_ICON_SVG = {
  service:
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">' +
      '<path d="M0 0h24v24H0V0z" fill="none"></path>' +
      '<path d="M19 13H5c-1.1 0-2 .9-2 2v4c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-4c0-1.1-.9-2-2-2zM7 19c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM19 3H5c-1.1 0-2 .9-2 2v4c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM7 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"></path>' +
    '</svg>',
  datetime:
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">' +
      '<path d="M0 0h24v24H0V0z" fill="none"></path>' +
      '<path d="M19 4h-1V3c0-.55-.45-1-1-1s-1 .45-1 1v1H8V3c0-.55-.45-1-1-1s-1 .45-1 1v1H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 15c0 .55-.45 1-1 1H6c-.55 0-1-.45-1-1V9h14v10zM7 11h2v2H7zm4 0h2v2h-2zm4 0h2v2h-2z"></path>' +
    '</svg>',
  basic_details:
    '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24">' +
      '<g><rect fill="none" height="24" width="24"></rect>' +
      '<path d="M19,3H5C3.9,3,3,3.9,3,5v14c0,1.1,0.9,2,2,2h14c1.1,0,2-0.9,2-2V5C21,3.9,20.1,3,19,3z M13,17H8c-0.55,0-1-0.45-1-1 c0-0.55,0.45-1,1-1h5c0.55,0,1,0.45,1,1C14,16.55,13.55,17,13,17z M16,13H8c-0.55,0-1-0.45-1-1c0-0.55,0.45-1,1-1h8 c0.55,0,1,0.45,1,1C17,12.55,16.55,13,16,13z M16,9H8C7.45,9,7,8.55,7,8c0-0.55,0.45-1,1-1h8c0.55,0,1,0.45,1,1 C17,8.55,16.55,9,16,9z"></path></g>' +
    '</svg>',
  summary:
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">' +
      '<path d="M0 0h24v24H0V0z" fill="none"></path>' +
      '<path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1s-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9.29 16.29L6.7 13.7c-.39-.39-.39-1.02 0-1.41.39-.39 1.02-.39 1.41 0L10 14.17l5.88-5.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-6.59 6.59c-.38.39-1.02.39-1.41 0z"></path>' +
    '</svg>',
};

export default {
  name: 'StepNav',
  setup() {
    const state = inject('state');
    const nav   = inject('nav');

    function iconFor(step) {
      // A step may carry its own inline-SVG icon in `tab_icon` (used by
      // Pro-injected steps such as Staff Member, set via the FILTER_STEPS
      // seam). Fall back to the built-in icon map, then to the service icon.
      // Lite's built-in steps leave `tab_icon` empty, so they use the map.
      const custom = step && typeof step.tab_icon === 'string' ? step.tab_icon.trim() : '';
      if (custom.slice(0, 4).toLowerCase() === '<svg') return custom;
      return STEP_ICON_SVG[step && step.id] || STEP_ICON_SVG.service;
    }

    function itemClass(step) {
      const cls = ['bpa-front-tab-menu--item', 'bpa_focusable'];
      if (step.id === state.currentTab) cls.push('__bpa-is-active');
      if (!nav.isClickable(step.id))    cls.push('bpa-front-disabled-menu-item');
      return cls.join(' ');
    }

    function onClick(step, event) {
      if (event) event.preventDefault();
      if (nav.isClickable(step.id)) nav.goTo(step.id);
    }

    function onKeydown(step, event) {
      if (!nav.isClickable(step.id)) return;
      if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar') {
        event.preventDefault();
        nav.goTo(step.id);
      }
    }

    /** "Service, step 1 of 4" — gives screen readers the position context
     *  the visual layout communicates spatially. */
    function itemLabel(step, index) {
      const total = nav.visibleSteps.value.length;
      const tpl = (state.strings && state.strings.step_nav_item_label) || '%1$s, step %2$s of %3$s';
      return tpl
        .replace('%1$s', String(step.tab_name || ''))
        .replace('%2$s', String(index + 1))
        .replace('%3$s', String(total));
    }

    const navLabel = (state.strings && state.strings.step_nav_label) || 'Booking steps';

    return { state, nav, iconFor, itemClass, itemLabel, navLabel, onClick, onKeydown };
  },
  template: `
    <div class="bpa-front-tab-menu" role="navigation" :aria-label="navLabel">
      <a
        v-for="(step, i) in nav.visibleSteps.value"
        :key="step.id"
        href="#"
        :class="itemClass(step)"
        :aria-current="step.id === state.currentTab ? 'step' : null"
        :aria-disabled="!nav.isClickable(step.id) ? 'true' : null"
        :aria-label="itemLabel(step, i)"
        tabindex="0"
        @click="onClick(step, $event)"
        @keydown="onKeydown(step, $event)"
      >
        <span class="bpa-front-tm--item-icon material-icons-round" aria-hidden="true" v-html="iconFor(step)"></span>
        <div class="bpa-front-tm--item-label">{{ step.tab_name }}</div>
      </a>
    </div>
  `,
};
