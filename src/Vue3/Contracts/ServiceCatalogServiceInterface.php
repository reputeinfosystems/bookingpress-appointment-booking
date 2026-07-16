<?php
/**
 * ServiceCatalogServiceInterface — services + categories + preselection logic.
 *
 * @package BookingPress\Vue3\Contracts
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.1, §M0.2
 */

namespace BookingPress\Vue3\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Owns category/service discovery and the preselection precedence chain.
 *
 * Pro extends via the `bookingpress_form_v3_services` and
 * `bookingpress_form_v3_categories` filters. The implementation runs both
 * filters internally; consumers receive the post-filter shape.
 */
interface ServiceCatalogServiceInterface {

	/**
	 * Return the services list for the current form instance.
	 *
	 * The list respects the `service` CSV shortcode attribute filter and
	 * any Pro filter callbacks.
	 *
	 * @param array $context Optional:
	 *                       - `service_csv`      string `service` shortcode attr.
	 *                       - `selected_service` int    Forced-include hint for Pro.
	 *                       - `category_id`      int    Filter to one category.
	 *
	 * @return array<int|string, array> Service rows keyed by id, ordered for display.
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.1
	 */
	public function get_services( array $context = array() );

	/**
	 * Return the categories list, augmented with the "All" pseudo-row when needed.
	 *
	 * @param array $services Output of {@see get_services()}.
	 *
	 * @return array<int, array> Category rows (may contain a leading `category_id === 0` pseudo-row).
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.1, §M0.2
	 */
	public function get_categories( array $services );

	/**
	 * Resolve the preselection precedence chain (URL params + shortcode attrs).
	 *
	 * @param array $atts Sanitized shortcode attributes.
	 *
	 * @return array{ service: string, category: int, selected_service: int, is_from_url: int, allow_modify: int|null }
	 *         `allow_modify` is 0|1 on a share-URL load (`s_id` + `allow_modify`),
	 *         or null otherwise.
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.1
	 */
	public function resolve_preselection( array $atts );

	/**
	 * Pick the initial category when no service has been preselected.
	 *
	 * @param array $categories       Output of {@see get_categories()}.
	 * @param array $settings         Map of customize settings (see §M0.2).
	 * @param array $preselection_seed The current `selected_category` seed (may be empty).
	 *
	 * @return array{ selected_category?: string, selected_cat_name?: string }
	 *         Empty array when no default applies.
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.2
	 */
	public function resolve_default_category( array $categories, array $settings, array $preselection_seed );
}
