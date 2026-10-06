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
	
	async function refreshWpNonce() {
    try {
		const refreshPayload = {
      		instanceId: String(cfg.instanceId || ''),
    	};
		
      const res = await fetch(`${root}/refresh-nonce`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(refreshPayload),
      });
      const json = await res.json();
      if (json && json.ok && json.data && json.data.wp_rest_nonce ) {
        	cfg.wpRestNonce   = json.data.wp_rest_nonce;
		  cfg.formNonce     = json.data.form_nonce;
		  //cfg.instanceId    = json.data.instanceId;
		  cfg.instanceToken = json.data.instanceToken;

		  
        return true;
      }
    } catch (_err) { /* fall through */ }
    return false;
  }
	
  async function post(route, body, _retried) {
    const payload = {
      ...(body || {}),
      bp_v3_nonce: cfg.formNonce,
      bp_v3_instance_token: cfg.instanceToken,
      instanceId: cfg.instanceId,
    };
    if( 'submit' == route ){
      if( "undefined" != typeof BookingPressFormV3 ){
        var formState = BookingPressFormV3.instances[ cfg.instanceId ].state;
        if( "undefined" != typeof formState.config.current_locale && formState.config.current_locale ){
          payload.locale = formState.config.current_locale;
        }
      }
    }
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
	  
	 if (res.status === 403 && json && (json.code === 'rest_cookie_invalid_nonce' || json.code === 'bp_v3_invalid_form_nonce' ||  json.code === 'bp_v3_invalid_instance') && !_retried) {
      const refreshed = await refreshWpNonce();
      if (refreshed) return post(route, body, true);
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
