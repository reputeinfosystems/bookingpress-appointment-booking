/**
 * app.js — Vue 3 application root for one My Bookings instance.
 *
 * LITE-ONLY renderer for the `[bookingpress_my_appointments]` shortcode. It
 * loads the customer's appointments through the EXISTING admin-ajax action
 * `bookingpress_get_customer_appointments` (no REST endpoint) and renders,
 * with legacy class parity:
 *   - guest "please login" state (no AJAX),
 *   - customer header (avatar / fullname / email),
 *   - filter bar (search + date range) with Apply / Clear,
 *   - loader / list / empty / error states,
 *   - appointments table + expand card + cancel (hover card + popconfirm),
 *   - refund preview before cancel (driven purely by Pro row flags; the
 *     request is the same Lite cancel action, the refund runs server-side),
 *   - Book Again link (pure navigation off `book_again_page_url` row data),
 *   - Delete Account tab,
 *   - pagination visibility based on `total_records > per_page`.
 *
 * Pro / add-on features (Edit Account, Change Password, Reschedule, Gift
 * Cards, Packages) are NOT part of this file. They plug in from their own
 * plugins via the add-on registry:
 *   window.BookingPressMyBookingsV3.registerAddon(name, factory)
 * (see bootstrap.js). Each factory receives the per-instance `api`:
 *   api.registerTab({ id, title, icon, component, order })
 *   api.registerRowAction({ id, component, placement, order })
 *   api.registerRowDetail({ id, component, placement, order })  // PR-e1
 *   api.registerGuestView(component)                            // PR-e1
 *   api.registerNav(component)
 *   api.reloadAppointments(page)
 *   api.notifySuccess(message)
 * Extension components render via `<component :is>` and receive
 * `{ ctx }` (tabs / guest view) or `{ row, ctx }` (row actions / row details),
 * where `ctx` carries `{ ajaxUrl, nonce, config, strings, reloadAppointments,
 * notifySuccess, switchTab, setCustomerName, onAuthenticated }`.
 *
 * Exports `mountMyBookingsInstance(instanceId, initialState)`.
 */
import { createApp, markRaw } from 'vue';

import { Remove, CirclePlus } from 'bookingpress-ui';

const EMPTY_VIEW_IMAGE_URL = new URL(
  '../../../../images/data-grid-empty-view-vector.webp',
  import.meta.url
).href;

// eslint-disable-next-line no-console
console.info('[bp-mb-v3] app module loaded');

/**
 * Map viewport width → legacy screen-size token (parity with the legacy
 * `current_screen_size` data field that drives the 3 responsive blocks).
 *
 * @returns {'desktop'|'tablet'|'mobile'}
 */
function detectScreenSize() {
  const w = typeof window !== 'undefined' ? window.innerWidth : 1280;
  if (w >= 1024) return 'desktop';
  if (w >= 768) return 'tablet';
  return 'mobile';
}

/**
 * Build the per-instance root component.
 *
 * Options API + template strings (shipped `vue.min.js` is the full build).
 * Class names mirror `core/views/frontend/appointment_my_appointments.php`
 * so the existing My Booking CSS applies with no new styling.
 *
 * @param {object} cfg Instance config from the JSON island.
 * @returns {object} Vue component options.
 */
