/**
 * BasicDetailsStep — customer-details form for the Vue 3 booking form.
 *
 * Markup mirrors the released `[bookingpress_form]` step-3 panel:
 *   - `.bpa-front-default-card` > `.bpa-front-dc--body` + `.bpa-front-dc--footer`
 *   - `.bpa-front-module-container.bpa-front-module--basic-details`
 *   - `.bpa-front-module--bd-form` > `.bpa-bd-fields-row` > per-field column
 *   - Each field rendered as `.bp-form-item.bp-form-item--label-top.asterisk-right.bpa-form-item`
 *     with `.bpa-front-form-label` for the label and the released's
 *     `.bpa-front-form-control` / `.bpa-front-form-control--checkbox`
 *     control classes. Inline error appears under the field; a toast at
 *     the top of the card surfaces on a failed `Next` click.
 *
 * Required fields render a red asterisk (`<span class="bpa-front-required-asterisk">*</span>`)
 * next to their label — explicit request from Azhar.
 *
 * Fields come from `state.customer_form_fields` (delivered by the
 * backend's `FormFieldRepository` + `StateBuilder.prepare_form_fields`),
 * which already supplies the `fieldName`, `fieldLabel`, `fieldPlaceholder`,
 * `fieldType` ('Text' | 'Email' | 'Phone' | 'Textarea' | 'terms_and_conditions'),
 * `fieldRequired`, and the `vModelValue` (the key under
 * `appointment_step_form_data` the input is bound to). Validation
 * messages come from `state.customer_details_rule` (already keyed by
 * `vModelValue`).
 */
import { computed, inject, nextTick, reactive, ref } from 'vue';

const ICON_ERROR =
  '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">' +
    '<path d="M12 7c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1s-1-.45-1-1V8c0-.55.45-1 1-1zm-.01-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm1-3h-2v-2h2v2z"/>' +
  '</svg>';

const ICON_ARROW_LEFT =
  '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24">' +
    '<rect fill="none" height="24" width="24"/>' +
    '<path d="M9.71,18.29L9.71,18.29c0.39-0.39,0.39-1.02,0-1.41L5.83,13H21c0.55,0,1-0.45,1-1v0c0-0.55-0.45-1-1-1H5.83l3.88-3.88 c0.39-0.39,0.39-1.02,0-1.41l0,0c-0.39-0.39-1.02-0.39-1.41,0L2.71,11.3c-0.39,0.39-0.39,1.02,0,1.41l5.59,5.59 C8.68,18.68,9.32,18.68,9.71,18.29z"/>' +
  '</svg>';

const ICON_ARROW_RIGHT =
  '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24">' +
    '<rect fill="none" height="24" width="24"/>' +
    '<path d="M14.29,5.71L14.29,5.71c-0.39,0.39-0.39,1.02,0,1.41L18.17,11H3c-0.55,0-1,0.45-1,1v0c0,0.55,0.45,1,1,1h15.18l-3.88,3.88 c-0.39,0.39-0.39,1.02,0,1.41l0,0c0.39,0.39,1.02,0.39,1.41,0l5.59-5.59c0.39-0.39,0.39-1.02,0-1.41L15.7,5.71 C15.32,5.32,14.68,5.32,14.29,5.71z"/>' +
  '</svg>';

