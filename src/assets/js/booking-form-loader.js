/**
 * BookingPress — Vue 3 booking form loader (entry module).
 *
 * Script module handle: `bookingpress-form-vue3-loader`
 * Declared dependencies (via wp_register_script_module):
 *   - `bookingpress-form-vue3` (provides the app factory)
 *   - `bookingpress-ui`
 *
 * Responsibilities:
 *   1. Read the per-page script-module data island emitted by
 *      BookingPress\frontend\BookingForm::filter_module_data().
 *   2. For each `instances[instanceId]` entry, resolve its DOM
 *      container and mount an isolated Vue 3 app.
 *   3. Tolerate multiple shortcode instances on the same page and
 *      skip any already-mounted container (re-entrancy safe).
 */

"use strict";

// Bare specifier — resolved by the import map that WordPress emits from
// `wp_register_script_module('bookingpress-form-vue3', ...)`. Using a
// relative path here would fetch a second copy of the file at the wrong
// URL (no `?ver=` query), bypassing the module graph that preloaded the
// dependency and causing duplicate network requests.
import { mountBookingFormInstance } from 'bookingpress-form-vue3';

const MODULE_DATA_ID = 'bookingpress-form-vue3-loader';

/**
 * Read and parse the JSON script-module data island for the given handle.
 *
 * @param {string} moduleId Script module handle (without the `wp-script-module-data-` prefix).
 * @returns {Object}
 */
function getModuleData(moduleId) {
    const el = document.getElementById(`wp-script-module-data-${moduleId}`);

    if (!el) {
        return {};
    }

    try {
        return JSON.parse(el.textContent || '{}');
    } catch (error) {
        console.error('BookingPress form Vue3: failed to parse module data', error);
        return {};
    }
}

/**
 * Mount every shortcode instance present in the data island.
 */
function bootstrapInstances() {
    const moduleData = getModuleData(MODULE_DATA_ID);
    const instances  = (moduleData && moduleData.instances) || {};

    if (!window.BookingPressFormVue3) {
        window.BookingPressFormVue3 = {
            instances: {},
            mount: mountBookingFormInstance
        };
    }

    Object.keys(instances).forEach((instanceId) => {
        if (window.BookingPressFormVue3.instances[instanceId]) {
            return;
        }

        const mounted = mountBookingFormInstance(instances[instanceId]);

        if (mounted) {
            window.BookingPressFormVue3.instances[instanceId] = mounted;
        }
    });
}

if ('loading' === document.readyState) {
    document.addEventListener('DOMContentLoaded', bootstrapInstances, { once: true });
} else {
    bootstrapInstances();
}