function createMyBookingsComponent(cfg) {
  const strings = (cfg && cfg.strings) || {};
  const config = (cfg && cfg.config) || {};

  return {
    name: 'BookingPressMyBookings',
    data() {
      return {
        // Boot phase: the BookingPress loader is shown until we know which view
        // to render (guest login form vs logged-in panel), so the static "please
        // login" message never flashes before a Pro login form registers.
        booting: true,
        isLoading: true,
        isApplying: false,
        hasError: false,
        CirclePlus,
        Remove,
        errorMessage: strings.error || 'Something went wrong..',
        items: [],
        totalRecords: 0,
        currentPage: 1,
        perPage: parseInt(cfg.perPage, 10) || 10,
        isLoggedIn: String(cfg.isUserLoggedIn) === '1',
        hideCustomerDetails: String(config.hide_customer_details) === '1',
        allowCancel: String(config.allow_cancel_appointments) === '1',
        allowDeleteProfile: String(config.allow_customer_delete_profile) === '1',
        logoutUrl: cfg.logoutUrl || '',
        profileMenuOpen: false,
        expandedRow: null,
        emptyViewImageUrl: EMPTY_VIEW_IMAGE_URL,
        // Extension registries — populated by Pro/add-on modules through the
        // instance api (registerTab / registerRowAction). Reactive so
        // post-mount registration re-renders the sidebar/action areas.
        extensionTabs: [],
        extensionRowActions: [],
        // Per-row detail injections (PR-e1). A Pro/add-on module may register a
        // component through `api.registerRowDetail` to add content inside the
        // expand card at one of three placements: 'connect' (head/connect-link
        // area, legacy `bookingpress_integration_connect_extra_link`), 'details'
        // (after the basic-details section) or 'payment' (inside the payment-
        // details section). Each receives `{ row, ctx }`. Empty → nothing renders.
        extensionRowDetails: [],
        // Single-slot guest view presenter (PR-e1). A Pro/add-on module may
        // register a component through `api.registerGuestView` to replace Lite's
        // static "please login" message for logged-out visitors (e.g. the Pro
        // login/forgot-password form); null → Lite renders its static message
        // unchanged. markRaw'd on set (see registerGuestViewComponent).
        guestViewComponent: null,
        // Single-slot nav presenter (PR-b1). A Pro/add-on module may register a
        // component through `api.registerNav` to replace Lite's built-in
        // dropdown nav (e.g. the Pro left-sidebar + mobile nav); null → Lite
        // renders its own dropdown unchanged. markRaw'd on set (see
        // registerNavPresenter) so it stays out of the reactivity graph.
        navComponent: null,
        // Tab + Delete Account state (MB-5B shell + MB-5C AJAX wiring).
        currentTab: 'my_appointment', // 'my_appointment' | 'delete_account' | extension tab id
        deleting: false, // request in flight (duplicate-submit guard)
        deleteError: '', // inline error inside the delete panel
        // Backend-configured Delete Account panel (customizer
        // `delete_account_content`, legacy parity). The PHP island replaces the
        // [bookingpress_delete_account] shortcode with an empty slot div; the
        // reactive action block teleports into it (gated by ...SlotReady so the
        // target exists in the document before the teleport mounts).
        deleteAccountHtml: (cfg.deleteAccountContent && cfg.deleteAccountContent.html) || '',
        deleteAccountSlotId: (cfg.deleteAccountContent && cfg.deleteAccountContent.slotId)
          || ('bp-mb-v3-da-actions-' + (cfg.instanceId || '0')),
        deleteAccountSlotReady: false,
        // Cancel-action state.
        cancelConfirmId: null, // row whose inline confirm is open
        cancelingId: null, // row whose cancel request is in flight
        cancelError: '', // inline error message for the open confirm
        // Refund preview dialog (MB-7D): shown before cancel when a Pro row is
        // refundable (appointment_refund_status == 1). The Apply button reuses
        // the SAME bookingpress_cancel_appointment action (server does the refund).
        refundDialog: { open: false, row: null, refundAmount: '', defaultRefundAmount: '', error: '' },
        // Inline success banner shown above the list (api.notifySuccess).
        successMessage: '',
        // Filter model — mirrors legacy `search_appointment` +
        // `appointment_date_range[0|1]`.
        searchAppointment: '',
        dateRange: ['', ''],
        // Customer header — populated from response.customer_details.
        customer: {
          fullname: '',
          email: '',
          avatarUrl: '',
          usePlaceholder: true,
        },
        currentScreenSize: detectScreenSize(),
        strings,
        config,
      };
    },
    computed: {
      showPagination() {
        return this.totalRecords > this.perPage;
      },
      totalPages() {
        return Math.max(1, Math.ceil(this.totalRecords / this.perPage));
      },
      isEmpty() {
        return !this.isLoading && !this.hasError && this.items.length === 0;
      },
      // Delete Account tab is offered only to logged-in customers when allowed
      // (legacy-compatible: shown unless explicitly disabled — see PHP island).
      showDeleteAccount() {
        return this.isLoggedIn && this.allowDeleteProfile;
      },
      // Button labels: the [bookingpress_delete_account] shortcode atts win
      // (legacy parity), then the customizer strings, then translated defaults.
      deleteAccountCancelLabel() {
        const c = cfg.deleteAccountContent || {};
        return c.cancelText || this.strings.cancel_button_text || 'Cancel';
      },
      deleteAccountDeleteLabel() {
        const c = cfg.deleteAccountContent || {};
        if (c.deleteText) return c.deleteText;
        // Legacy shortcode default is plain "Delete"; the heading-style label
        // only applies to the fallback (no backend content) panel.
        return this.deleteAccountHtml
          ? (this.strings.delete_button_text || 'Delete')
          : (this.strings.delete_account_button_title || this.strings.delete_button_text || 'Delete');
      },
      // Extension row actions split by placement (hover card vs expand card).
      hoverRowActions() {
        return this.extensionRowActions.filter((a) => a.placement === 'hover' || a.placement === 'both');
      },
      expandRowActions() {
        return this.extensionRowActions.filter((a) => a.placement === 'expand' || a.placement === 'both');
      },
      // Extension row details split by placement inside the expand card (PR-e1).
      connectRowDetails() {
        return this.extensionRowDetails.filter((d) => d.placement === 'connect');
      },
      detailsRowDetails() {
        return this.extensionRowDetails.filter((d) => d.placement === 'details');
      },
      paymentRowDetails() {
        return this.extensionRowDetails.filter((d) => d.placement === 'payment');
      },
      paymentBeforeTotalRowDetails() {
        return this.extensionRowDetails.filter((d) => d.placement === 'payment_before_total');
      },
      // Context object handed to every extension component (tabs + row
      // actions). Extensions call back through it instead of importing Lite
      // internals; the arrow functions keep `this` bound to the root vm.
      extensionCtx() {
        return {
          ajaxUrl: cfg.ajaxUrl || '',
          nonce: cfg.nonce || '',
          config: this.config,
          strings: this.strings,
          perPage: this.perPage,
          isLoggedIn: this.isLoggedIn,
          currentTab: this.currentTab,
          reloadAppointments: (page) => this.loadAppointments(page || this.currentPage),
          notifySuccess: (msg) => this.notifySuccess(msg),
          switchTab: (id) => this.switchTab(id),
          setCustomerName: (name) => this.setCustomerName(name),
          onAuthenticated: (payload) => this.onAuthenticated(payload),
        };
      },
      // Presentation-agnostic model for the nav slot (PR-b1). A registered nav
      // presenter renders the tab menu from this and drives navigation through
      // `switchTab`; Lite's built-in dropdown reads the same underlying state.
      // `tabs` is the ordered, visibility-filtered list the presenter should
      // render (built-in My Appointments, extension tabs, Delete Account when
      // allowed). Built-in icons are left empty so the presenter supplies its
      // own (legacy Pro uses the `bookingpress_my_booking_page_icons` action);
      // extension tabs carry the icon they registered with.
      navModel() {
        const tabs = [
          { id: 'my_appointment', title: this.strings.my_appointment_menu_title || 'My Appointments', icon: '' },
        ];
        this.extensionTabs.forEach((t) => tabs.push({ id: t.id, title: t.title, icon: t.icon }));
        if (this.showDeleteAccount) {
          tabs.push({ id: 'delete_account', title: this.strings.delete_appointment_menu_title || 'Delete Account', icon: '' });
        }
        return {
          tabs,
          currentTab: this.currentTab,
          switchTab: (id) => this.switchTab(id),
          customer: this.customer,
          logoutUrl: this.logoutUrl,
          hideCustomerDetails: this.hideCustomerDetails,
        };
      },
      // Show the Staff column only when at least one row carries staff data
      // (Pro). Keeps the Lite table clean while matching the Pro layout.
      hasStaffColumn() {
        return this.items.some((r) => this.staffName(r) !== '');
      },
    },
    mounted() {
      // Parity with the legacy on_load gate: guests have nothing to load.
      if (this.isLoggedIn) {
        // Logged-in: the boot loader stays up until the first appointment load
        // resolves (see loadAppointments .finally), then the populated panel shows.
        this.loadAppointments(1);
      } else {
        this.isLoading = false;
        // Guest: don't flash the static "please login" message before a Pro
        // login form registers. When the page expects a login form (the Pro
        // guest-login feature seeds config.bpa_has_guest_login), keep the boot
        // loader until the guest view registers; on Lite-only sites (no such
        // form) reveal the static message immediately. A safety timer resolves
        // the loader even if the module never loads.
        if (!this.config.bpa_has_guest_login) {
          this.booting = false;
        } else if (this.guestViewComponent) {
          this.booting = false;
        } else {
          this._bootWatch = this.$watch('guestViewComponent', (v) => {
            if (v) { this.booting = false; this.clearBootTimer(); }
          });
          this._bootTimer = window.setTimeout(() => { this.booting = false; }, 2500);
        }
      }
      this._onResize = () => {
        this.currentScreenSize = detectScreenSize();
      };
      if (typeof window !== 'undefined') {
        window.addEventListener('resize', this._onResize);
      }
      // Teleport gate for the Delete Account action buttons: their slot div is
      // injected via v-html (or rendered by the fallback branch), so wait until
      // the initial patch has put it in the document before mounting the
      // teleport. If the admin removed the shortcode from the content, the slot
      // never exists and no buttons render — same as the legacy template.
      this.$nextTick(() => {
        this.deleteAccountSlotReady = !!(this.deleteAccountSlotId
          && document.getElementById(this.deleteAccountSlotId));
      });
    },
    unmounted() {
      if (typeof window !== 'undefined' && this._onResize) {
        window.removeEventListener('resize', this._onResize);
      }
      this.clearBootTimer();
    },
    methods: {
      // Tear down the guest boot watcher/timer (idempotent).
      clearBootTimer() {
        if (this._bootTimer) { window.clearTimeout(this._bootTimer); this._bootTimer = null; }
        if (this._bootWatch) { this._bootWatch(); this._bootWatch = null; }
      },
      // --- legacy-compatible status / payment mapping ----------------------
      statusPillClass(row) {
        const s = String(row.bookingpress_appointment_status);
        if (s === '2') return '--bpa-warning';
        if (s === '3') return '--bpa-info';
        if (s === '4') return '--bpa-rejected';
        return '';
      },
      statusBoxClass(row) {
        const s = String(row.bookingpress_appointment_status);
        if (s === '1') return '__bpa-is-approved';
        if (s === '2') return '__bpa-is-pending';
        if (s === '4') return '__bpa-is-rejected';
        return '';
      },
      paymentStatusValClass(row) {
        // Prefer the server-provided class (Pro sets it via
        // `bookingpress_modify_appointment_status_cls`); fall back to the
        // legacy client mapping when it is empty (Lite).
        if (row.bookingpress_payment_status_class) {
          return row.bookingpress_payment_status_class;
        }
        // Legacy inline map (appointment_my_appointments.php:689): 1=Paid green,
        // 2=Pending orange, 3=Refunded red, 4=Partially Paid blue. Statuses 3 & 4
        // were previously unhandled and rendered in the default text color.
        const s = String(row.bookingpress_payment_status);
        if (s === '1') return 'bpa-front-text-primary-color';
        if (s === '2') return 'bpa-front-text--secondary-orange-color';
        if (s === '3') return 'bpa-front-text--danger-color';
        if (s === '4') return 'bpa-front-text-blue-color';
        return '';
      },
      paymentMethodLabel(row) {
        if (row.bookingpress_payment_method === 'manual') {
          return this.strings.manual_booked_by_admin || 'Manual ( Booked By Admin )';
        }
        return row.bookingpress_payment_method_label || '';
      },
      // Total: Pro provides `total_amt_with_currency` (with tax/extras/
      // discount applied); Lite only has `bookingpress_paid_price_with_currency`.
      totalLabel(row) {
        return row.total_amt_with_currency || row.bookingpress_paid_price_with_currency || '';
      },
      staffName(row) {
        const first = row.staff_first_name || '';
        const last = row.staff_last_name || '';
        return (first + ' ' + last).trim();
      },
      hasExtras(row) {
        return Array.isArray(row.extras_details) && row.extras_details.length > 0;
      },
      // --- Multi Service (add-on) row data ----------------------------------
      // Stamped by the Multi Service add-on's legacy row filter
      // (bookingpress_modify_my_appointment_data): is_multi_service_booking,
      // bookingpress_multiple_service_name/_extra_name/_total and
      // appointment_details_arr. Absent on Lite / non-MS rows → gates false.
      isMultiService(row) {
        return !!row && String(row.is_multi_service_booking) === '1';
      },
      msServices(row) {
        return Array.isArray(row.appointment_details_arr) ? row.appointment_details_arr : [];
      },
      // --- cancel action ---------------------------------------------------
      rowId(row) {
        return row.bookingpress_appointment_booking_id || row.booking_id;
      },
      // Normalize boolean-like values coming from the AJAX response
      // (true / 1 / '1' all mean true; everything else is false).
      truthy(val) {
        return val === true || val === 1 || val === '1';
      },
      // Cancel is offered when the global toggle allows it, Pro has not hidden
      // the whole action group (`hide_action_wrapper`), the appointment is not
      // already cancelled (3) / rejected (4), and — when Pro supplies the
      // per-row flag — `allow_cancelling` is truthy.
      canCancel(row) {
        if (!this.allowCancel) return false;
        // Pro hides the entire action button group via this flag (it already
        // folds in min-cancel-time, past-appointment and allow_cancelling
        // logic). Honor it as a hard gate when present.
        if (this.truthy(row.hide_action_wrapper)) return false;
        const status = String(row.bookingpress_appointment_status);
        if (status === '3' || status === '4') return false;
        if (Object.prototype.hasOwnProperty.call(row, 'allow_cancelling')) {
          return this.truthy(row.allow_cancelling);
        }
        return true;
      },
      // Refundable (Pro): legacy shows a refund-amount preview before cancel when
      // appointment_refund_status == 1. Only relevant for an otherwise-cancellable
      // row; Lite rows have no such flag → false (plain popconfirm).
      isRefundable(row) {
        return this.canCancel(row) && String(row.appointment_refund_status) === '1';
      },
      // Book again is a pure navigation link (Pro): shown when the action
      // group is not hidden and a destination URL is present on the row.
      // Mirrors legacy `v-if="scope.row.book_again_page_url != ''"` inside
      // `v-if="!scope.row.hide_action_wrapper"`.
      canBookAgain(row) {
        if (this.truthy(row.hide_action_wrapper)) return false;
        return typeof row.book_again_page_url === 'string' && row.book_again_page_url !== '';
      },
      // The action area renders only when at least one action is available.
      // Extension actions decide their own per-row visibility inside their
      // component (they render nothing when not applicable).
      hasActions(row) {
        return this.canCancel(row) || this.canBookAgain(row) || this.expandRowActions.length > 0;
      },
      // --- extension api (called through the mount handle's `api`) ----------
      // Register a sidebar tab + body. `component` is a Vue component options
      // object rendered via <component :is> when the tab is active; it receives
      // { ctx } (see extensionCtx). markRaw keeps the options object out of the
      // reactivity graph.
      registerExtensionTab(def) {
        if (!def || !def.id || !def.component) return;
        const id = String(def.id);
        if (id === 'my_appointment' || id === 'delete_account') return; // reserved
        if (this.extensionTabs.some((t) => t.id === id)) return; // dedupe
        this.extensionTabs.push({
          id,
          title: def.title || id,
          icon: typeof def.icon === 'string' ? def.icon : '',
          component: markRaw(def.component),
          order: Number(def.order) || 0,
        });
        this.extensionTabs.sort((a, b) => a.order - b.order);
      },
      // Register a per-row action button component. Rendered inside the hover
      // card and/or the expand-card action group; receives { row, ctx }.
      registerExtensionRowAction(def) {
        if (!def || !def.id || !def.component) return;
        const id = String(def.id);
        if (this.extensionRowActions.some((a) => a.id === id)) return; // dedupe
        const placement = ['hover', 'expand', 'both'].indexOf(def.placement) !== -1 ? def.placement : 'expand';
        this.extensionRowActions.push({
          id,
          component: markRaw(def.component),
          placement,
          order: Number(def.order) || 0,
        });
        this.extensionRowActions.sort((a, b) => a.order - b.order);
      },
      // Register the single nav presenter (PR-b1). Single-slot: the last
      // registration wins. `component` is a Vue component options object
      // rendered via <component :is> in place of Lite's dropdown; it receives
      // `:nav="navModel"`. markRaw keeps it out of the reactivity graph.
      registerNavPresenter(component) {
        if (!component) return;
        this.navComponent = markRaw(component);
      },
      // Register a per-row detail component (PR-e1). Rendered inside the expand
      // card at the requested placement ('connect' | 'details' | 'payment');
      // receives { row, ctx }. Unknown placement normalizes to 'details'.
      registerExtensionRowDetail(def) {
        if (!def || !def.id || !def.component) return;
        const id = String(def.id);
        if (this.extensionRowDetails.some((d) => d.id === id)) return; // dedupe
        const placement = ['connect', 'details', 'payment', 'payment_before_total'].indexOf(def.placement) !== -1 ? def.placement : 'details';
        this.extensionRowDetails.push({
          id,
          component: markRaw(def.component),
          placement,
          order: Number(def.order) || 0,
        });
        this.extensionRowDetails.sort((a, b) => a.order - b.order);
      },
      // Register the single guest view presenter (PR-e1). Single-slot: the last
      // registration wins. `component` is a Vue component options object rendered
      // via <component :is> in place of Lite's static login message for logged-out
      // visitors; it receives `:ctx="extensionCtx"` (so it can call
      // ctx.onAuthenticated on success). markRaw keeps it out of the reactivity graph.
      registerGuestViewComponent(component) {
        if (!component) return;
        this.guestViewComponent = markRaw(component);
      },
      // In-place guest → logged-in transition (PR-e1). Called by a guest view
      // (e.g. the Pro login form) after a successful sign-in. When the server
      // returns a redirect (e.g. staff redirect) we follow it; otherwise we swap
      // in the fresh nonce (subsequent Lite AJAX reuses cfg.nonce), flip
      // isLoggedIn so the logged-in panel renders immediately, load the
      // appointment list, and rehydrate the login-gated add-on modules (PR-e3)
      // so their tabs / row actions appear live without a page reload.
      onAuthenticated(payload) {
        const data = payload || {};
        if (data.redirectUrl) {
          window.location.href = data.redirectUrl;
          return;
        }
        if (data.nonce) {
          cfg.nonce = data.nonce; // fetch bodies read cfg.nonce at call time
        }
        this.isLoggedIn = true;
        this.loadAppointments(1);
        this.rehydrateAfterAuth();
      },
      // Fetch the authenticated instance state + login-gated module list (PR-e3),
      // merge the server config/strings into the reactive state so the just-loaded
      // add-on components read the logged-in data, refresh the nonce + logout URL
      // (the guest-seeded logout nonce was for uid 0), then dynamically import each
      // module. Each imported module calls registerAddon → its factory runs against
      // this mounted instance (now carrying the merged config) → its tab / row
      // action registers live. Best-effort: failure just leaves Lite's own view.
      rehydrateAfterAuth() {
        const body = new URLSearchParams();
        body.append('action', 'bookingpress_mybooking_vue3_rehydrate');
        body.append('instance_id', cfg.instanceId || '');
        body.append('_wpnonce', cfg.nonce || '');

        return fetch(cfg.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: body.toString(),
        })
          .then((res) => res.json())
          .then((data) => {
            if (!data || data.variant === 'error') return;
            if (data.nonce) cfg.nonce = data.nonce;
            if (data.config && typeof data.config === 'object') {
              Object.keys(data.config).forEach((k) => { this.config[k] = data.config[k]; });
              // Re-derive the top-level flags mounted from config so Lite's own
              // gates (cancel / delete-account) match the logged-in state.
              this.hideCustomerDetails = String(this.config.hide_customer_details) === '1';
              this.allowCancel = String(this.config.allow_cancel_appointments) === '1';
              this.allowDeleteProfile = String(this.config.allow_customer_delete_profile) === '1';
            }
            if (data.strings && typeof data.strings === 'object') {
              Object.keys(data.strings).forEach((k) => { this.strings[k] = data.strings[k]; });
            }
            if (typeof data.logoutUrl === 'string' && data.logoutUrl) {
              this.logoutUrl = data.logoutUrl;
            }
            const mods = Array.isArray(data.modules) ? data.modules : [];
            mods.forEach((m) => { this.loadAddonModule(m && m.src ? m.src : m); });
          })
          .catch((err) => {
            // eslint-disable-next-line no-console
            console.error('[bp-mb-v3] rehydrate failed', err);
          });
      },
      // Dynamically import an add-on module by URL (PR-e3), once per src. The
      // module's top-level `registerAddon` call runs its factory against this
      // already-mounted instance (bootstrap.js), which registers its tabs / row
      // actions. Bare imports inside the module resolve via the page import map.
      loadAddonModule(src) {
        if (!src || typeof src !== 'string') return;
        if (!this._loadedAddonSrcs) this._loadedAddonSrcs = Object.create(null);
        if (this._loadedAddonSrcs[src]) return;
        this._loadedAddonSrcs[src] = true;
        import(src).catch((err) => {
          // eslint-disable-next-line no-console
          console.error('[bp-mb-v3] addon import failed', src, err);
        });
      },
      // Update the displayed customer name (e.g. after a Pro Edit Account save).
      // Exposed to extensions via extensionCtx.setCustomerName.
      setCustomerName(name) {
        if (typeof name === 'string') this.customer.fullname = name;
      },
      // Show the inline success banner above the list (auto-clears).
      notifySuccess(msg) {
        this.successMessage = msg || '';
        if (this.successMessage) {
          window.setTimeout(() => { this.successMessage = ''; }, 5000);
        }
      },
      openCancelConfirm(row) {
        this.cancelError = '';
        this.cancelConfirmId = this.rowId(row);
      },
      closeCancelConfirm() {
        this.cancelConfirmId = null;
        this.cancelError = '';
      },
      // Open the refund preview dialog for a refundable row (Pro). No AJAX here —
      // just shows the amounts; the actual cancel/refund happens on Apply.
      openRefundPreview(row) {
        if (this.cancelingId !== null) return; // a cancel is already in flight
        this.refundDialog = {
          open: true,
          row: row,
          refundAmount: row.refund_amount || '',
          defaultRefundAmount: row.default_refund_amount || '',
          error: '',
        };
      },
      async open_book_again_page_func(currentElement, apt_id,book_again_page_url){
        window.location.href = book_again_page_url;
      },
      closeRefundPreview() {
        if (this.cancelingId !== null) return; // never close mid-request
        this.refundDialog = { open: false, row: null, refundAmount: '', defaultRefundAmount: '', error: '' };
      },
      // Apply = confirm the refund preview → run the SAME cancel request. Errors
      // surface inside the dialog (legacy parity); success redirects/reloads.
      applyRefund() {
        if (this.refundDialog.row) {
          this.confirmCancel(this.refundDialog.row, true);
        }
      },
      confirmCancel(row, fromRefund) {
        const id = this.rowId(row);
        // Prevent duplicate submit while a request is in flight.
        if (this.cancelingId !== null) return;
        this.cancelingId = id;
        this.cancelError = '';
        if (fromRefund) this.refundDialog.error = '';

        const body = new URLSearchParams();
        body.append('action', 'bookingpress_cancel_appointment');
        body.append('cancel_id', String(row.bookingpress_appointment_booking_id || id));
        body.append('cancel_reason', ''); // core does not collect a reason here
        body.append('_wpnonce', cfg.nonce || '');

        fetch(cfg.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: body.toString(),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data && data.variant !== 'error') {
              // Legacy parity: redirect to the after-cancel page when present.
              if (data.redirect_url) {
                window.location.href = data.redirect_url;
                return;
              }
              // Fallback: refresh the current page/filters in place.
              this.cancelConfirmId = null;
              if (fromRefund) {
                this.refundDialog = { open: false, row: null, refundAmount: '', defaultRefundAmount: '', error: '' };
              }
              this.loadAppointments(this.currentPage);
              return;
            }
            // Server-reported error (e.g. refund failure / nonce). Surface inside
            // the refund dialog when that initiated it; appointment stays as-is.
            const msg = (data && data.msg) || this.strings.error || 'Something went wrong..';
            if (fromRefund) { this.refundDialog.error = msg; } else { this.cancelError = msg; }
          })
          .catch((err) => {
            // eslint-disable-next-line no-console
            console.error('[bp-mb-v3] cancel failed', err);
            const msg = this.strings.error || 'Something went wrong..';
            if (fromRefund) { this.refundDialog.error = msg; } else { this.cancelError = msg; }
          })
          .finally(() => {
            this.cancelingId = null;
          });
      },
      // --- tabs / delete account (MB-5B shell + MB-5C AJAX) ----------------
      resetDeleteState() {
        this.deleting = false;
        this.deleteError = '';
      },
      switchTab(tab) {
        if (this.deleting) return; // never leave mid-write (race guard)
        if (tab === 'delete_account' && !this.showDeleteAccount) return;
        // Besides the built-in tabs, only registered extension tabs are valid.
        if (tab !== 'my_appointment' && tab !== 'delete_account'
          && !this.extensionTabs.some((t) => t.id === tab)) return;
        this.currentTab = tab;
        this.resetDeleteState();
      },
      cancelDelete() {
        // Mirror legacy: Cancel returns to My Appointments (Edit Account is not
        // implemented in this scaffold).
        this.resetDeleteState();
        this.currentTab = 'my_appointment';
      },
      // Frontend safety gate only — NOT authorization. The backend independently
      // re-checks nonce + login + customer ownership; this just blocks duplicate
      // submits. The panel content itself is the confirmation (legacy parity).
      canDeleteAccount() {
        return !this.deleting && this.isLoggedIn && this.showDeleteAccount;
      },
      deleteAccount() {
        // Legacy parity: Delete fires immediately (the backend-configured panel
        // copy is the confirmation) via the existing legacy admin-ajax action.
        if (!this.canDeleteAccount()) return; // also blocks duplicate submit
        this.deleting = true;
        this.deleteError = '';

        const body = new URLSearchParams();
        body.append('action', 'bookingpress_delete_account');
        body.append('_wpnonce', cfg.nonce || '');

        fetch(cfg.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: body.toString(),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data && data.variant === 'success') {
              // Backend has already wp_logout()'d the user. Do NOT call any
              // further authenticated AJAX (e.g. loadAppointments); just reload
              // so the page renders in its logged-out state.
              window.location.reload();
              return;
            }
            this.deleteError = (data && data.msg) || this.strings.error || 'Something went wrong..';
            this.deleting = false;
          })
          .catch((err) => {
            // eslint-disable-next-line no-console
            console.error('[bp-mb-v3] delete account failed', err);
            this.deleteError = this.strings.error || 'Something went wrong..';
            this.deleting = false;
          });
      },
      // --- data load -------------------------------------------------------
      buildSearchBody(body) {
        // Legacy shape:
        //   search_data[search_appointment]
        //   search_data[selected_date_range][]  (start, then end)
        body.append('search_data[search_appointment]', this.searchAppointment || '');
        body.append('search_data[selected_date_range][]', this.dateRange[0] || '');
        body.append('search_data[selected_date_range][]', this.dateRange[1] || '');
        return body;
      },
      loadAppointments(page) {
        if (!this.isLoggedIn) {
          this.isLoading = false;
          return;
        }
        const targetPage = page || this.currentPage || 1;
        this.currentPage = targetPage;
        this.isLoading = true;
        this.isApplying = true;
        this.hasError = false;

        const body = new URLSearchParams();
        body.append('action', cfg.action || 'bookingpress_get_customer_appointments');
        body.append('perpage', String(this.perPage));
        body.append('currentpage', String(targetPage));
        body.append('_wpnonce', cfg.nonce || '');
        this.buildSearchBody(body);

        fetch(cfg.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: body.toString(),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data && data.variant === 'error') {
              this.hasError = true;
              this.errorMessage = data.msg || this.strings.error || 'Something went wrong..';
              this.items = [];
              this.totalRecords = 0;
              return;
            }
            this.items = Array.isArray(data && data.items) ? data.items : [];
            this.totalRecords = parseInt((data && data.total_records) || 0, 10) || 0;
            const cd = (data && data.customer_details) || {};
            this.customer = {
              fullname: cd.bookingpress_user_fullname || '',
              email: cd.bookingpress_user_email || '',
              avatarUrl: cd.bookingpress_avatar_url || '',
              usePlaceholder: !!cd.bookingpress_use_placeholder,
            };
          })
          .catch((err) => {
            // eslint-disable-next-line no-console
            console.error('[bp-mb-v3] load failed', err);
            this.hasError = true;
            this.errorMessage = this.strings.error || 'Something went wrong..';
          })
          .finally(() => {
            this.isLoading = false;
            this.isApplying = false;
            this.booting = false; // first load done → reveal the panel
          });
      },
      applyFilters() {
        this.loadAppointments(1);
      },
      clearFilters() {
        this.searchAppointment = '';
        this.dateRange = ['', ''];
        this.loadAppointments(1);
      },
      goToPage(page) {
        if (page < 1 || page > this.totalPages || page === this.currentPage) {
          return;
        }
        this.loadAppointments(page);
      },
      bookingpress_full_row_clickable(row, column, event){
          const vm = this;
          let target = event.target;
          let getParent = vm.bookingpress_get_parent_node( target, '.bpa-ma--action-btn-wrapper' );

          if (getParent.length > 0 && getParent[0] != null) {
            return;
          }

          if (vm.expandedRow && vm.expandedRow !== row) {
            vm.$refs.multipleTable.toggleRowExpansion(vm.expandedRow, false);
          }
        
        vm.$refs.multipleTable.toggleRowExpansion(row);          
      },
      bookingpress_expand_change(row, expandedRows) {
          if (expandedRows.length) {
              this.expandedRow = expandedRows[0];
          } else {
              this.expandedRow = null;
          }
      },
      bookingpress_get_parent_node( elem, selector ){
          if (!Element.prototype.matches) {
              Element.prototype.matches = Element.prototype.matchesSelector ||
                  Element.prototype.mozMatchesSelector ||
                  Element.prototype.msMatchesSelector ||
                  Element.prototype.oMatchesSelector ||
                  Element.prototype.webkitMatchesSelector ||
                  function(s) {
                      var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                          i = matches.length;
                      while (--i >= 0 && matches.item(i) !== this) {}
                      return i > -1;
                  };
          }
      
          var parents = [];
      
          for (; elem && elem !== document; elem = elem.parentNode) {
              if (selector) {
                  if (elem.matches(selector)) {
                      parents.push(elem);
                  }
                  continue;
              }
              parents.push(elem);
          }
      
          return parents;
      },
      
    },
    template: `
      <div class="bpa-front-mb-v3__inner" :class="'bpa-front-mb-v3--' + currentScreenSize">

        <!-- Boot loader: shown until we know which view to render, so the guest
             static message never flashes before the Pro login form registers,
             and the logged-in panel appears already populated. -->
        <div class="bpa-front-loader-container bpa-front-mb-v3-boot" v-if="booting">
          <div class="bpa-front-loader" role="status" aria-live="polite">
            <span class="screen-reader-text">{{ strings.loading || 'Loading…' }}</span>
          </div>
        </div>
        <template v-else>

        <!-- Guest state (no AJAX). A registered guest view (PR-e1, e.g. the Pro
             login form) replaces the static message and drives sign-in through
             ctx.onAuthenticated; with nothing registered, guestViewComponent is
             null and Lite's static message renders unchanged. -->
        <template v-if="!isLoggedIn">
          <component v-if="guestViewComponent" :is="guestViewComponent" :ctx="extensionCtx"></component>
          <div v-else class="bpa-front-data-empty-view--my-bookings bpa-front-mb-v3-guest">
            <div class="bpa-front-dev__title" :aria-label="strings.login_message">{{ strings.login_message || 'Please login to your account to view bookings!' }}</div>
          </div>
        </template>

        <!-- Logged-in customer panel -->
        <div class="bpa-front-customer-panel-container" v-else>
          <div class="bpa-front-cp-card">

            <!-- Nav presenter slot (PR-b1). A registered presenter (e.g. the Pro
                 left-sidebar + mobile nav module) replaces Lite's built-in
                 dropdown nav and receives :nav set to navModel; it owns its own
                 visibility (mobile variant, delete-account hiding, etc.). With
                 nothing registered, navComponent is null and the v-else-if
                 below renders Lite's current dropdown, byte-identical. -->
            <component v-if="navComponent" :is="navComponent" :nav="navModel"></component>
            <!-- Left sidebar (avatar / name / email / nav) -->
            <div v-else-if="!hideCustomerDetails && currentTab !== 'delete_account'" class="bpa-front-cp-left-sidebar">
              <div class="bpa-cp-tn__left">
                <div class="bpa-front-module-heading" :aria-label="strings.mybooking_title_text">{{ strings.mybooking_title_text }}</div>	
              </div>
            
              <div class="bpa-cp-ls__tab-menu">
                <bp-ui-dropdown trigger="click" placement="top" tabindex="0">
                  <div class="bpa-tn__dropdown-head bpa_focusable">
                    <div class="bpa-tn__default-img" v-if="!customer.usePlaceholder && customer.avatarUrl">
                      <img :src="customer.avatarUrl" :alt="customer.fullname" class="bpa-cp-pd__avatar">					
                    </div>
                    <div class="bpa-cp-avatar__default-img" v-else>
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v1c0 .55.45 1 1 1h14c.55 0 1-.45 1-1v-1c0-2.66-5.33-4-8-4z"/></svg>
                    </div>
                    <div class="bpa-cp-pd__title" :aria-label="customer.fullname">{{ customer.fullname }}</div>						
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M8.12 9.29L12 13.17l3.88-3.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-4.59 4.59c-.39.39-1.02.39-1.41 0L6.7 10.7c-.39-.39-.39-1.02 0-1.41.39-.38 1.03-.39 1.42 0z"/></svg>
                  </div>                  
                  <template #dropdown>
                    <bp-ui-dropdown-menu class="bpa-tn__dropdown-menu" slot="dropdown" role="dropdown">
                      <bp-ui-dropdown-item class="bpa-tn__dropdown-item" ref="menuitem" tabindex="-1">
                        <a href="javascript:void(0)" class="bpa-tm__item bpa_focusable" :class="(currentTab == 'my_appointment') ? ' __bpa-is-active' : ''"  @click="switchTab('my_appointment')" :aria-label="strings.my_appointment_menu_title">
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1s-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm1 14H8c-.55 0-1-.45-1-1s.45-1 1-1h5c.55 0 1 .45 1 1s-.45 1-1 1zm3-4H8c-.55 0-1-.45-1-1s.45-1 1-1h8c.55 0 1 .45 1 1s-.45 1-1 1zm0-4H8c-.55 0-1-.45-1-1s.45-1 1-1h8c.55 0 1 .45 1 1s-.45 1-1 1z"/></svg>
                            {{ strings.my_appointment_menu_title || 'My Appointments' }}
                        </a>
                      </bp-ui-dropdown-item>
                      <!-- Extension tabs (Pro / add-ons, via api.registerTab). The
                           icon is trusted SVG markup supplied by the registering
                           plugin (same trust level as the legacy icon actions). -->
                      <bp-ui-dropdown-item class="bpa-tn__dropdown-item" v-for="tab in extensionTabs" :key="tab.id" tabindex="-1">
                        <a href="javascript:void(0)" class="bpa-tm__item bpa_focusable" :class="(currentTab === tab.id) ? ' __bpa-is-active' : ''" @click="switchTab(tab.id)" :aria-label="tab.title">
                          <span v-if="tab.icon" v-html="tab.icon"></span>
                            {{ tab.title }}
                        </a>
                      </bp-ui-dropdown-item>
                      <bp-ui-dropdown-item class="bpa-tn__dropdown-item" ref="menuitem" tabindex="-1">
                        <a href="javascript:void(0)" class="bpa-tm__item bpa_focusable" :class="currentTab === 'delete_account' ? '__bpa-is-active' : ''" @click="switchTab('delete_account')" :aria-label="strings.delete_appointment_menu_title">
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2H8c-1.1 0-2 .9-2 2v10zM18 4h-2.5l-.71-.71c-.18-.18-.44-.29-.7-.29H9.91c-.26 0-.52.11-.7.29L8.5 4H6c-.55 0-1 .45-1 1s.45 1 1 1h12c.55 0 1-.45 1-1s-.45-1-1-1z"/></svg>
                            {{ strings.delete_appointment_menu_title || 'Delete Account' }}
                        </a>
                      </bp-ui-dropdown-item>
                    </bp-ui-dropdown-menu>
                  </template>
                </bp-ui-dropdown>                  
                              
              </div>
            </div>

            <!-- My Appointments body -->
            <div class="bpa-front-cp-body __bpa-is-active" v-show="currentTab === 'my_appointment'">

              <!-- Success banner (api.notifySuccess) -->
              <div class="bpa-front-mb-v3-success" role="status" v-if="successMessage">{{ successMessage }}</div>

              <!-- Filter bar -->
              <div class="bpa-front-cp--filter-wrapper">
                <div class="bpa-front-cp--fw__row">
                  <div class="bpa-front-cp--fw__col bpa-front-cp--fw__date-picker-col">
                    <bp-ui-date-picker class="bpa-front-form-control bpa-front-form-control--date-picker" type="date"
                      :placeholder="strings.search_date_title" v-model="dateRange[0]" value-format="YYYY-MM-DD" :clearable="false"></bp-ui-date-picker>
                    <bp-ui-date-picker class="bpa-front-form-control bpa-front-form-control--date-picker" type="date"
                      :placeholder="strings.search_end_date_title" v-model="dateRange[1]" value-format="YYYY-MM-DD"></bp-ui-date-picker>
                  </div>
                  <div class="bpa-front-cp--fw__col __bpa-is-search-icon">
                    <bp-ui-input type="search" class="bpa-front-form-control"
                      :placeholder="strings.search_appointment_title" :aria-label="strings.search_appointment_title"
                      v-model="searchAppointment" @keyup.enter="applyFilters"></bp-ui-input>
                  </div>
                  <div class="bpa-front-cp--fw__col">
                    <bp-ui-button class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--primary bpa-front-btn--full-width bpa_focusable"
                      :disabled="isApplying" :aria-label="strings.apply_button_title" @click="applyFilters">{{ strings.apply_button_title || 'Apply' }}</bp-ui-button>
                  </div>                  
                </div>
              </div>

              <!-- Loader -->
              <div class="bpa-front-loader-container" v-if="isLoading">
                <div class="bpa-front-loader" role="status" aria-live="polite">
                  <span class="screen-reader-text">{{ strings.loading || 'Loading…' }}</span>
                </div>
              </div>

              <!-- Error -->
              <div class="bpa-front-data-empty-view--my-bookings bpa-front-mb-v3-error" v-else-if="hasError">
                <div class="bpa-front-dev__title" :aria-label="errorMessage">{{ errorMessage }}</div>
              </div>

              <!-- Empty -->
              <div class="bpa-front-data-empty-view--my-bookings" v-else-if="isEmpty">
                <div class="bpa-front-dev__title" :aria-label="strings.no_appointments || 'No Appointments found!'">{{ strings.no_appointments || 'No Appointments found!' }}</div>
              </div>

              <!-- Appointments table (Element Plus / bp-ui) -->
              <div class="bpa-front-cp-my-appointment" v-else>
                <bp-ui-table ref="multipleTable" class="bpa-cp-ma-table bpa-cp-ma-table--wrapper" :data="items" stripe fit @row-click="bookingpress_full_row_clickable"  @expand-change="bookingpress_expand_change">
                  <!-- Expandable detail row -->
                  <bp-ui-table-column type="expand" :expand-icon="CirclePlus" :collapse-icon="Remove">
                    <template #default="scope">
                      <div class="bpa-front-ma-view-appointment-card">
                        <div class="bpa-ma-vac--head">
                          <div class="bpa-ma-vac--head__left">
                            <div class="bpa-left__service-detail">
                              <div class="bpa-sd__appointment-id">{{ strings.booking_id_heading || 'Booking ID' }} : #{{ scope.row.booking_id }}</div>
                              <!-- Multi Service rows list their services in the
                                   "Service Details" section instead (legacy parity). -->
                              <div class="bpa-sd__appointment-title" v-if="!isMultiService(scope.row)">{{ scope.row.bookingpress_service_name }}</div>
                            </div>
                          </div>
                          <div class="bpa-ma-vac--head__right">
                            <bp-ui-tag class="bpa-front-pill" :class="statusPillClass(scope.row)">{{ scope.row.bookingpress_appointment_status_label }}</bp-ui-tag>
                          </div>
                        </div>
                        <!-- Row detail extensions, 'connect' placement (PR-e1):
                             head/connect-link area (legacy
                             bookingpress_integration_connect_extra_link — e.g. the
                             Google Meet join link). Each receives { row, ctx }. -->
                        <component v-for="d in connectRowDetails" :key="d.id" :is="d.component" :row="scope.row" :ctx="extensionCtx" placement="connect"></component>
                        <div class="bpa-ma-vac--basic-details">
                          <div class="bpa-vac-bd__row">
                            <div class="bpa-bd__item">                            
                              <div class="bpa-item--label">{{ strings.date_main_heading || 'Date' }}:</div>
                              <div class="bpa-item--val" v-if="scope.row.bookingpress_service_duration_unit === 'd' && scope.row.bookingpress_appointment_formatted_end_date">{{ scope.row.bookingpress_appointment_formatted_date }} - {{ scope.row.bookingpress_appointment_formatted_end_date }}</div>
                              <div class="bpa-item--val" v-else>{{ scope.row.bookingpress_appointment_formatted_date }}</div>
                            </div>
                            <div class="bpa-bd__item">
                              <div class="bpa-item--label">{{ strings.booking_time_title || 'Time' }}:</div>
                              <div class="bpa-item--val">{{ scope.row.bookingpress_appointment_formatted_start_time }} - {{ scope.row.bookingpress_appointment_formatted_end_time }}</div>
                            </div>
                          </div>
                          <div class="bpa-vac-bd__row">
                            <div class="bpa-bd__item bpa-front-mb-v3-staff" v-if="staffName(scope.row)">
                              <div class="bpa-item--label">{{ strings.staff || 'Staff' }}:</div>
                              <div class="bpa-item--val">
                                <img v-if="scope.row.staff_avatar_url" :src="scope.row.staff_avatar_url" :alt="staffName(scope.row)" class="bpa-front-mb-v3-staff__avatar">
                                {{ staffName(scope.row) }}
                              </div>
                            </div>
                            <div class="bpa-bd__item" v-if="scope.row.selected_extra_members && scope.row.selected_extra_members > 0">
                              <div class="bpa-item--label">{{ strings.members || 'Members' }}:</div>
                              <div class="bpa-item--val">{{ scope.row.selected_extra_members }}</div>
                            </div>
                          </div>
                          <!-- Single-service extras; Multi Service rows show extras
                               per-service in the "Service Details" section instead. -->
                          <div class="bpa-vac-bd__extras bpa-front-mb-v3-extras" v-if="hasExtras(scope.row) && !isMultiService(scope.row)">
                            <div class="bpa-ma-vac-sec-title">{{ strings.extras || 'Service Extras' }}:</div>
                            <div class="bpa-vac-pd__item" v-for="(ex, exi) in scope.row.extras_details" :key="exi">
                              <div class="bpa-vac-pd__label">{{ ex.extra_service_name }} <span v-if="ex.extra_service_selected_qty">x {{ ex.extra_service_selected_qty }}</span></div>
                              <div class="bpa-vac-pd__val">{{ ex.extra_service_total_price_with_currency }}</div>
                            </div>
                          </div>
                        </div>
                        <!-- Row detail extensions, 'details' placement (PR-e1):
                             after the basic-details section (legacy additional-info /
                             guest-data hooks). Each receives { row, ctx }. -->
                        <component v-for="d in detailsRowDetails" :key="d.id" :is="d.component" :row="scope.row" :ctx="extensionCtx" placement="details"></component>
                        <!-- Multi Service (add-on): per-service breakdown. Mirrors the
                             legacy bookingpress_my_booking_display_guest_data hook output
                             (name + extras left, per-service price right, one row each). -->
                        <div class="bpa-ma-vac--payment-details bpa-ma-vac--multiservice-section" v-if="isMultiService(scope.row) && msServices(scope.row).length">
                          <div class="bpa-ma-vac-sec-title">{{ strings.multiservice_service_details_title || 'Service Details' }}:</div>
                          <div class="bpa-vac-pd__item" v-for="(service, si) in msServices(scope.row)" :key="si">
                            <div class="bpa-vac-pd__val bpa-vac-multiservice">
                              <p>{{ service.bookingpress_service_name }}</p>
                              <div class="bpa-vac-pd__val bpa-ap__service-extras" v-if="service.extra_service_details && service.extra_service_details.length">
                                <p class="bpa-vac-pd__val bpa-ap__multiservice-extra-label" v-for="(msx, msxi) in service.extra_service_details" :key="msxi">
                                  {{ msx.extra_name }} x {{ msx.selected_qty }}
                                  <span v-show="0 != msx.extra_service_duration">({{ msx.extra_service_duration }}{{ msx.extra_service_duration_unit }})</span>
                                </p>
                              </div>
                            </div>
                            <div class="bpa-vac-pd__val bpa-vac-multiservice">{{ service.bookingpress_service_price_with_currency }}
                              <div class="bpa-vac-pd__val bpa-ap__service-extras" v-if="service.extra_service_details && service.extra_service_details.length">
                                <p class="bpa-vac-pd__val" v-for="(msx, msxi) in service.extra_service_details" :key="msxi">{{ msx.extra_service_price_with_currency }}</p>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="bpa-ma-vac--payment-details">
                          <div class="bpa-ma-vac-sec-title">{{ strings.payment_details_title || 'Payment Details' }}:</div>
                          <div class="bpa-vac-pd__item">
                            <div class="bpa-vac-pd__label">{{ strings.payment_method_title || 'Payment Method' }}:</div>
                            <div class="bpa-vac-pd__val">{{ paymentMethodLabel(scope.row) }}</div>
                          </div>
                          <div class="bpa-vac-pd__item">
                            <div class="bpa-vac-pd__label">{{ strings.status_main_heading || 'Status' }}:</div>
                            <div class="bpa-vac-pd__val" :class="paymentStatusValClass(scope.row)">{{ scope.row.bookingpress_payment_status_label }}</div>
                          </div>
                          <div class="bpa-vac-pd__item" v-if="scope.row.deposit_amt_with_currency && scope.row.is_deposit">
                            <div class="bpa-vac-pd__label">{{ strings.deposit || 'Deposit' }}:</div>
                            <div class="bpa-vac-pd__val">{{ scope.row.deposit_amt_with_currency }}</div>
                          </div>
                          <div class="bpa-vac-pd__item" v-if="scope.row.coupon_discount_amt && scope.row.coupon_discount_amt > 0">
                            <div class="bpa-vac-pd__label">{{ strings.discount || 'Discount' }}:</div>
                            <div class="bpa-vac-pd__val">{{ scope.row.coupon_discount_amt_with_currency }}</div>
                          </div>
                          <component v-for="d in paymentBeforeTotalRowDetails" :key="d.id" :is="d.component" :row="scope.row" :ctx="extensionCtx" placement="payment_before_total"></component>
                          <div class="bpa-vac-pd__item" v-if="scope.row.tax_amt && scope.row.tax_amt > 0">
                            <div class="bpa-vac-pd__label">{{ strings.tax || 'Tax' }}:</div>
                            <div class="bpa-vac-pd__val">+{{ scope.row.tax_amt_with_currency }}</div>
                          </div>
                          <div class="bpa-vac-pd__item __bpa-pd-is-total-item">
                            <div class="bpa-vac-pd__label">{{ strings.total_amount_title || 'Total Amount' }}<span v-if="scope.row.bookingpress_price_display_setting === 'include_taxes' && scope.row.bookingpress_included_tax_label" :aria-label="scope.row.bookingpress_included_tax_label">{{ ' ' + scope.row.bookingpress_included_tax_label }}</span>:</div>
                            <div class="bpa-vac-pd__val bpa-front-text-primary-color">{{ totalLabel(scope.row) }}</div>
                          </div>
                          <!-- Row detail extensions, 'payment' placement (PR-e1):
                               inside the payment-details section, after the total
                               (legacy payment after-subtotal / modified hooks — e.g.
                               tip / gift-card lines). Each receives { row, ctx }. -->
                          <component v-for="d in paymentRowDetails" :key="d.id" :is="d.component" :row="scope.row" :ctx="extensionCtx" placement="payment"></component>
                        </div>

                        <!-- Action area -->
                        <div class="bpa-ma-vac--action-btn-group" v-if="hasActions(scope.row)">
                          <!-- Extension row actions (Pro / add-ons) render first to
                               preserve the legacy order (Reschedule, Book Again, Cancel). -->
                          <component v-for="act in expandRowActions" :key="act.id" :is="act.component" :row="scope.row" :ctx="extensionCtx" placement="expand"></component>
                          <bp-ui-button v-if="canBookAgain(scope.row)" @click="open_book_again_page_func($event, scope.row.bookingpress_appointment_booking_id,scope.row.book_again_page_url)" :underline="false" class="bpa-front-btn bpa-front-btn__small bpa_focusable" :aria-label="strings.book_again_button_title">
                          <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_2344_779)">
                          <g clip-path="url(#clip1_2344_779)"><path class="bpa-my-booking-front-icon" d="M3.13636 17.3636C2.68636 17.3636 2.30114 17.2034 1.98068 16.883C1.66023 16.5625 1.5 16.1773 1.5 15.7273V4.27273C1.5 3.82273 1.66023 3.4375 1.98068 3.11705C2.30114 2.79659 2.68636 2.63636 3.13636 2.63636H3.95455V1H5.59091V2.63636H12.1364V1H13.7727V2.63636H14.5909C15.0409 2.63636 15.4261 2.79659 15.7466 3.11705C16.067 3.4375 16.2273 3.82273 16.2273 4.27273V9.18182H14.5909V7.54545H3.13636V15.7273H8.86364V17.3636H3.13636ZM14.5909 19C13.5955 19 12.7261 18.6898 11.983 18.0693C11.2398 17.4489 10.7727 16.6682 10.5818 15.7273H11.85C12.0273 16.3273 12.3648 16.8182 12.8625 17.2C13.3602 17.5818 13.9364 17.7727 14.5909 17.7727C15.3818 17.7727 16.0568 17.4932 16.6159 16.9341C17.175 16.375 17.4545 15.7 17.4545 14.9091C17.4545 14.1182 17.175 13.4432 16.6159 12.8841C16.0568 12.325 15.3818 12.0455 14.5909 12.0455C14.1955 12.0455 13.8273 12.117 13.4864 12.2602C13.1455 12.4034 12.8455 12.6045 12.5864 12.8636H13.7727V14.0909H10.5V10.8182H11.7273V11.9841C12.0955 11.6295 12.525 11.3466 13.0159 11.1352C13.5068 10.9239 14.0318 10.8182 14.5909 10.8182C15.7227 10.8182 16.6875 11.217 17.4852 12.0148C18.283 12.8125 18.6818 13.7773 18.6818 14.9091C18.6818 16.0409 18.283 17.0057 17.4852 17.8034C16.6875 18.6011 15.7227 19 14.5909 19ZM3.13636 5.90909H14.5909V4.27273H3.13636V5.90909Z" fill="#727E95" stroke="#727E95" stroke-width="0.2"/></g></g><defs><clipPath id="clip0_2344_779">
                          <rect width="20" height="20" fill="white"/></clipPath><clipPath id="clip1_2344_779"><rect width="20" height="20" fill="white"/></clipPath></defs>
                          </svg>	{{ strings.book_again_button_title || 'Book Again' }}</bp-ui-button>
                          <template v-if="canCancel(scope.row)">
                            <!-- Refundable (Pro): open the refund-amount preview before cancelling -->
                            <bp-ui-button v-if="isRefundable(scope.row)"
                              class="bpa-front-btn bpa-front-btn__small bpa-front-btn__ma-refund bpa_focusable"
                              :disabled="cancelingId !== null" :aria-label="strings.cancel_appointment_title"
                              @click="openRefundPreview(scope.row)">{{ strings.cancel_appointment_title || 'Cancel Appointment' }}</bp-ui-button>
                            <!-- Non-refundable: existing plain cancel popconfirm -->
                            <bp-ui-popconfirm v-else
                              :title="strings.cancel_appointment_confirmation_message"
                              :confirm-button-text="strings.cancel_appointment_yes_btn_text || 'Yes'"
                              :cancel-button-text="strings.cancel_appointment_no_btn_text || 'No'"
                              confirm-button-type="bpa-front-btn bpa-front-btn__small bpa-front-btn--danger"
                              cancel-button-type="bpa-front-btn bpa-front-btn__small"
                              :teleported="false"
                              :icon="false"
                              data-class="bp-ui-popconfirm--ma-cancel"
                              @confirm="confirmCancel(scope.row)">
                              <template #reference>
                                <bp-ui-button class="bpa-front-btn bpa-front-btn__small bpa_focusable"
                                  :disabled="cancelingId !== null" :aria-label="strings.cancel_appointment_title">
                                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M14.0397 3.49904H14.7892C15.6137 3.49904 16.2882 4.17361 16.2882 4.99808V9.8203C15.8477 9.42598 15.3419 9.10298 14.7892 8.86944V7.24664H4.29592V14.7418C4.29592 15.1541 4.6332 15.4914 5.04544 15.4914H7.55047C7.72418 16.0334 7.98125 16.5381 8.30658 16.9904H4.29592C3.46395 16.9904 2.79688 16.3158 2.79688 15.4914L2.80437 4.99808C2.80437 4.17361 3.46395 3.49904 4.29592 3.49904H5.04544V2.74952C5.04544 2.33728 5.38272 2 5.79496 2C6.20719 2 6.54448 2.33728 6.54448 2.74952V3.49904H12.5406V2.74952C12.5406 2.33728 12.8779 2 13.2902 2C13.7024 2 14.0397 2.33728 14.0397 2.74952V3.49904Z" />
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M8.27344 13.8431C8.27344 16.2817 10.2502 18.2584 12.6888 18.2584C15.1274 18.2584 17.1041 16.2817 17.1041 13.8431C17.1041 11.4045 15.1274 9.42773 12.6888 9.42773C10.2502 9.42773 8.27344 11.4045 8.27344 13.8431ZM13.3151 13.8411L14.5622 12.5944L14.5603 12.5925L14.5617 12.5912L13.9372 11.9668L12.689 13.215L11.4408 11.9668L10.8164 12.5912L10.8175 12.5923L10.8154 12.5944L12.0627 13.8413L10.8189 15.0851L10.8204 15.0867L10.8154 15.0918L11.4401 15.7165L12.6888 14.4674L13.9375 15.7165L14.5622 15.0918L14.5574 15.0869L14.5592 15.0851L13.3151 13.8411Z" />
                                  </svg> {{ strings.cancel_appointment_title || 'Cancel Appointment' }}</bp-ui-button>
                              </template>
                            </bp-ui-popconfirm>
                          </template>
                        </div>
                      </div>
                    </template>
                  </bp-ui-table-column>

                  <bp-ui-table-column :label="strings.id_main_heading || 'ID'" min-width="60">
                    <template #default="scope"><span class="bpa-cp-ma-cell-val">#{{ scope.row.booking_id }}</span></template>
                  </bp-ui-table-column>
                  <bp-ui-table-column prop="bookingpress_service_name" :label="strings.service_main_heading || 'Service'" sortable min-width="120">
                    <template #default="scope">
                      <!-- Multi Service (add-on): first service name + "+N" hover popover
                           listing the remaining services (legacy parity:
                           bookingpress_mybooking_multiple_service_name_fetch). -->
                      <div class="bpa-multi-services" v-if="isMultiService(scope.row) && scope.row.bookingpress_multiple_service_name">
                        <span>{{ scope.row.bookingpress_multiple_service_name }}</span>
                        <bp-ui-popover v-if="scope.row.bookingpress_multiple_service_total > 0" placement="bottom-start" :title="strings.multiservice_other_title || 'Other'" :width="280" trigger="hover" popper-class="bpa-card-item-extra-popover bpa-card-item-multi-service-popover">
                          <div class="bpa-card-item-multi-service-content">{{ scope.row.bookingpress_multiple_service_extra_name }}</div>
                          <template #reference>
                            <span class="bpa-card__item-extra-tooltip"><bp-ui-link class="bpa-iet__label" :underline="false">+{{ scope.row.bookingpress_multiple_service_total }}</bp-ui-link></span>
                          </template>
                        </bp-ui-popover>
                      </div>
                      <span v-else>{{ scope.row.bookingpress_service_name }}</span>
                    </template>
                  </bp-ui-table-column>
                  <bp-ui-table-column prop="bookingpress_appointment_date" :label="strings.date_main_heading || 'Date'" sortable min-width="150">
                    <template #default="scope">
                      <div class="bpa-ma-date-time-details">
                        <div class="bpa-ma-dt__date-val">{{ scope.row.bookingpress_appointment_formatted_date }} {{ scope.row.bookingpress_appointment_formatted_start_time }}</div>
                        <div class="bpa-ma-dt__time-val"><bp-ui-icon class="bp-icon-clock"></bp-ui-icon>{{ scope.row.bookingpress_service_duration_val }} {{ scope.row.bookingpress_service_duration_label }}</div>
                      </div>
                    </template>
                  </bp-ui-table-column>
                  <bp-ui-table-column :label="strings.status_main_heading || 'Status'" min-width="80" align="center" >
                    <template #default="scope">
                      <bp-ui-tooltip :content="scope.row.bookingpress_appointment_status_label" placement="top" :open-delay="300" >
                        <div class="bpa-ma-status-box bpa_focusable" :class="statusBoxClass(scope.row)" >
                          <div class="bpa-sb__circle"></div>
                        </div>
                      </bp-ui-tooltip>
                    </template>
                  </bp-ui-table-column>
                  <bp-ui-table-column v-if="hasStaffColumn" :label="strings.staff_main_heading || 'Staff'" min-width="120">
                    <template #default="scope">
                      <span class="bpa-cp-ma-cell-val">{{ staffName(scope.row) }}</span>
                      <span class="bpa-front-pill bpa-front-mb-v3-members-pill" v-if="scope.row.selected_extra_members && scope.row.selected_extra_members > 1">+{{ scope.row.selected_extra_members - 1 }}</span>
                    </template>
                  </bp-ui-table-column>
                  <bp-ui-table-column :label="strings.payment_main_heading || 'Payment'" min-width="100">
                    <template #default="scope">
                      <!-- Amount + indicator-icon strip (legacy Pro parity:
                           appointment_my_appointments.php:467-486). The amount sits in
                           .bpa-front__ar-body and the transaction-type icons (cart /
                           gift card / package / recurring / deposit) in .bpa-front__ar-icons,
                           after the amount. Each icon is gated on the same row flag the
                           legacy template uses. -->
                      <div class="bpa-front-cp-ma__amount-row">
                        <div class="bpa-front__ar-body">
                          <span class="bpa-cp-ma-cell-val">{{ totalLabel(scope.row) }}</span>
                        </div>
                        <div class="bpa-front__ar-icons">
                          <bp-ui-tooltip :content="strings.cart_transaction || 'Cart Transaction'" placement="top" v-if="String(scope.row.is_cart) === '1'">
                            <svg class="bpa-front-appointment-cart-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 3c0 .55.45 1 1 1h1l3.6 7.59-1.35 2.44C4.52 15.37 5.48 17 7 17h11c.55 0 1-.45 1-1s-.45-1-1-1H7l1.1-2h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.37-.66-.11-1.48-.87-1.48H5.21l-.67-1.43c-.16-.35-.52-.57-.9-.57H2c-.55 0-1 .45-1 1zm16 15c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                          </bp-ui-tooltip>
                          <bp-ui-tooltip :content="strings.gift_card_transaction || 'Gift Card Transaction'" placement="top" v-if="String(scope.row.bookingpress_purchase_type) === '5'">
                            <span class="bpgc-apc__gift-card-icon">
                              <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="M6.58256 4.65332C6.35224 4.65332 6.10048 4.73606 5.9267 4.93212C5.69911 5.18888 5.62776 5.95616 5.6362 6.34337C6.02048 6.35267 6.75518 6.34248 7.05282 6.04652C7.36403 5.73701 7.44919 5.19619 7.11825 4.86706C6.97422 4.72382 6.78191 4.65332 6.58256 4.65332ZM3.6494 4.65332C3.87972 4.65332 4.13148 4.73606 4.30526 4.93212C4.53285 5.18888 4.6042 5.95616 4.59576 6.34337C4.21148 6.35267 3.47675 6.34248 3.17914 6.04652C2.86793 5.73701 2.78277 5.19619 3.11371 4.86706C3.25774 4.72382 3.45005 4.65332 3.6494 4.65332Z"/><path d="M7.84516 4.14053C8.42954 4.72159 8.50739 5.63742 8.10701 6.34343H16V3.76525C16 2.95605 15.3428 2.30005 14.5321 2.30005H5.63074V3.88203C6.35012 3.47612 7.25911 3.55779 7.84516 4.14053C8.17809 4.47157 7.25911 3.55779 7.84516 4.14053ZM3.47757 9.22719L2.74933 8.50367L3.88072 7.36907H0V12.1169C0 12.9261 0.657197 13.5821 1.46786 13.5821H4.60321V8.09837L3.47757 9.22719ZM6.34872 7.36907L7.46503 8.49431L6.73491 9.21595L5.63074 8.10296V13.5821H14.5321C15.3428 13.5821 16 12.9261 16 12.1169V7.36907H6.34872ZM2.12694 6.34343C1.7265 5.63731 1.80448 4.72155 2.38879 4.14053C2.9748 3.55779 3.88379 3.47615 4.60321 3.88203V2.30005H1.46786C0.657197 2.30005 0 2.95605 0 3.76525V6.34343H2.12694Z"/></svg>
                            </span>
                          </bp-ui-tooltip>
                          <bp-ui-tooltip :content="strings.package_transaction || 'Package Transaction'" placement="top" v-if="String(scope.row.bookingpress_purchase_type) === '3'">
                            <span class="bpp-apc__package-icon">
                              <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M7.98832 9.995H2.14277C1.6796 9.995 1.30061 10.3739 1.30061 10.8371V17.1579C1.30061 17.6211 1.6796 18 2.14277 18H8.08661C8.28473 18 8.28473 17.7872 8.28473 17.7872V10.2812C8.28467 10.2812 8.28467 9.995 7.98832 9.995ZM15.849 9.995H10.0094C9.65375 9.995 9.707 10.3654 9.707 10.3654V17.794C9.707 17.794 9.70412 17.9998 9.91806 17.9998H15.8489C16.3121 17.9998 16.6911 17.6209 16.6911 17.1577V10.8371C16.6912 10.3739 16.3122 9.995 15.849 9.995ZM8.28467 5.02167C8.28467 5.02167 8.28467 4.73631 8.00268 4.73631H1.19177C0.7286 4.73631 0.349609 5.1153 0.349609 5.57841V8.20717C0.349609 8.67034 0.7286 9.04927 1.19177 9.04927H8.02575C8.28467 9.04927 8.28467 8.82471 8.28467 8.82471V5.02167ZM16.8 4.73631H9.96733C9.70713 4.73631 9.70713 4.98425 9.70713 4.98425V8.82992C9.70713 8.82992 9.70713 9.04927 10.0235 9.04927H16.8C17.2631 9.04927 17.6421 8.67034 17.6421 8.20717V5.57841C17.6421 5.1153 17.2631 4.73631 16.8 4.73631ZM5.4203 4.11325C5.03499 4.11325 4.68306 4.08269 4.37444 4.02238C3.59057 3.86924 3.05181 3.57118 2.72737 3.11126C2.43667 2.69907 2.3477 2.19093 2.46286 1.60088C2.6646 0.568517 3.35791 0 4.41494 0C4.63864 0 4.88431 0.0258305 5.14519 0.0768166C5.8088 0.20646 6.65759 0.586985 7.41576 1.0947C8.70207 1.95619 8.76569 2.49175 8.70164 2.8197C8.60746 3.30158 8.15705 3.64591 7.32464 3.87243C6.76226 4.02545 6.06815 4.11325 5.4203 4.11325ZM4.415 1.34975C4.00564 1.34975 3.86495 1.46412 3.78771 1.85967C3.72451 2.18308 3.80421 2.29603 3.83035 2.33315C3.9398 2.48838 4.22492 2.61784 4.63324 2.69754C4.85357 2.74061 5.1258 2.76337 5.42024 2.76337C6.06772 2.76337 6.63814 2.66527 7.01664 2.55796C7.04419 2.55016 7.08696 2.51759 7.04112 2.49028C6.54629 2.08718 5.641 1.54891 4.88639 1.40147C4.71061 1.36724 4.55195 1.34975 4.415 1.34975ZM12.5909 4.11325H12.5908C11.943 4.11325 11.2489 4.02545 10.6865 3.87243C9.85407 3.64597 9.40372 3.30158 9.30954 2.81976C9.24555 2.49182 9.30905 1.95625 10.5955 1.09476C11.3535 0.587046 12.2023 0.206521 12.8661 0.076878C13.1269 0.0258919 13.3726 6.13551e-05 13.5961 6.13551e-05C14.6534 6.13551e-05 15.3466 0.568639 15.5482 1.601C15.6635 2.19099 15.5746 2.69913 15.2838 3.11132C14.9594 3.5713 14.4207 3.8693 13.6366 4.02244C13.3281 4.08263 12.9762 4.11325 12.5909 4.11325ZM10.9809 2.48194C10.937 2.5074 10.9582 2.54759 10.981 2.55415C11.3593 2.66294 11.9357 2.76344 12.5908 2.76344C12.8854 2.76344 13.1575 2.74067 13.3779 2.6976C13.7861 2.61784 14.0714 2.48844 14.1808 2.33321C14.207 2.29609 14.2868 2.18314 14.2234 1.85974C14.1462 1.46418 14.0055 1.34981 13.5961 1.34981C13.4592 1.34981 13.3006 1.36724 13.1247 1.4016C12.3701 1.54897 11.4757 2.07877 10.9809 2.48194Z"/></svg>
                            </span>
                          </bp-ui-tooltip>
                          <bp-ui-tooltip :content="strings.recurring_transaction || 'Recurring Transaction'" placement="top" v-if="String(scope.row.bookingpress_is_recurring) === '1'">
                            <svg width="15" height="15" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0 8.53745C0 8.82364 0.113692 9.09812 0.316064 9.30049C0.518437 9.50287 0.792913 9.61656 1.07911 9.61656C1.3578 9.6082 1.62245 9.49231 1.81758 9.29315C2.01271 9.094 2.12318 8.82704 2.12585 8.54824C2.14747 7.32275 2.5165 6.12857 3.19003 5.10453C3.86355 4.0805 4.81391 3.26867 5.93062 2.76344C7.04733 2.25821 8.2845 2.08033 9.49831 2.25048C10.7121 2.42063 11.8527 2.93182 12.7875 3.72461H12.4421C12.3004 3.72461 12.1601 3.75252 12.0292 3.80675C11.8983 3.86098 11.7793 3.94047 11.6791 4.04068C11.5789 4.14088 11.4994 4.25984 11.4452 4.39076C11.3909 4.52169 11.363 4.66201 11.363 4.80372C11.363 4.94543 11.3909 5.08576 11.4452 5.21668C11.4994 5.3476 11.5789 5.46657 11.6791 5.56677C11.7793 5.66697 11.8983 5.74646 12.0292 5.80069C12.1601 5.85492 12.3004 5.88283 12.4421 5.88283H15.496C15.7822 5.88283 16.0567 5.76914 16.2591 5.56677C16.4614 5.3644 16.5751 5.08992 16.5751 4.80372V1.74984C16.5751 1.46364 16.4614 1.18917 16.2591 0.986793C16.0567 0.78442 15.7822 0.670729 15.496 0.670729C15.2098 0.670729 14.9354 0.78442 14.733 0.986793C14.5306 1.18917 14.4169 1.46364 14.4169 1.74984V2.22465C13.1834 1.11118 11.6547 0.377381 10.0144 0.11135C8.3741 -0.154681 6.69186 0.0583593 5.16965 0.724896C3.64744 1.39143 2.34995 2.48314 1.43294 3.86896C0.515929 5.25477 0.0183721 6.8758 0 8.53745ZM15.8521 9.30361C15.8521 9.01742 15.9658 8.74294 16.1682 8.54057C16.3706 8.3382 16.6451 8.2245 16.9312 8.2245C17.2137 8.22731 17.4838 8.34077 17.6836 8.54051C17.8833 8.74026 17.9968 9.01036 17.9996 9.29282C18.0174 10.4421 17.8029 11.5831 17.369 12.6475C16.9351 13.7119 16.2907 14.6777 15.4744 15.4869C13.9087 17.0448 11.8054 17.9432 9.59738 17.9974C7.38934 18.0516 5.24451 17.2574 3.60423 15.7783V16.2531C3.60423 16.5393 3.49054 16.8138 3.28817 17.0161C3.08579 17.2185 2.81132 17.3322 2.52512 17.3322C2.23892 17.3322 1.96444 17.2185 1.76207 17.0161C1.5597 16.8138 1.44601 16.5393 1.44601 16.2531V13.1992C1.44601 12.913 1.5597 12.6385 1.76207 12.4362C1.96444 12.2338 2.23892 12.1201 2.52512 12.1201H5.61138C5.75309 12.1201 5.89341 12.148 6.02433 12.2022C6.15526 12.2565 6.27422 12.336 6.37442 12.4362C6.47463 12.5364 6.55411 12.6553 6.60834 12.7862C6.66257 12.9172 6.69049 13.0575 6.69049 13.1992C6.69049 13.3409 6.66257 13.4812 6.60834 13.6122C6.55411 13.7431 6.47463 13.862 6.37442 13.9623C6.27422 14.0625 6.15526 14.1419 6.02433 14.1962C5.89341 14.2504 5.75309 14.2783 5.61138 14.2783H5.23369C6.17914 15.0658 7.32942 15.5676 8.54979 15.7249C9.77015 15.8822 11.0101 15.6885 12.1243 15.1665C13.2386 14.6444 14.181 13.8157 14.8412 12.7774C15.5014 11.739 15.8521 10.5341 15.8521 9.30361Z" fill="#727E95"/></svg>
                          </bp-ui-tooltip>
                          <bp-ui-tooltip :content="strings.deposit || 'Deposit'" placement="top" v-if="String(scope.row.is_deposit) === '1'">
                            <span class="bpa-front-ari__deposit-icon">
                              <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.9596 12.2237C16.8902 12.0273 16.746 11.8662 16.5583 11.7756C16.3706 11.685 16.1548 11.6723 15.9578 11.7402L13.7872 12.4116C13.2376 12.9125 13.0288 12.7838 9.00068 12.7838C8.90842 12.7838 8.81994 12.7471 8.75471 12.6819C8.68947 12.6167 8.65282 12.5282 8.65282 12.4359C8.65282 12.3437 8.68947 12.2552 8.75471 12.1899C8.81994 12.1247 8.90842 12.0881 9.00068 12.0881C13.1749 12.0881 13.0323 12.1681 13.3384 11.862C13.4551 11.7331 13.5206 11.5661 13.5228 11.3923C13.5228 11.2078 13.4495 11.0309 13.319 10.9004C13.1886 10.7699 13.0116 10.6966 12.8271 10.6966H9.10504C8.62152 10.6966 8.21801 10.0009 6.80919 10.0009H4.47856V13.6256L4.92729 13.8795C6.20362 14.6153 7.63128 15.0496 9.10115 15.149C10.571 15.2485 12.0442 15.0106 13.408 14.4535L16.5387 13.1908C16.7162 13.1104 16.8576 12.967 16.9354 12.7883C17.0132 12.6096 17.0218 12.4084 16.9596 12.2237ZM1 14.523H3.78285V9.30521H1V14.523ZM2.0714 12.9994C2.09103 12.9518 2.12099 12.9092 2.1591 12.8746C2.19722 12.84 2.24255 12.8142 2.29181 12.7993C2.34107 12.7843 2.39304 12.7805 2.44398 12.788C2.49491 12.7956 2.54353 12.8143 2.58633 12.8429C2.62913 12.8716 2.66504 12.9093 2.69147 12.9535C2.71791 12.9977 2.7342 13.0472 2.73919 13.0984C2.74417 13.1497 2.73771 13.2014 2.72028 13.2499C2.70285 13.2983 2.67489 13.3423 2.6384 13.3786C2.58145 13.4353 2.50661 13.4705 2.42662 13.4783C2.34663 13.4861 2.26641 13.4659 2.1996 13.4213C2.13279 13.3766 2.08351 13.3102 2.06014 13.2333C2.03677 13.1564 2.04074 13.0737 2.0714 12.9994ZM11.4357 8.95736C12.1237 8.95736 12.7962 8.75334 13.3683 8.37112C13.9403 7.98889 14.3862 7.44561 14.6494 6.80999C14.9127 6.17437 14.9816 5.47494 14.8474 4.80017C14.7132 4.1254 14.3819 3.50558 13.8954 3.01909C13.4089 2.53261 12.7891 2.20131 12.1143 2.06709C11.4395 1.93286 10.7401 2.00175 10.1045 2.26504C9.46886 2.52832 8.92558 2.97417 8.54336 3.54622C8.16113 4.11827 7.95711 4.79081 7.95711 5.4788C7.95711 6.40137 8.3236 7.28616 8.97596 7.93851C9.62831 8.59087 10.5131 8.95736 11.4357 8.95736ZM11.7835 5.82666H11.0878C10.811 5.82666 10.5456 5.71671 10.3499 5.521C10.1542 5.3253 10.0442 5.05986 10.0442 4.78309C10.0442 4.50632 10.1542 4.24088 10.3499 4.04518C10.5456 3.84947 10.811 3.73952 11.0878 3.73952V3.39167C11.0878 3.29941 11.1245 3.21093 11.1897 3.1457C11.2549 3.08046 11.3434 3.04381 11.4357 3.04381C11.5279 3.04381 11.6164 3.08046 11.6816 3.1457C11.7469 3.21093 11.7835 3.29941 11.7835 3.39167V3.73952H12.4792C12.5715 3.73952 12.66 3.77617 12.7252 3.84141C12.7904 3.90664 12.8271 3.99512 12.8271 4.08738C12.8271 4.17964 12.7904 4.26812 12.7252 4.33335C12.66 4.39859 12.5715 4.43524 12.4792 4.43524H11.0878C10.9956 4.43524 10.9071 4.47188 10.8418 4.53712C10.7766 4.60236 10.74 4.69083 10.74 4.78309C10.74 4.87535 10.7766 4.96383 10.8418 5.02906C10.9071 5.0943 10.9956 5.13095 11.0878 5.13095H11.7835C12.0603 5.13095 12.3257 5.24089 12.5214 5.4366C12.7171 5.63231 12.8271 5.89774 12.8271 6.17451C12.8271 6.45128 12.7171 6.71672 12.5214 6.91243C12.3257 7.10813 12.0603 7.21808 11.7835 7.21808V7.56594C11.7835 7.65819 11.7469 7.74667 11.6816 7.81191C11.6164 7.87714 11.5279 7.91379 11.4357 7.91379C11.3434 7.91379 11.2549 7.87714 11.1897 7.81191C11.1245 7.74667 11.0878 7.65819 11.0878 7.56594V7.21808H10.3921C10.2998 7.21808 10.2114 7.18143 10.1461 7.1162C10.0809 7.05096 10.0442 6.96248 10.0442 6.87022C10.0442 6.77797 10.0809 6.68949 10.1461 6.62425C10.2114 6.55902 10.2998 6.52237 10.3921 6.52237H11.7835C11.8758 6.52237 11.9643 6.48572 12.0295 6.42049C12.0947 6.35525 12.1314 6.26677 12.1314 6.17451C12.1314 6.08226 12.0947 5.99378 12.0295 5.92854C11.9643 5.86331 11.8758 5.82666 11.7835 5.82666Z"/></svg>
                            </span>
                          </bp-ui-tooltip>
                        </div>
                      </div>
                      <!-- Hover action card (legacy parity): revealed on row hover by the
                           actions-wrap CSS. bpa-ma--action-btn-wrapper is the class
                           bookingpress_full_row_clickable guards on, so clicks in here
                           never toggle the expand row. -->
                      <div class="bpa-front-ma-table-actions-wrap bpa-ma--action-btn-wrapper" v-if="canCancel(scope.row) || hoverRowActions.length > 0">
                        <div class="bpa-front-ma-taw__card">
                          <!-- Extension row actions (Pro / add-ons) render first to
                               preserve the legacy order (Reschedule, Book Again, Cancel). -->
                          <component v-for="act in hoverRowActions" :key="act.id" :is="act.component" :row="scope.row" :ctx="extensionCtx" placement="hover"></component>

                            <bp-ui-tooltip :content="strings.book_again_button_title" placement="top" :open-delay="300" >
                              <bp-ui-button v-if="canBookAgain(scope.row)" @click="open_book_again_page_func($event, scope.row.bookingpress_appointment_booking_id,scope.row.book_again_page_url)" :underline="false" class="bpa-front-btn bpa-front-btn--icon-without-box bpa_focusable" :aria-label="strings.book_again_button_title">
                              <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_2344_779)">
                              <g clip-path="url(#clip1_2344_779)"><path class="bpa-my-booking-front-icon" d="M3.13636 17.3636C2.68636 17.3636 2.30114 17.2034 1.98068 16.883C1.66023 16.5625 1.5 16.1773 1.5 15.7273V4.27273C1.5 3.82273 1.66023 3.4375 1.98068 3.11705C2.30114 2.79659 2.68636 2.63636 3.13636 2.63636H3.95455V1H5.59091V2.63636H12.1364V1H13.7727V2.63636H14.5909C15.0409 2.63636 15.4261 2.79659 15.7466 3.11705C16.067 3.4375 16.2273 3.82273 16.2273 4.27273V9.18182H14.5909V7.54545H3.13636V15.7273H8.86364V17.3636H3.13636ZM14.5909 19C13.5955 19 12.7261 18.6898 11.983 18.0693C11.2398 17.4489 10.7727 16.6682 10.5818 15.7273H11.85C12.0273 16.3273 12.3648 16.8182 12.8625 17.2C13.3602 17.5818 13.9364 17.7727 14.5909 17.7727C15.3818 17.7727 16.0568 17.4932 16.6159 16.9341C17.175 16.375 17.4545 15.7 17.4545 14.9091C17.4545 14.1182 17.175 13.4432 16.6159 12.8841C16.0568 12.325 15.3818 12.0455 14.5909 12.0455C14.1955 12.0455 13.8273 12.117 13.4864 12.2602C13.1455 12.4034 12.8455 12.6045 12.5864 12.8636H13.7727V14.0909H10.5V10.8182H11.7273V11.9841C12.0955 11.6295 12.525 11.3466 13.0159 11.1352C13.5068 10.9239 14.0318 10.8182 14.5909 10.8182C15.7227 10.8182 16.6875 11.217 17.4852 12.0148C18.283 12.8125 18.6818 13.7773 18.6818 14.9091C18.6818 16.0409 18.283 17.0057 17.4852 17.8034C16.6875 18.6011 15.7227 19 14.5909 19ZM3.13636 5.90909H14.5909V4.27273H3.13636V5.90909Z" fill="#727E95" stroke="#727E95" stroke-width="0.2"/></g></g><defs><clipPath id="clip0_2344_779">
                              <rect width="20" height="20" fill="white"/></clipPath><clipPath id="clip1_2344_779"><rect width="20" height="20" fill="white"/></clipPath></defs>
                              </svg></bp-ui-button>
                            </bp-ui-tooltip>
                            <bp-ui-tooltip effect="dark" v-if="canCancel(scope.row)" :content="strings.cancel_appointment_title || 'Cancel Appointment'" placement="top" open-delay="300">                           
                              <!-- Refundable (Pro): open the refund-amount preview before cancelling -->
                              <bp-ui-button v-if="isRefundable(scope.row)"
                                class="bpa-front-btn bpa-front-btn--icon-without-box bpa-front-btn__ma-refund bpa_focusable"
                                :disabled="cancelingId !== null" :aria-label="strings.cancel_appointment_title || 'Cancel Appointment'"
                                @click="openRefundPreview(scope.row)">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M14.0397 3.49904H14.7892C15.6137 3.49904 16.2882 4.17361 16.2882 4.99808V9.8203C15.8477 9.42598 15.3419 9.10298 14.7892 8.86944V7.24664H4.29592V14.7418C4.29592 15.1541 4.6332 15.4914 5.04544 15.4914H7.55047C7.72418 16.0334 7.98125 16.5381 8.30658 16.9904H4.29592C3.46395 16.9904 2.79688 16.3158 2.79688 15.4914L2.80437 4.99808C2.80437 4.17361 3.46395 3.49904 4.29592 3.49904H5.04544V2.74952C5.04544 2.33728 5.38272 2 5.79496 2C6.20719 2 6.54448 2.33728 6.54448 2.74952V3.49904H12.5406V2.74952C12.5406 2.33728 12.8779 2 13.2902 2C13.7024 2 14.0397 2.33728 14.0397 2.74952V3.49904Z" /><path fill-rule="evenodd" clip-rule="evenodd" d="M8.27344 13.8431C8.27344 16.2817 10.2502 18.2584 12.6888 18.2584C15.1274 18.2584 17.1041 16.2817 17.1041 13.8431C17.1041 11.4045 15.1274 9.42773 12.6888 9.42773C10.2502 9.42773 8.27344 11.4045 8.27344 13.8431ZM13.3151 13.8411L14.5622 12.5944L14.5603 12.5925L14.5617 12.5912L13.9372 11.9668L12.689 13.215L11.4408 11.9668L10.8164 12.5912L10.8175 12.5923L10.8154 12.5944L12.0627 13.8413L10.8189 15.0851L10.8204 15.0867L10.8154 15.0918L11.4401 15.7165L12.6888 14.4674L13.9375 15.7165L14.5622 15.0918L14.5574 15.0869L14.5592 15.0851L13.3151 13.8411Z" /></svg>
                              </bp-ui-button>
                            </bp-ui-tooltip>
                              <!-- Non-refundable: plain cancel popconfirm (legacy Lite parity).
                                   The reference MUST be the button directly — wrapping it in a
                                   bp-ui-tooltip shadows the popconfirm's forwardRef context, so
                                   the popconfirm never binds its click trigger and never opens
                                   (the working expand-card popconfirm has no such wrapper).
                                   Left teleported (EP default): the button sits at the right
                                   edge of the row, so an in-wrap popup gets clipped by the
                                   content panel; teleporting to body lets it float above and
                                   auto-shift into the viewport. placement bottom-end opens it
                                   inward from the edge. The hover title is kept via the button's
                                   native title attribute. -->
                              <bp-ui-popconfirm v-if="(!isRefundable(scope.row) && canCancel(scope.row))" :title="strings.cancel_appointment_confirmation_message" :confirm-button-text="strings.cancel_appointment_yes_btn_text || 'Yes'" :cancel-button-text="strings.cancel_appointment_no_btn_text || 'No'" confirm-button-type="bpa-front-btn bpa-front-btn__small bpa-front-btn--danger" cancel-button-type="bpa-front-btn bpa-front-btn__small" popper-class="booking-cancel-confirm-wrapper" placement="bottom-end" :icon="false" @confirm="confirmCancel(scope.row)">
                                <template #reference>
                                  <bp-ui-button class="bpa-front-btn bpa-front-btn--icon-without-box bpa_focusable"
                                    :disabled="cancelingId !== null" :title="strings.cancel_appointment_title || 'Cancel Appointment'" :aria-label="strings.cancel_appointment_title || 'Cancel Appointment'">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M14.0397 3.49904H14.7892C15.6137 3.49904 16.2882 4.17361 16.2882 4.99808V9.8203C15.8477 9.42598 15.3419 9.10298 14.7892 8.86944V7.24664H4.29592V14.7418C4.29592 15.1541 4.6332 15.4914 5.04544 15.4914H7.55047C7.72418 16.0334 7.98125 16.5381 8.30658 16.9904H4.29592C3.46395 16.9904 2.79688 16.3158 2.79688 15.4914L2.80437 4.99808C2.80437 4.17361 3.46395 3.49904 4.29592 3.49904H5.04544V2.74952C5.04544 2.33728 5.38272 2 5.79496 2C6.20719 2 6.54448 2.33728 6.54448 2.74952V3.49904H12.5406V2.74952C12.5406 2.33728 12.8779 2 13.2902 2C13.7024 2 14.0397 2.33728 14.0397 2.74952V3.49904Z" /><path fill-rule="evenodd" clip-rule="evenodd" d="M8.27344 13.8431C8.27344 16.2817 10.2502 18.2584 12.6888 18.2584C15.1274 18.2584 17.1041 16.2817 17.1041 13.8431C17.1041 11.4045 15.1274 9.42773 12.6888 9.42773C10.2502 9.42773 8.27344 11.4045 8.27344 13.8431ZM13.3151 13.8411L14.5622 12.5944L14.5603 12.5925L14.5617 12.5912L13.9372 11.9668L12.689 13.215L11.4408 11.9668L10.8164 12.5912L10.8175 12.5923L10.8154 12.5944L12.0627 13.8413L10.8189 15.0851L10.8204 15.0867L10.8154 15.0918L11.4401 15.7165L12.6888 14.4674L13.9375 15.7165L14.5622 15.0918L14.5574 15.0869L14.5592 15.0851L13.3151 13.8411Z" /></svg>
                                  </bp-ui-button>
                                </template>
                              </bp-ui-popconfirm>
                          </div>
                        </div>
                    </template>
                  </bp-ui-table-column>
                </bp-ui-table>

                <!-- Pagination -->
                <div class="bpa-front-ma--pagination-wrapper" v-if="showPagination">
                  <bp-ui-pagination layout="prev, pager, next" :pager-count="5" :total="totalRecords"
                    :page-size="perPage" :current-page="currentPage" @current-change="goToPage" class="bpa_focusable"></bp-ui-pagination>
                </div>
              </div>
            </div>

            <!-- Extension tab bodies (Pro / add-ons, via api.registerTab). v-if
                 mounts a tab's component only while it is active, so extension
                 tabs lazy-load their data in their own mounted() hook. -->
            <template v-for="tab in extensionTabs" :key="tab.id">
              <div class="bpa-front-cp-body __bpa-cp-is-form-controls __bpa-is-active" v-if="currentTab === tab.id">
                <component :is="tab.component" :ctx="extensionCtx"></component>
              </div>
            </template>

            <!-- Delete Account body. The panel is the backend-configured
                 customizer HTML (delete_account_content: vector/SVG + headings,
                 legacy parity); the [bookingpress_delete_account] shortcode in
                 it was replaced server-side by an empty slot div, and the
                 reactive action block below teleports into that slot so the
                 buttons render exactly where the admin placed the shortcode.
                 Fallback (no content configured): translated heading +
                 description with an inline slot. -->
            <div class="bpa-front-cp-body bpa-front-mb-v3-delete-account" :class="currentTab === 'delete_account' ? '__bpa-is-active' : ''" v-show="currentTab === 'delete_account'" v-if="showDeleteAccount">
              <div class="bpa-front-mb-v3-da-content" v-if="deleteAccountHtml" v-html="deleteAccountHtml"></div>
              <template v-else>
                <div class="bpa-front-dab__left">
                  <div class="bpa-front-dab-left__title" :aria-label="strings.delete_account_heading_title">{{ strings.delete_account_heading_title || 'Delete Account' }}</div>
                  <div class="bpa-front-dab-left__desc" :aria-label="strings.delete_account_desc">{{ strings.delete_account_desc }}</div>
                </div>
                <div class="bpa-front-mb-v3-da-actions" :id="deleteAccountSlotId"></div>
              </template>
              <teleport :to="'#' + deleteAccountSlotId" v-if="deleteAccountSlotReady">
                <!-- Legacy UX: Delete fires immediately — the panel copy above
                     ("Sorry to see you go. Please Confirm") is the confirmation. -->
                <div class="bpa-front-dcw__body-btn-group">
                  <bp-ui-button class="bpa-front-btn bpa-front-btn__medium bpa-front-delete-account-txt bpa_focusable"
                    :disabled="deleting" @click="cancelDelete">{{ deleteAccountCancelLabel }}</bp-ui-button>
                  <bp-ui-button class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--danger bpa_focusable"
                    :disabled="!canDeleteAccount()" :aria-disabled="!canDeleteAccount()"
                    @click="deleteAccount">{{ deleteAccountDeleteLabel }}</bp-ui-button>
                </div>
                <div class="bpa-front-mb-v3-delete-confirm__error" v-if="deleteError" role="alert">{{ deleteError }}</div>
              </teleport>
            </div>
          </div>
        </div>

        <!-- Refund preview dialog (Pro, MB-7D): amounts shown before cancel -->
        <bp-ui-dialog v-model="refundDialog.open" custom-class="bpa-front-mb-v3-refund-dialog bpa-dialog--refund-appointments" :append-to-body="true" width="420px" @closed="closeRefundPreview">
          <div class="bpa-front-cp-rd__desc" v-if="strings.refund_policy_msg" :aria-label="strings.refund_policy_msg">{{ strings.refund_policy_msg }}</div>
          <div class="bpa-front-refund-content-row">
            <div class="bpa-front-rcr__item">
              <div class="bpa-front-rcr__item-label">{{ strings.paid_amount_text || 'Paid Amount' }}</div>
              <div class="bpa-front-rcr__item-val">{{ refundDialog.defaultRefundAmount }}</div>
            </div>
            <div class="bpa-front-rcr__item">
              <div class="bpa-front-rcr__item-label">{{ strings.refund_amount_text || 'Refund Amount' }}</div>
              <div class="bpa-front-rcr__item-val bpa-front-text-primary-color">{{ refundDialog.refundAmount }}</div>
            </div>
          </div>
          <div class="bpa-front-mb-v3-error bpa-front-ra__error-msg" v-if="refundDialog.error" role="alert">{{ refundDialog.error }}</div>
          <template #footer>
            <bp-ui-button class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--borderless bpa_focusable"
              :disabled="cancelingId !== null" @click="refundDialog.open = false">{{ strings.refund_cancel_text || 'Cancel' }}</bp-ui-button>
            <bp-ui-button class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--primary bpa_focusable"
              :disabled="cancelingId !== null" :aria-disabled="cancelingId !== null"
              :aria-label="strings.refund_apply_text" @click="applyRefund">{{ strings.refund_apply_text || 'Apply' }}</bp-ui-button>
          </template>
        </bp-ui-dialog>
        </template>
      </div>

    `,
  };
}

