/**
 * api/client.js — thin fetch wrapper for the /form-v3/* REST endpoints.
 *
 * Auto-attaches the three auth tokens (per plan §4.1):
 *   - `X-WP-Nonce` HTTP header  (verifies against `wp_rest`)
 *   - `bp_v3_nonce` body field  (verifies against `bookingpress_form_v3_nonce`)
 *   - `bp_v3_instance_token` + `instanceId` body fields  (per-instance anti-replay)
 *
 * One client per Vue 3 form instance. Caller passes the instance-bound
 * `state.rest` + `state.nonces` snapshot to `createApiClient()` at mount time.
 */

/**
 * @typedef {Object} ApiConfig
 * @property {string} restRoot       Absolute URL to the `/form-v3/` namespace.
 * @property {string} instanceId     12-char per-render id.
 * @property {string} wpRestNonce    Nonce against `wp_rest` action.
 * @property {string} formNonce      Nonce against `bookingpress_form_v3_nonce` action.
 * @property {string} instanceToken  Per-render opaque token.
 */

/**
 * @param {ApiConfig} cfg
 * @returns {object} API methods.
 */
export function createApiClient(cfg) {
  const root = String(cfg.restRoot || '').replace(/\/+$/, '');

  async function post(route, body) {
    const payload = {
      ...(body || {}),
      bp_v3_nonce: cfg.formNonce,
      bp_v3_instance_token: cfg.instanceToken,
      instanceId: cfg.instanceId,
    };
    const res = await fetch(`${root}/${route}`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': cfg.wpRestNonce,
      },
      body: JSON.stringify(payload),
    });
    let json = null;
    try {
      json = await res.json();
    } catch (_err) {
      json = { ok: false, error: { code: 'bp_v3_invalid_json', message: 'Server returned non-JSON.' } };
    }
    return { status: res.status, ok: !!(json && json.ok), data: json && json.data, error: json && json.error, errors: json && json.errors, raw: json };
  }

  return {
    state(extra) {
      return post('state', extra || {});
    },
    timeslots(body) {
      return post('timeslots', body || {});
    },
    monthDetails(body) {
      return post('month-details', body || {});
    },
    submit(appointmentData) {
      return post('submit', appointmentData || {});
    },
    captcha() {
      return post('captcha', {});
    },
    validateUsername(username) {
      return post('validate-username', { username: String(username || '') });
    },
    paypalValidate(body) {
      return post('payment/paypal-validate', body || {});
    },
    paypalConfirm(body) {
      return post('payment/paypal-confirm', body || {});
    },
    paypalRedirectPrepare(body) {
      return post('payment/paypal-redirect-prepare', body || {});
    },
  };
}