export default {
  name: 'BasicDetailsStep',
  setup() {
    const state = inject('state');
    const nav   = inject('nav');

    /** Per-field "user has interacted" flag — gates inline error display. */
    const touched = reactive({});

    /** Per-field "currently focused" flag — drives the `is-focus` class on
     *  wrappers (e.g. `.el-checkbox__input`) so the legacy box-shadow rule
     *  in `bookingpress_front.css` can fire while the keyboard focus ring
     *  is active. Text/email inputs don't need this because their wrapper
     *  already uses focusin/focusout DOM listeners. */
    const focused = reactive({});
    function setFocused(key, v) { focused[key] = !!v; }

    function value(key) { return state.appointment_step_form_data[key]; }
    function setValue(key, v) {
      const field = (state.customer_form_fields || []).find(f => f.vModelValue === key);
      if (field && field.fieldType === 'Phone' && typeof v === 'string') {
        v = v.replace(/[^0-9+\-\(\)\s]/g, '');
      }
      state.appointment_step_form_data[key] = v;
    }
    function markTouched(key) { touched[key] = true; }

    function isEmpty(field) {
      const v = state.appointment_step_form_data[field.vModelValue];
      return v === undefined || v === null || v === '' || (Array.isArray(v) && v.length === 0);
    }

    function ruleMessage(field, ruleType) {
      const rules = (state.customer_details_rule || {})[field.vModelValue] || [];
      for (const r of rules) {
        if ((ruleType === 'required' && r.required) || r.type === ruleType) {
          return r.message || '';
        }
      }
      return '';
    }

    function errorFor(field) {
      const key = field.vModelValue;
      // Surface the inline error only after the user has touched the
      // field OR after a failed Next click force-touched everything.
      if (!touched[key]) return '';

      if (field.fieldRequired && isEmpty(field)) {
        return ruleMessage(field, 'required')
          || field.fieldErrorMessage
          || (field.fieldLabel + ' is required');
      }
      if (field.fieldType === 'Email') {
        const v = state.appointment_step_form_data[key];
        if (v && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(String(v))) {
          return ruleMessage(field, 'email') || 'Please enter a valid email address';
        }
      }
      return '';
    }

    const visibleFields = computed(() => {
      return (state.customer_form_fields || []).filter(f => true);
    });

    const allValid = computed(() => {
      for (const f of visibleFields.value) {
        if (errorFor(f)) return false;
        if (f.fieldRequired && isEmpty(f)) return false;
      }
      return true;
    });

    // Toast surfaced when Next is pressed and one or more required fields
    // are missing. Mirrors the released `is_display_error` / `is_error_msg`
    // pattern in `bookingpress_step_navigation()`.
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

    /** Tab name of the next step ("Summary" by default). */
    const nextStepName = computed(() => {
      const cur = state.steps.find(s => s.id === state.currentTab);
      if (!cur || !cur.next_step) return '';
      const next = state.steps.find(s => s.id === cur.next_step);
      return (next && next.tab_name) || '';
    });

    // Whether this step has a previous step in the (possibly Pro-reordered)
    // schema. Lite's default order puts Basic Details third, so it always has
    // one and the back button shows as before. Pro's "Booking form sequence"
    // can place Basic Details first, in which case there's no previous step
    // and the back button must hide. Visibility-aware via nav: a hidden
    // previous step (is_display_step=0 stays in the array) must not count,
    // or goPrev() would be a no-op.
    const hasPrev = nav.hasPrev;

    // --- Programmatic label / error association -----------------------------
    //
    // Deterministic, per-instance ids so `<label for>` ↔ `<input id>` and
    // `aria-describedby` ↔ error `<p id>` resolve even with multiple form
    // instances on one page.
    function fieldId(field) {
      const key = String(field.vModelValue || field.fieldName || '').replace(/[^\w-]/g, '_');
      return 'bp-v3-f-' + state.instanceId + '-' + key;
    }
    function errorId(field) {
      return fieldId(field) + '-error';
    }

    /** `input-options` payload for the BpUiTelInput → forwarded verbatim onto
     *  the inner `<input type="tel">` (vue-tel-input contract), giving the
     *  phone field the same label/error associations as the native inputs. */
    function telInputOptions(field) {
      return {
        id: fieldId(field),
        required: !!field.fieldRequired,
        maxlength: 63,
        'aria-describedby': errorFor(field) ? errorId(field) : undefined,
      };
    }

    /** Move focus to a field's input — used after a failed submit so the
     *  keyboard user lands directly on the first problem field and hears
     *  its label + error (via aria-describedby). */
    function focusField(field) {
      nextTick(() => {
        const root = formRoot.value;
        if (!root) return;
        const wrap = root.querySelector('[data-bp-field="' + String(field.vModelValue) + '"]');
        if (!wrap) return;
        const target = wrap.querySelector('input:not([type="hidden"]), textarea, select')
          || wrap.querySelector('[tabindex]');
        if (target && typeof target.focus === 'function') {
          try { target.focus({ preventScroll: false }); } catch (_e) { target.focus(); }
        }
        if (target && typeof target.scrollIntoView === 'function') {
          try { target.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (_e) { /* ignore */ }
        }
      });
    }

    function next() {
      // Generic Pro seam: when an add-on owns the Basic Details field rendering
      // (it sets `config.suppressDefaultBasicFields` and renders into the
      // `basic-details-step:fields` slot), Lite cannot validate the field shapes
      // it no longer renders. It instead runs any synchronous validators the
      // add-on registered on its instance handle
      // (`window.BookingPressFormV3.instances[id].basicDetailsValidators`), each
      // returning `{ valid:boolean, message }`. The first failure blocks Next and
      // surfaces its message. Inert on a Lite-only render (flag unset).
      if (state.config && state.config.suppressDefaultBasicFields) {
        const reg = window.BookingPressFormV3 && window.BookingPressFormV3.instances;
        const handle = reg ? reg[state.instanceId] : null;
        const validators = (handle && Array.isArray(handle.basicDetailsValidators)) ? handle.basicDetailsValidators : [];
        for (const validate of validators) {
          let result = null;
          try { result = validate(); } catch (_e) { result = null; }
          if (result && result.valid === false) {
            setError(result.message || '');
            return;
          }
        }
        clearError();
        nav.goNext();
        return;
      }

      // Force-touch every required field so missing ones surface
      // inline. Surface the FIRST missing field's message as the toast.
      let firstMissing = null;
      for (const f of visibleFields.value) {
        if (f.fieldRequired) {
          touched[f.vModelValue] = true;
          if (isEmpty(f) && !firstMissing) firstMissing = f;
        }
      }
      if (!allValid.value) {
        if (firstMissing) {
          setError(
            ruleMessage(firstMissing, 'required')
              || firstMissing.fieldErrorMessage
              || (firstMissing.fieldLabel + ' is required')
          );
        }
        // Move focus to the first invalid field (missing OR format error)
        // so the failure is actionable without hunting for the red text.
        const firstInvalid = visibleFields.value.find(
          (f) => errorFor(f) || (f.fieldRequired && isEmpty(f))
        );
        if (firstInvalid) focusField(firstInvalid);
        return;
      }
      clearError();
      nav.goNext();
    }

    function prev() {
      clearError();
      nav.goPrev();
    }

    function onCheckboxChange(field, ev) {
      const checked = !!ev.target.checked;
      setValue(field.vModelValue, checked ? [true] : []);
      markTouched(field.vModelValue);
    }

    /** Released-form control flavour. */
    function controlType(field) {
      switch (field.fieldType) {
        case 'Email':   return 'email';
        case 'Phone':   return 'tel';
        case 'Textarea': return 'textarea';
        case 'terms_and_conditions': return 'terms';
        default: return 'text';
      }
    }

    /** Per-field column class — released uses 3-col grid on md+ via el-col md=8/24. */
    function colClass(field) {
      const cls = ['bpa-bd-field-col'];
      if (field.fieldName === 'terms_and_conditions') cls.push('bpa_terms_conditions');
      if (field.fieldName === 'note') cls.push('bpa-bd-field-col--full');
      return cls.join(' ');
    }

    function formItemClass(field) {
      const cls = ['bp-form-item', 'bp-form-item--label-top', 'bpa-form-item'];
      if (field.fieldRequired) cls.push('asterisk-right');
      if (errorFor(field))     cls.push('is-error');
      return cls.join(' ');
    }

    /** Default country for the BpUiTelInput. Falls back to 'us' when the
     *  backend doesn't tell us where we are (the released form lets admins
     *  configure this; surfacing it on `state.config.defaultCountry` is a
     *  cheap upgrade for later). */
    const defaultCountry = computed(() => {
      const v = state.config && state.config.defaultCountry;
      return v ? String(v).toLowerCase() : 'us';
    });

    /** Form root ref — scopes the focus-first-invalid query to this instance.
     *
     *  NOTE: the previous auto-focus-first-input-on-mount was removed in
     *  favour of the cross-step focus model (App.js moves focus to the step
     *  heading on every transition, announcing context first; one Tab then
     *  reaches the first field). Keeps Enter/Space behaviour identical on
     *  every step instead of this one stealing focus into an input. */
    const formRoot = ref(null);

    return {
      state,
      visibleFields,
      value,
      setValue,
      markTouched,
      focused,
      setFocused,
      errorFor,
      fieldId,
      errorId,
      telInputOptions,
      controlType,
      colClass,
      formItemClass,
      onCheckboxChange,
      next,
      prev,
      errorMsg,
      nextStepName,
      hasPrev,
      defaultCountry,
      formRoot,
      ICON_ERROR,
      ICON_ARROW_LEFT,
      ICON_ARROW_RIGHT,
    };
  },
  template: `
    <div class="bpa-front-default-card">
      <!-- Per-field inline errors are sufficient feedback on this step
           (released form also relies on inline errors here, not a
           top-of-card toast). No toast banner. -->

      <div class="bpa-front-dc--body">
        <div class="bpa-front-module-container bpa-front-module--basic-details">
          <div class="bpa-front-module-heading" role="heading" aria-level="2" tabindex="-1" data-bp-step-heading>{{ state.strings.basic_details_step_name }}</div>

          <div class="bpa-front-module--bd-form" ref="formRoot">
            <!-- Generic add-on seam: a Pro feature can render its own field grid
                 (containers / repeater / extra field types) here and suppress
                 Lite's default grid by setting config.suppressDefaultBasicFields.
                 On a Lite-only render this slot stays empty and the default grid
                 below renders exactly as before. -->
            <div class="bp-v3-slot" data-bp-v3-slot="basic-details-step:fields" :data-bp-v3-instance="state.instanceId"></div>
            <div v-if="!(state.config && state.config.suppressDefaultBasicFields)" class="bpa-bd-fields-row">
              <div
                v-for="field in visibleFields"
                :key="field.fieldId || field.vModelValue"
                :class="colClass(field)"
              >
                <div :class="formItemClass(field)" :data-bp-field="field.vModelValue">
                  <!-- Terms & Conditions: match legacy <bp-ui-checkbox> (Element-Plus
                       el-checkbox) DOM verbatim so bookingpress_front.css /
                       bookingpress_element_theme.css rules keyed off
                       \`.bpa-front-form-control--checkbox.el-checkbox\` apply (20×20 box,
                       4px radius, SVG-mask tick, green-on-checked, etc.). The outer
                       <label> itself is the el-checkbox container and carries
                       \`is-checked\`; <input> precedes the visual \`.el-checkbox__inner\`
                       span, matching the order Element-Plus emits. -->
                  <template v-if="controlType(field) === 'terms'">
                    <label
                      class="bp-form-item__content el-checkbox bp-ui-checkbox bp-checkbox bpa-checkbox bpa-front-form-control--checkbox"
                      :class="{ 'is-checked': Array.isArray(state.appointment_step_form_data[field.vModelValue]) && state.appointment_step_form_data[field.vModelValue].includes(true) }"
                    >
                      <span
                        class="el-checkbox__input"
                        :class="{
                          'is-checked': Array.isArray(state.appointment_step_form_data[field.vModelValue]) && state.appointment_step_form_data[field.vModelValue].includes(true),
                          'is-focus': !!focused[field.vModelValue]
                        }"
                      >
                        <input
                          type="checkbox"
                          class="el-checkbox__original"
                          :id="fieldId(field)"
                          :checked="Array.isArray(state.appointment_step_form_data[field.vModelValue]) && state.appointment_step_form_data[field.vModelValue].includes(true)"
                          :aria-required="field.fieldRequired ? 'true' : null"
                          :aria-invalid="errorFor(field) ? 'true' : null"
                          :aria-describedby="errorFor(field) ? errorId(field) : null"
                          @change="onCheckboxChange(field, $event)"
                          @focus="setFocused(field.vModelValue, true)"
                          @blur="setFocused(field.vModelValue, false)"
                        />
                        <span class="el-checkbox__inner"></span>
                      </span>
                      <span class="el-checkbox__label">
                        <span v-html="field.fieldLabel"></span>
                        <span v-if="field.fieldRequired" class="bpa-front-required-asterisk" aria-hidden="true">&nbsp;*</span>
                      </span>
                    </label>
                  </template>

                  <template v-else>
                    <label class="bp-form-item__label" :for="fieldId(field)">
                      <span class="bpa-front-form-label">{{ field.fieldLabel }}<span v-if="field.fieldRequired" class="bpa-front-required-asterisk" aria-hidden="true">&nbsp;*</span></span>
                    </label>
                    <div class="bp-form-item__content">
                      <!-- Phone field uses the BookingPressUI tel input,
                           which wraps vue-tel-input. We deliberately do
                           not pass a static placeholder so the
                           component's built-in country-formatted
                           example wins (matches the released form). -->
                      <bp-ui-tel-input
                        v-if="controlType(field) === 'tel'"
                        class="bpa-front-form-control --bpa-country-dropdown"
                        :model-value="value(field.vModelValue) || ''"
                        :default-country="defaultCountry"
                        :input-options="telInputOptions(field)"
                        :dropdown-options="{ showFlags: true, showSearchBox: false, showDialCodeInSelection: false, showDialCodeInList: true }"
                        mode="international"
                        maxlength="63"
                        @update:model-value="setValue(field.vModelValue, $event)"
                        @blur="markTouched(field.vModelValue)"
                      />
                      <!-- Textarea uses the same .bp-input__wrapper +
                           is-focus pattern as text/email inputs so the
                           focus ring comes from booking-form.css's
                           wrapper rule (neutral grey shadow), NOT from
                           the customize generator's primary-colored
                           :focus rule that targets
                           \`textarea.bpa-front-form-control:focus\`. The
                           inner <textarea> uses .bp-textarea__inner for
                           sizing + font; chrome (border, bg, focus
                           shadow) is owned by the wrapper. -->
                      <div
                        v-else-if="controlType(field) === 'textarea'"
                        class="bp-input__wrapper bp-input__wrapper--textarea"
                        @focusin="$event.currentTarget.classList.add('is-focus')"
                        @focusout="$event.currentTarget.classList.remove('is-focus')"
                      >
                        <textarea
                          class="bp-textarea__inner"
                          :id="fieldId(field)"
                          :placeholder="field.fieldPlaceholder"
                          :value="value(field.vModelValue) || ''"
                          :aria-required="field.fieldRequired ? 'true' : null"
                          :aria-invalid="errorFor(field) ? 'true' : null"
                          :aria-describedby="errorFor(field) ? errorId(field) : null"
                          @input="setValue(field.vModelValue, $event.target.value)"
                          @blur="markTouched(field.vModelValue)"
                        ></textarea>
                      </div>
                      <!-- Wrap text/email inputs in .bp-input__wrapper so the legacy
                           is-focus class toggle (and :focus-within) controls the focus
                           ring instead of :focus on the raw input (which the customize
                           CSS generator styles with a heavy primary-colour shadow). -->
                      <div
                        v-else
                        class="bp-input__wrapper"
                        @focusin="$event.currentTarget.classList.add('is-focus')"
                        @focusout="$event.currentTarget.classList.remove('is-focus')"
                      >
                        <input
                          class="bp-input__inner"
                          :id="fieldId(field)"
                          :type="controlType(field)"
                          :placeholder="field.fieldPlaceholder"
                          :value="value(field.vModelValue) || ''"
                          :aria-required="field.fieldRequired ? 'true' : null"
                          :aria-invalid="errorFor(field) ? 'true' : null"
                          :aria-describedby="errorFor(field) ? errorId(field) : null"
                          :maxlength="['firstname', 'lastname', 'email_address'].includes(field.fieldName) ? 255 : null"
                          @input="setValue(field.vModelValue, $event.target.value)"
                          @blur="markTouched(field.vModelValue)"
                        />
                      </div>
                    </div>
                  </template>

                  <p v-if="errorFor(field)" class="bp-form-item__error bpa-front-form-error" :id="errorId(field)" role="alert">{{ errorFor(field) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="bpa-front-dc--footer">
        <div class="bpa-front-tabs--foot">
          <!-- Back button — hidden when the (Pro-reordered) schema makes this
               the first step (no previous_step). Lite's default keeps it third,
               so it shows as before. -->
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