/**
 * Mount a single Vue 3 My Bookings instance.
 *
 * @param {string} instanceId
 * @param {object} initialState Per-instance config from the JSON island.
 * @returns {object|null} Mounted-instance handle (also stored on the registry).
 */
export function mountMyBookingsInstance(instanceId, initialState) {
  const cfg = initialState || {};
  const mountNode =
    document.querySelector(`[data-bp-mb-instance="${instanceId}"]`) ||
    document.querySelector(`#bookingpress-my-bookings-vue3-${instanceId}`);

  if (!mountNode) {
    // eslint-disable-next-line no-console
    console.warn('[bp-mb-v3] mount node missing:', instanceId);
    return null;
  }

  const app = createApp(createMyBookingsComponent(cfg));
  app.config.errorHandler = (err, _vm, info) => {
    // eslint-disable-next-line no-console
    console.error('[bp-mb-v3] runtime error', { instanceId, info, error: err });
  };

  // Register the BookingPress UI plugin (Element Plus) so the bp-ui-*
  // components used for legacy design parity (date picker, table, pagination,
  // tag, popconfirm, dialog) are available. The bootstrap module side-effect
  // imports `bookingpress-ui`, so `window.BookingPressUI` should already exist.
  if (typeof window !== 'undefined' && window.BookingPressUI && typeof window.BookingPressUI.install === 'function') {
    try { app.use(window.BookingPressUI); }
    catch (e) {
      // eslint-disable-next-line no-console
      console.warn('[bp-mb-v3] BookingPressUI install failed', e);
    }
  } else {
    // BookingPress UI (Element Plus) vendor module did not load. We still mount
    // so the page is NOT blank (sidebar/filters/text render); the bp-ui-* widgets
    // (date picker / table / dialog) will be inert. Make the cause loud.
    // eslint-disable-next-line no-console
    console.error('[bp-mb-v3] BookingPressUI (Element Plus) unavailable — bp-ui-* widgets will not render; mounting in degraded mode.');
  }

  // Mount inside a try/catch so a compile/mount-time failure never leaves the
  // shortcode stuck on its boot loader (blank page); surface it instead.
  let vm = null;
  try {
    vm = app.mount(mountNode);
  } catch (e) {
    // eslint-disable-next-line no-console
    console.error('[bp-mb-v3] mount failed', instanceId, e);
    return null;
  }

  // Per-instance extension api handed to add-on factories (see bootstrap.js
  // registerAddon). Thin wrappers around the root vm so add-ons never hold a
  // direct reference to Lite internals.
  const api = {
    registerTab(def) {
      try { vm.registerExtensionTab(def); } catch (e) {
        // eslint-disable-next-line no-console
        console.error('[bp-mb-v3] registerTab failed', instanceId, e);
      }
    },
    registerRowAction(def) {
      try { vm.registerExtensionRowAction(def); } catch (e) {
        // eslint-disable-next-line no-console
        console.error('[bp-mb-v3] registerRowAction failed', instanceId, e);
      }
    },
    // Register a single nav presenter component (PR-b1). Replaces Lite's
    // built-in dropdown nav; receives `:nav="navModel"`. No registration →
    // Lite renders its current dropdown (unchanged).
    registerNav(component) {
      try { vm.registerNavPresenter(component); } catch (e) {
        // eslint-disable-next-line no-console
        console.error('[bp-mb-v3] registerNav failed', instanceId, e);
      }
    },
    // Register a per-row detail component (PR-e1). Rendered inside the expand
    // card at `placement` ('connect' | 'details' | 'payment'); receives
    // `{ row, ctx }`. No registration → nothing added (Lite unchanged).
    registerRowDetail(def) {
      try { vm.registerExtensionRowDetail(def); } catch (e) {
        // eslint-disable-next-line no-console
        console.error('[bp-mb-v3] registerRowDetail failed', instanceId, e);
      }
    },
    // Register a single guest view component (PR-e1). Replaces Lite's static
    // "please login" message for logged-out visitors; receives `:ctx` so it can
    // call `ctx.onAuthenticated({ nonce, redirectUrl })` on success. No
    // registration → Lite renders its static message (unchanged).
    registerGuestView(component) {
      try { vm.registerGuestViewComponent(component); } catch (e) {
        // eslint-disable-next-line no-console
        console.error('[bp-mb-v3] registerGuestView failed', instanceId, e);
      }
    },
    reloadAppointments(page) {
      try { vm.loadAppointments(page || vm.currentPage); } catch (_e) {}
    },
    notifySuccess(msg) {
      try { vm.notifySuccess(msg); } catch (_e) {}
    },
  };

  if (!window.BookingPressMyBookingsV3) window.BookingPressMyBookingsV3 = { instances: {} };
  if (!window.BookingPressMyBookingsV3.instances) window.BookingPressMyBookingsV3.instances = {};
  const handle = { instanceId, app, vm, api, mountNode, config: cfg };
  window.BookingPressMyBookingsV3.instances[instanceId] = handle;
  return handle;
}

export default { mountMyBookingsInstance };
