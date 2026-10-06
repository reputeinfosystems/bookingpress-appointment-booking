/**
 * card-fields.js — the card-detail fields for direct-API gateways, built with
 * the BookingPress UI library.
 *
 * WHY THIS EXISTS
 * ---------------
 * Gateways that take the card themselves (Authorize.Net, PayPal Pro) mount
 * their fields through the payment host (`host.mount(render)`), which hands
 * them a bare DOM node. Authorize.Net filled it with hand-written `<input>` /
 * `<select>` markup that only imitated the booking form's controls, and
 * PayPal Pro rendered nothing at all — it relied on a card block the Vue 3
 * pages do not have. This module gives every such gateway the SAME fields,
 * built from the real `bp-ui` components, on every host: the booking form,
 * Complete Payment, Gift Card, Package and Waiting List.
 *
 * THE CARD STAYS WITH THE GATEWAY
 * -------------------------------
 * Values are written into the plain `state` object the gateway passes in and
 * nowhere else — never onto the host's form data — so the PAN is not part of
 * anything a host might serialise, persist or send to `prepare`. The gateway
 * reads the card back from that object on submit, as it did before.
 *
 * Usage:
 *
 *     import { mountCardFields } from 'bookingpress-payments-card-fields';
 *     const unmount = mountCardFields(node, { state, labels });
 *     // ...on deselect: unmount();
 *
 * Each call creates a small, self-contained Vue app on `node`. It depends on
 * the `vue` and `bookingpress-ui` modules every payment host already loads.
 */

import { createApp, reactive } from 'vue';
import BookingPressUI from 'bookingpress-ui';

const FIELDS = ['card_holder_name', 'card_number', 'expire_month', 'expire_year', 'cvv'];

/** How many years ahead the expiry-year list offers. */
const YEARS_AHEAD = 20;

/**
 * Group card digits in fours for legibility. The gateway strips whitespace
 * before the number is ever sent.
 *
 * @param {string} value
 * @returns {string}
 */
function groupCardNumber(value) {
  const digits = String(value || '').replace(/\D/g, '').slice(0, 19);
  return digits.replace(/(.{4})/g, '$1 ').trim();
}

/**
 * Mount the card fields into a node.
 *
 * @param {HTMLElement} node
 * @param {object}      options
 * @param {object}      options.state   Plain object the values are written to
 *                                      (`card_holder_name`, `card_number`,
 *                                      `expire_month`, `expire_year`, `cvv`).
 *                                      Existing values are shown, so a
 *                                      half-typed card survives a re-mount.
 * @param {object}      [options.labels] `{ title, card_holder_name, card_number,
 *                                      expire_month, expire_year, cvv }`.
 * @returns {function} unmount
 */
export function mountCardFields(node, options) {
  if (!node) return () => {};

  const opts = options || {};
  const target = opts.state || {};
  const labels = Object.assign(
    {
      title: 'Card Details',
      card_holder_name: 'Card Holder Name',
      card_number: 'Card Number',
      expire_month: 'MM',
      expire_year: 'YYYY',
      cvv: 'CVV',
    },
    opts.labels || {}
  );

  const months = [];
  for (let m = 1; m <= 12; m++) {
    const mm = (m < 10 ? '0' : '') + m;
    months.push({ value: mm, label: mm });
  }

  const years = [];
  const thisYear = new Date().getFullYear();
  for (let y = thisYear; y <= thisYear + YEARS_AHEAD; y++) {
    years.push({ value: String(y), label: String(y) });
  }

  const app = createApp({
    name: 'BookingPressCardFields',

    setup() {
      const card = reactive({});
      FIELDS.forEach((field) => {
        card[field] = target[field] ? String(target[field]) : '';
      });

      // Mirror every change into the gateway's own object.
      function set(field, value) {
        let next = value == null ? '' : String(value);
        if (field === 'card_number') next = groupCardNumber(next);
        if (field === 'cvv') next = next.replace(/\D/g, '').slice(0, 4);
        card[field] = next;
        target[field] = next;
      }

      return { card, labels, months, years, set };
    },

    // The same markup and controls as Complete Payment's own card block, so
    // the fields inherit the page's existing styling on every host.
    template: `
      <div class="bpa-front-module--pm-card-detail-form bpa-card-fields-v3">
        <div class="bpa-front-cdf__title" :aria-label="labels.title">{{ labels.title }}</div>
        <bp-ui-row>
          <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <bp-ui-input class="bpa-front-form-control" :model-value="card.card_holder_name" @update:model-value="set('card_holder_name', $event)" :placeholder="labels.card_holder_name" :aria-label="labels.card_holder_name" autocomplete="cc-name"></bp-ui-input>
          </bp-ui-col>
        </bp-ui-row>
        <bp-ui-row>
          <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <bp-ui-input class="bpa-front-form-control" :model-value="card.card_number" @update:model-value="set('card_number', $event)" :placeholder="labels.card_number" :aria-label="labels.card_number" maxlength="23" inputmode="numeric" autocomplete="cc-number"></bp-ui-input>
          </bp-ui-col>
        </bp-ui-row>
        <bp-ui-row :gutter="16">
          <bp-ui-col :xs="24" :sm="24" :md="24" :lg="20" :xl="20">
            <bp-ui-row :gutter="12">
              <bp-ui-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                <bp-ui-select class="bpa-front-form-control" popper-class="bpa-custom-dropdown" :model-value="card.expire_month" @update:model-value="set('expire_month', $event)" :placeholder="labels.expire_month" :aria-label="labels.expire_month">
                  <bp-ui-option v-for="m in months" :key="m.value" :label="m.label" :value="m.value"></bp-ui-option>
                </bp-ui-select>
              </bp-ui-col>
              <bp-ui-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                <bp-ui-select class="bpa-front-form-control" popper-class="bpa-custom-dropdown" :model-value="card.expire_year" @update:model-value="set('expire_year', $event)" :placeholder="labels.expire_year" :aria-label="labels.expire_year">
                  <bp-ui-option v-for="y in years" :key="y.value" :label="y.label" :value="y.value"></bp-ui-option>
                </bp-ui-select>
              </bp-ui-col>
            </bp-ui-row>
          </bp-ui-col>
          <bp-ui-col :xs="24" :sm="24" :md="24" :lg="4" :xl="4">
            <bp-ui-input class="bpa-front-form-control" :model-value="card.cvv" @update:model-value="set('cvv', $event)" :placeholder="labels.cvv" :aria-label="labels.cvv" maxlength="4" inputmode="numeric" autocomplete="cc-csc"></bp-ui-input>
          </bp-ui-col>
        </bp-ui-row>
      </div>
    `,
  });

  if (BookingPressUI) {
    app.use(BookingPressUI);
  }

  // Mount into our OWN child, never onto `node` itself. A host slot can be
  // shared: on the booking form Pro's CardDetailsForm also mounts a Vue app
  // into `summary-step:above-actions`. `app.mount(node)` would wipe that app's
  // DOM and leave the two apps patching the same container.
  const container = document.createElement('div');
  container.className = 'bpa-card-fields-v3__mount';
  node.appendChild(container);

  app.mount(container);

  let mounted = true;
  return function unmount() {
    if (!mounted) return;
    mounted = false;
    try { app.unmount(); } catch (e) { /* noop */ }
    if (container.parentNode) {
      container.parentNode.removeChild(container);
    }
  };
}
