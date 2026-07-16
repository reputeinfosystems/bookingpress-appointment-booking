/**
 * ThankYouStep — shown after a successful submit while the page is about
 * to redirect to the legacy thank-you URL.
 */
import { inject } from 'vue';

export default {
  name: 'ThankYouStep',
  setup() {
    const state = inject('state');
    return { state };
  },
  template: `
    <div class="bp-v3-thankyou" role="status" aria-live="polite">
      <div class="bp-v3-thankyou-icon" aria-hidden="true">&#10003;</div>
      <h2 class="bp-v3-thankyou-title">Booking confirmed</h2>
      <p class="bp-v3-thankyou-message">Redirecting to your confirmation page…</p>
      <p v-if="state.redirectUrl" class="bp-v3-thankyou-fallback">
        <a :href="state.redirectUrl">Continue manually</a>
      </p>
    </div>
  `,
};
