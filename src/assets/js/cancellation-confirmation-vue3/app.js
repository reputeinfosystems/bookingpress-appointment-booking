/**
 * app.js — Vue 3 application for one cancellation-confirmation instance.
 *
 * Renderer for the `[bookingpress_appointment_cancellation_confirmation]`
 * shortcode. The page markup is rendered server-side by the shortcode
 * callback; this app mounts on it as an in-DOM template (shipped `vue.min.js`
 * is the full build) and drives only the Cancel button + error toast, with
 * the same data fields as the legacy Vue 2 app. Static sections carry
 * `v-pre`, so server content is not compiled.
 *
 * The request is the EXISTING admin-ajax action
 * `bookingpress_front_cancel_appointment` (legacy `bpa_wp_nonce`).
 *
 * Add-ons extend an instance through
 *   window.BookingPressCancellationConfirmationV3.registerAddon(name, factory)
 * (see bootstrap.js). Each factory receives the per-instance `api`:
 *   api.registerBeforeCancel(fn)  // fn(body: URLSearchParams, vm) — append
 *                                 // fields; return false to abort the request
 *   api.setError(message) / api.clearError()
 *
 * Exports `mountCancellationConfirmationInstance(instanceId, initialState)`.
 */
import { createApp } from 'vue';

// eslint-disable-next-line no-console
console.info('[bp-cc-v3] app module loaded');

/**
 * Build the per-instance root component (no template → Vue compiles the
 * mount node's server-rendered innerHTML).
 *
 * @param {object} cfg Instance config from the JSON island.
 * @param {Array<Function>} beforeCancel Add-on pre-request callbacks.
 * @returns {object} Vue component options.
 */
function createCancellationConfirmationComponent(cfg, beforeCancel) {
  const strings = (cfg && cfg.strings) || {};
  const seed = (cfg && cfg.data) || {};

  return {
    data() {
      return Object.assign(
        {
          is_cancel_appointment_loader: '0',
          is_cancel_button_disabled: false,
          bookingpress_cancel_variant: '',
          bookingpress_cancel_msg: '',
        },
        seed,
        {
          bookingpress_uniq_id: cfg.instanceId || '',
          bookingpress_appointment_id: String(cfg.appointmentId || ''),
          bookingpress_cancel_token: cfg.cancelToken || '',
        }
      );
    },
    methods: {
      setError(msg) {
        this.bookingpress_cancel_variant = 'error';
        this.bookingpress_cancel_msg = msg || strings.error || 'Something went wrong..';
      },
      clearError() {
        this.bookingpress_cancel_variant = '';
        this.bookingpress_cancel_msg = '';
      },
      bookingpress_cancel_appointment(appointment_id, cancel_token) {
        // Prevent duplicate submit while a request is in flight.
        if (this.is_cancel_button_disabled) return;

        const body = new URLSearchParams();
        body.append('action', cfg.action || 'bookingpress_front_cancel_appointment');
        body.append('appointment_id', String(appointment_id || ''));
        body.append('cancel_token', cancel_token || '');
        body.append('_wpnonce', cfg.nonce || '');

        for (const fn of beforeCancel) {
          try {
            if (fn(body, this) === false) return;
          } catch (e) {
            // eslint-disable-next-line no-console
            console.error('[bp-cc-v3] beforeCancel callback failed', e);
          }
        }

        this.is_cancel_appointment_loader = '1';
        this.is_cancel_button_disabled = true;

        fetch(cfg.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: body.toString(),
        })
          .then((res) => res.json())
          .then((data) => {
            this.is_cancel_appointment_loader = '0';
            this.is_cancel_button_disabled = false;

            if (data && data.variant !== undefined && data.variant !== 'error') {
              window.location.href = data.redirect_url;
              return;
            }
            this.bookingpress_cancel_variant = (data && data.variant) || 'error';
            this.bookingpress_cancel_msg = (data && data.msg) || strings.error || 'Something went wrong..';
          })
          .catch((err) => {
            // eslint-disable-next-line no-console
            console.error('[bp-cc-v3] cancel failed', err);
            this.is_cancel_appointment_loader = '0';
            this.is_cancel_button_disabled = false;
            this.setError(strings.error);
          });
      },
    },
  };
}

/**
 * Mount one instance on its server-rendered container.
 *
 * @param {string} instanceId   Per-render id (matches `data-bp-cc-instance`).
 * @param {object} initialState Island state for this instance.
 * @returns {{instanceId: string, api: object, config: object}|null}
 */
export function mountCancellationConfirmationInstance(instanceId, initialState) {
  const cfg = initialState || {};
  const mountNode =
    document.querySelector(`[data-bp-cc-instance="${instanceId}"]`) ||
    document.querySelector(`#bookingpress_appointment_cancellation_form_${instanceId}`);

  if (!mountNode) {
    // eslint-disable-next-line no-console
    console.warn('[bp-cc-v3] mount node missing:', instanceId);
    return null;
  }

  const beforeCancel = [];
  const app = createApp(createCancellationConfirmationComponent(cfg, beforeCancel));
  app.config.errorHandler = (err, _vm, info) => {
    // eslint-disable-next-line no-console
    console.error('[bp-cc-v3] runtime error', { instanceId, info, error: err });
  };

  let vm = null;
  try {
    vm = app.mount(mountNode);
  } catch (e) {
    // eslint-disable-next-line no-console
    console.error('[bp-cc-v3] mount failed', instanceId, e);
    return null;
  }

  const api = {
    registerBeforeCancel(fn) {
      if (typeof fn === 'function') beforeCancel.push(fn);
    },
    setError(msg) {
      try { vm.setError(msg); } catch (_e) {}
    },
    clearError() {
      try { vm.clearError(); } catch (_e) {}
    },
  };

  const handle = { instanceId, api, config: cfg, app };
  if (typeof window !== 'undefined' && window.BookingPressCancellationConfirmationV3) {
    window.BookingPressCancellationConfirmationV3.instances[instanceId] = handle;
  }
  return handle;
}
