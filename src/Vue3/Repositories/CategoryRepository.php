<?php
/**
 * CategoryRepository — reads `bookingpress_categories`.
 *
 * @package BookingPress\Vue3\Repositories
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.2
 */

namespace BookingPress\Vue3\Repositories;

use BookingPress\Vue3\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CategoryRepository extends BaseRepository {

	/**
	 * @inheritDoc
	 */
	protected function table_suffix() {
		return 'bookingpress_categories';
	}

	/**
	 * Return all categories, ordered by position then id, normalized.
	 *
	 * Output shape:
	 *   array<int, array{
	 *     categoryId:       int,
	 *     categoryName:     string,
	 *     categoryPosition: int
	 *   }>
	 *
	 * **The "All" pseudo-row with `categoryId === 0` is NOT injected here.**
	 * §M0.2 places that responsibility in `ServiceCatalogService` (M3) so the
	 * repository stays a pure data-access layer.
	 *
	 * Fires `bookingpress_form_v3_categories` post-fetch.
	 *
	 * @param array $context Optional context for the filter.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_all( array $context = array() ) {
		$rows = $this->remember( 'all', function () {
			global $wpdb;
			$table = $this->table();
			$raw   = $wpdb->get_results(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM `{$table}` ORDER BY bookingpress_category_position ASC, bookingpress_category_id ASC",
				ARRAY_A
			);

			$out = array();
			if ( is_array( $raw ) ) {
				foreach ( $raw as $r ) {
					$out[] = $this->normalize_row( $r );
				}
			}
			return $out;
		} );

		/**
		 * Filter the categories payload before it leaves the repository.
		 *
		 * @param array $rows    Normalized category rows.
		 * @param array $context Optional context bag from the caller.
		 */
		$filtered = apply_filters( Hooks::FILTER_CATEGORIES, $rows, $context );
		return is_array( $filtered ) ? $filtered : $rows;
	}

	/**
	 * Return a single normalized category by id, or null.
	 *
	 * @param int $category_id
	 *
	 * @return array<string, mixed>|null
	 */
	public function find( $category_id ) {
		$category_id = (int) $category_id;
		if ( $category_id <= 0 ) {
			return null;
		}
		return $this->remember( 'by_id_' . $category_id, function () use ( $category_id ) {
			global $wpdb;
			$table = $this->table();
			$raw   = $wpdb->get_row(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT * FROM `{$table}` WHERE bookingpress_category_id = %d LIMIT 1",
					$category_id
				),
				ARRAY_A
			);
			return is_array( $raw ) ? $this->normalize_row( $raw ) : null;
		} );
	}

	/**
	 * @param array $row Raw row from SELECT *.
	 *
	 * @return array<string, mixed>
	 */
	private function normalize_row( array $row ) {
		return array(
			'categoryId'       => (int) $this->pluck( $row, 'bookingpress_category_id', 0 ),
			'categoryName'     => $this->pluck_text( $row, 'bookingpress_category_name', '' ),
			'categoryPosition' => (int) $this->pluck( $row, 'bookingpress_category_position', 0 ),
		);
	}
}
