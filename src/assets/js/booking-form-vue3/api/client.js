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

  // The tokens are minted when the page is rendered, and a page cache can
  // serve that markup long after they expire (`wp_rest` lives 12-24h, the
  // instance token 12h). Re-issue them once they pass this age, so nothing
  // — the form or a payment module reading `state.nonces` — ever sends a
  // stale set. Kept under both lifetimes.
  const TOKEN_MAX_AGE_MS = 10 * 60 * 60 * 1000;
  const TOKEN_CHECK_EVERY_MS = 5 * 60 * 1000;

  // `issuedAt` is server time in seconds. Missing (older server) → count
  // from now, so a long-open tab is still covered.
  let tokensIssuedAt = Number(cfg.issuedAt) > 0 ? Number(cfg.issuedAt) * 1000 : Date.now();
  let refreshInFlight = null;

  // One request at a time: the age check and a 403 retry can both ask at once.
  function refreshWpNonce() {
    if (!refreshInFlight) {
      refreshInFlight = requestFreshTokens().finally(() => { refreshInFlight = null; });
    }
    return refreshInFlight;
  }

  function refreshIfStale() {
    if (Date.now() - tokensIssuedAt >= TOKEN_MAX_AGE_MS) refreshWpNonce();
  }

  function keepTokensFresh() {
    if (typeof window === 'undefined') return;
    refreshIfStale();
    // Timers are throttled in background tabs, so also check the moment the
    // customer comes back to the page.
    window.setInterval(refreshIfStale, TOKEN_CHECK_EVERY_MS);
    if (typeof document !== 'undefined') {
      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') refreshIfStale();
      });
    }
  }

	async function requestFreshTokens() {
    try {
		const refreshPayload = {
      		instanceId: String(cfg.instanceId || ''),
    	};
		
      const res = await fetch(`${root}/refresh-nonce`, {
        method: 'POST',
        credentials: 'same-origin',
        cache: 'no-store',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(refreshPayload),
      });
      const json = await res.json();
      if (json && json.ok && json.data && json.data.wp_rest_nonce ) {
        	cfg.wpRestNonce   = json.data.wp_rest_nonce;
		  cfg.formNonce     = json.data.form_nonce;
		  //cfg.instanceId    = json.data.instanceId;
		  cfg.instanceToken = json.data.instanceToken;
		  tokensIssuedAt    = Date.now();

		  // Payment modules read the tokens from `state.nonces` (via the
		  // payment host), not from this cfg. Hand the fresh set back so they
		  // stop posting the stale, page-cached ones.
		  if (typeof cfg.onTokensRefreshed === 'function') {
		    try {
		      cfg.onTokensRefreshed({
		        wpRestNonce:   cfg.wpRestNonce,
		        formNonce:     cfg.formNonce,
		        instanceToken: cfg.instanceToken,
		      });
		    } catch (_e) { /* noop */ }
		  }

		  
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
	  
	 const failureCode = (json && (json.code || (json.error && json.error.code))) || '';
	 const isStaleAuth = (
	   failureCode === 'rest_cookie_invalid_nonce'
	   || failureCode === 'bp_v3_rest_invalid_nonce'
	   || failureCode === 'bp_v3_invalid_form_nonce'
	   || failureCode === 'bp_v3_invalid_instance'
	 );

	 if (!res.ok && isStaleAuth && !_retried) {
      const refreshed = await refreshWpNonce();
      if (refreshed) return post(route, body, true);
    }
	  
    return { status: res.status, ok: !!(json && json.ok), data: json && json.data, error: json && json.error, errors: json && json.errors, raw: json };
  }

  keepTokensFresh();

  return {
    refreshNonces() {
      return refreshWpNonce();
    },
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
