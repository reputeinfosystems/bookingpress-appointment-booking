<?php
/**
 * ServiceCatalogService — services/categories list + preselection precedence.
 *
 * Per §M0.1 (preselection chain) and §M0.2 (default-category logic).
 *
 * @package BookingPress\Vue3\Services
 */

namespace BookingPress\Vue3\Services;

use BookingPress\Vue3\Contracts\ServiceCatalogServiceInterface;
use BookingPress\Vue3\Hooks;
use BookingPress\Vue3\Repositories\CategoryRepository;
use BookingPress\Vue3\Repositories\CustomizeRepository;
use BookingPress\Vue3\Repositories\ServiceRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ServiceCatalogService implements ServiceCatalogServiceInterface {

	/** @var ServiceRepository */
	private $services;
	/** @var CategoryRepository */
	private $categories;
	/** @var CustomizeRepository */
	private $customize;

	public function __construct(
		?ServiceRepository $services = null,
		?CategoryRepository $categories = null,
		?CustomizeRepository $customize = null
	) {
		$this->services   = $services ?: new ServiceRepository();
		$this->categories = $categories ?: new CategoryRepository();
		$this->customize  = $customize ?: new CustomizeRepository();
	}

	/**
	 * @inheritDoc
	 */
	public function get_services( array $context = array() ) {
		$rows = $this->services->get_all( $context );

		// Apply the optional `service_csv` shortcode filter — when present,
		// keep only services whose id appears in the CSV. The full row
		// payload (including the row referenced by `selected_service`) is
		// retained so the preselection seed can still resolve.
		if ( isset( $context['service_csv'] ) && '' !== (string) $context['service_csv'] ) {
			$allowed = array_filter( array_map( 'intval', explode( ',', (string) $context['service_csv'] ) ) );
			if ( ! empty( $allowed ) ) {
				$selected_service = isset( $context['selected_service'] ) ? (int) $context['selected_service'] : 0;
				$rows = array_values( array_filter( $rows, static function ( $row ) use ( $allowed, $selected_service ) {
					$sid = isset( $row['serviceId'] ) ? (int) $row['serviceId'] : 0;
					return in_array( $sid, $allowed, true ) || $sid === $selected_service;
				} ) );
			}
		}

		if ( isset( $context['category_csv'] ) && '' !== (string) $context['category_csv'] ) {
			$allowed = array_filter( array_map( 'intval', explode( ',', (string) $context['category_csv'] ) ) );
			if ( ! empty( $allowed ) ) {
				//selected_category = isset( $context['selected_category'] ) ? (int) $context['selected_category'] : 0;
				$rows = array_values( array_filter( $rows, static function ( $row ) use ( $allowed, $selected_category ) {
					$sid = isset( $row['categoryId'] ) ? (int) $row['categoryId'] : 0;
					return in_array( $sid, $allowed, true );
				} ) );
			}
		}

		// Optional category narrowing.
		/* if ( isset( $context['category_id'] ) && (int) $context['category_id'] > 0 ) {
			$cid  = (int) $context['category_id'];
			$rows = array_values( array_filter( $rows, static function ( $row ) use ( $cid ) {
				return isset( $row['categoryId'] ) && (int) $row['categoryId'] === $cid;
			} ) );
		} */

		return $rows;
	}

	/**
	 * @inheritDoc
	 */
	public function get_categories( array $services ) {
		$rows = $this->categories->get_all();

		// Legacy parity (core/classes/class.bookingpress.php:7736 —
		// bookingpress_retrieve_all_categories): only categories that are
		// referenced by at least one service appear in the picker. Build the
		// reference set and the `$has_uncategorized` flag from `$services` in
		// one pass, then filter `$rows` to that set.
		$cat_ids_with_services = array();
		$has_uncategorized     = false;
		foreach ( $services as $s ) {
			$cid = isset( $s['categoryId'] ) ? (int) $s['categoryId'] : 0;
			if ( 0 === $cid ) {
				$has_uncategorized = true;
			} else {
				$cat_ids_with_services[ $cid ] = true;
			}
		}
		$rows = array_values( array_filter( $rows, static function ( $r ) use ( $cat_ids_with_services ) {
			$cid = isset( $r['categoryId'] ) ? (int) $r['categoryId'] : 0;
			return isset( $cat_ids_with_services[ $cid ] );
		} ) );
		$real_count = count( $rows );

		// Released-form lite rules:
		//   1. Zero categories  → render only the "All" pill (one pill).
		//   2. One real category, every service categorized into it →
		//      no functional filter, hide the section entirely (return []).
		//   3. One real category + some uncategorized → "All" + named.
		//   4. Two or more real categories → "All" + named (in admin order).
		//
		// The "default-selected" pill is decided in `resolve_default_category()`
		// — this function only emits the visible pill set.

		// Case 2 — hide entirely. Frontend renders nothing when this is [].
		if ( 1 === $real_count && ! $has_uncategorized ) {
			return array();
		}

		// Decide whether to prepend the "All" pseudo-row.
		$need_all_pseudo = false;
		if ( 0 === $real_count ) {
			// Case 1 — only meaningful if there are services to filter at all.
			$need_all_pseudo = ! empty( $services );
		} elseif ( 1 === $real_count && $has_uncategorized ) {
			$need_all_pseudo = true;  // Case 3.
		} elseif ( $real_count > 1 ) {
			$need_all_pseudo = true;  // Case 4.
		}

		if ( $need_all_pseudo ) {
			$strings = $this->customize->get_many(
				CustomizeRepository::GROUP_BOOKING_FORM,
				array( 'all_category_title' )
			);
			$all_label = '' !== $strings['all_category_title'] ? $strings['all_category_title'] : 'All';
			array_unshift( $rows, array(
				'categoryId'       => 0,
				'categoryName'     => $all_label,
				'categoryPosition' => 0,
			) );
		}

		return $rows;
	}

	/**
	 * @inheritDoc
	 */
	public function resolve_preselection( array $atts ) {
		$service          = '';
		//$category         = isset( $atts['category'] ) ? (int) $atts['category'] : 0;

		$category = '';
		if( !empty( $atts['category'] ) ) {
			$csv = array_filter( array_map( 'intval', explode( ',', (string) $atts['category'] ) ) );
			if ( ! empty( $csv ) ) {
				$category = implode( ',', $csv );
			}
		}
		$selected_service = isset( $atts['selected_service'] ) ? (int) $atts['selected_service'] : 0;
		$is_from_url      = 0;

		if ( ! empty( $atts['service'] ) ) {
			$csv = array_filter( array_map( 'intval', explode( ',', (string) $atts['service'] ) ) );
			if ( ! empty( $csv ) ) {
				$service = implode( ',', $csv );
			}
		}

		// Per §M0.1: URL params take precedence over shortcode attrs.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$bpservice_id = isset( $_GET['bpservice_id'] ) ? (int) wp_unslash( $_GET['bpservice_id'] ) : 0;
		$s_id         = isset( $_GET['s_id'] ) ? (int) wp_unslash( $_GET['s_id'] ) : 0;
		// Share-URL "allow modify" flag (legacy parity:
		// class.bookingpress_appointment_bookings.php:5530-5536). Only
		// meaningful alongside a positive `s_id`, and only the literal `'0'`
		// / `'1'` values are honoured (anything else leaves it unset, exactly
		// like the two legacy branches). `null` means "not a share-URL load"
		// so downstream can tell it apart from an explicit 0.
		$allow_modify = null;
		if ( ! empty( $s_id ) && isset( $_GET['allow_modify'] ) ) {
			$raw_allow_modify = (string) wp_unslash( $_GET['allow_modify'] );
			if ( '0' === $raw_allow_modify ) {
				$allow_modify = 0;
			} elseif ( '1' === $raw_allow_modify ) {
				$allow_modify = 1;
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( ! empty( $bpservice_id ) && empty( $s_id ) ) {
			$selected_service = $bpservice_id;
			$is_from_url      = 1;
		} elseif ( ! empty( $s_id ) ) {
			$selected_service = $s_id;
			$service          = '';
			$category         = '';
			$is_from_url      = 1;
		}

		// Validate selected_service exists in the catalog. Silent fallback
		// to "no preselection" if it doesn't — matches the §M0.1 edge case.
		if ( $selected_service > 0 && null === $this->services->find( $selected_service ) ) {
			$selected_service = 0;
			$is_from_url      = 0;
		}

		$resolution = array(
			'service'          => $service,
			'category'         => $category,
			'selected_service' => $selected_service,
			'is_from_url'      => $is_from_url,
			// 0 | 1 | null (null => not a share-URL load). Consumed by
			// StateBuilder to force-hide / force-show the Service step.
			'allow_modify'     => $allow_modify,
		);

		/**
		 * Confirm / mutate the resolved selection (post-validate).
		 *
		 * @param bool  $is_valid    Whether the resolved selection is internally consistent.
		 * @param array $resolution  The four-key resolution shape.
		 * @param array $context     The shortcode attrs.
		 */
		apply_filters( Hooks::FILTER_CHECK_SELECTED_SERVICE, true, $resolution, $atts );

		return $resolution;
	}

	/**
	 * @inheritDoc
	 */
	public function resolve_default_category( array $categories, array $settings, array $preselection_seed ) {
		// Preselection already populated selected_category → leave it alone.
		if ( ! empty( $preselection_seed['selected_category'] ) ) {
			return array();
		}

		$out = array();

		if ( empty( $categories ) ) {
			return $out;
		}

		// Count real (non-pseudo) categories.
		$real = array_values( array_filter( $categories, static function ( $c ) {
			return ! empty( $c['categoryId'] );
		} ) );

		if ( 1 === count( $real ) ) {
			$out['selected_category'] = (string) $real[0]['categoryId'];
			$out['selected_cat_name'] = isset( $real[0]['categoryName'] ) ? (string) $real[0]['categoryName'] : '';
		} elseif ( count( $real ) > 1 ) {
			$out['selected_category'] = (string) $real[0]['categoryId'];
			$out['selected_cat_name'] = isset( $real[0]['categoryName'] ) ? (string) $real[0]['categoryName'] : '';
		}

		// Customize override: default_select_all_category === 'true' AND
		// hide_category_service_selection !== 'true'.
		$default_all = isset( $settings['default_select_all_category'] ) && 'true' === $settings['default_select_all_category'];
		$hidden      = isset( $settings['hide_category_service_selection'] ) && 'true' === $settings['hide_category_service_selection'];
		if ( $default_all && ! $hidden ) {
			$strings = $this->customize->get_many(
				CustomizeRepository::GROUP_BOOKING_FORM,
				array( 'all_category_title' )
			);
			$out['selected_category'] = '0';
			$out['selected_cat_name'] = '' !== $strings['all_category_title'] ? $strings['all_category_title'] : 'All';
		}

		return $out;
	}
}
