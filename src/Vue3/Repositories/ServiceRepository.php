<?php
/**
 * ServiceRepository — reads `bookingpress_services` + `bookingpress_servicesmeta`.
 *
 * Fires `bookingpress_form_v3_services` as the proof-of-extension-point filter
 * (per the M2 deliverables in `BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md`).
 * Pro 6.0+ uses this filter to inject staff-aware service rows or to scope
 * the list by category.
 *
 * @package BookingPress\Vue3\Repositories
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.1
 */

namespace BookingPress\Vue3\Repositories;

use BookingPress\Vue3\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ServiceRepository extends BaseRepository {

	/**
	 * Servicesmeta column-introspection cache (per request).
	 *
	 * @var array<string, true>|null
	 */
	private $meta_columns = null;

	/**
	 * @inheritDoc
	 */
	protected function table_suffix() {
		return 'bookingpress_services';
	}

	/**
	 * Servicemeta table (fully qualified).
	 *
	 * @return string
	 */
	private function meta_table() {
		global $wpdb;
		return $wpdb->prefix . 'bookingpress_servicesmeta';
	}

	/**
	 * Return all services, ordered by position then id, normalized.
	 *
	 * Output shape (camelCase keys, predictable types):
	 *   array<int, array{
	 *     serviceId:           int,
	 *     categoryId:          int,
	 *     serviceName:         string,
	 *     servicePrice:        float,
	 *     serviceDurationVal:  int,
	 *     serviceDurationUnit: string,    // 'm' | 'h' | 'd'
	 *     serviceDescription:  string,
	 *     servicePosition:     int,
	 *     colorMode:           string,    // 'preset' | 'custom'
	 *     colorScheme:         string|null,
	 *     meta:                array      // keyed by servicemeta_name → value
	 *   }>
	 *
	 * Cached under `bp_v3_services_v<N>_all` (where `<N>` is the current
	 * FrontendFormCache version). The
	 * {@see Hooks::FILTER_SERVICES} filter runs **after** cache fetch so
	 * Pro can layer per-request decoration without invalidating Lite's
	 * persistent cache.
	 *
	 * @param array $context Optional context passed to the filter (e.g.
	 *                       `service_csv`, `selected_service`, `category_id`).
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_all( array $context = array() ) {
		$rows = $this->remember( 'all', function () {
			global $wpdb;

			$table  = $this->table();
			$result = $wpdb->get_results(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM `{$table}` ORDER BY bookingpress_service_position ASC, bookingpress_service_id ASC",
				ARRAY_A
			);

			$rows = array();
			if ( is_array( $result ) ) {
				foreach ( $result as $r ) {
					$rows[] = $this->normalize_service_row( $r );
				}
			}
			return $rows;
		} );

		// Eager-load meta (cheap — single query for all services).
		$rows = $this->attach_meta_to_rows( $rows );

		/**
		 * Filter the services payload before it leaves the repository.
		 *
		 * @param array $rows    Normalized service rows.
		 * @param array $context Optional context bag from the caller.
		 */
		$filtered = apply_filters( Hooks::FILTER_SERVICES, $rows, $context );

		return is_array( $filtered ) ? $filtered : $rows;
	}

	/**
	 * Return a single normalized service row by id, or null.
	 *
	 * @param int $service_id
	 *
	 * @return array<string, mixed>|null
	 */
	public function find( $service_id ) {
		$service_id = (int) $service_id;
		if ( $service_id <= 0 ) {
			return null;
		}

		$row = $this->remember( 'by_id_' . $service_id, function () use ( $service_id ) {
			global $wpdb;
			$table = $this->table();
			$raw   = $wpdb->get_row(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT * FROM `{$table}` WHERE bookingpress_service_id = %d LIMIT 1",
					$service_id
				),
				ARRAY_A
			);
			return is_array( $raw ) ? $this->normalize_service_row( $raw ) : null;
		} );

		if ( null === $row ) {
			return null;
		}
		$rows = $this->attach_meta_to_rows( array( $row ) );
		return $rows[0];
	}

	/**
	 * Return all meta rows for a single service id.
	 *
	 * @param int $service_id
	 *
	 * @return array<string, mixed> name → value map.
	 */
	public function get_meta( $service_id ) {
		$service_id = (int) $service_id;
		if ( $service_id <= 0 ) {
			return array();
		}
		return $this->remember( 'meta_' . $service_id, function () use ( $service_id ) {
			global $wpdb;
			$meta_table = $this->meta_table();
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT bookingpress_servicemeta_name AS name, bookingpress_servicemeta_value AS value FROM `{$meta_table}` WHERE bookingpress_service_id = %d",
					$service_id
				),
				ARRAY_A
			);

			$out = array();
			if ( is_array( $rows ) ) {
				foreach ( $rows as $r ) {
					$out[ (string) $r['name'] ] = $r['value'];
				}
			}
			return $out;
		} );
	}

	/**
	 * Normalize one raw `bookingpress_services` row.
	 *
	 * @param array $row Raw row from SELECT *.
	 *
	 * @return array<string, mixed>
	 */
	private function normalize_service_row( array $row ) {
		return array(
			'serviceId'           => (int) $this->pluck( $row, 'bookingpress_service_id', 0 ),
			'categoryId'          => (int) $this->pluck( $row, 'bookingpress_category_id', 0 ),
			'serviceName'         => $this->pluck_text( $row, 'bookingpress_service_name', '' ),
			'servicePrice'        => (float) $this->pluck( $row, 'bookingpress_service_price', 0 ),
			'serviceDurationVal'  => (int) $this->pluck( $row, 'bookingpress_service_duration_val', 0 ),
			'serviceDurationUnit' => (string) $this->pluck( $row, 'bookingpress_service_duration_unit', 'm' ),
			'serviceDescription'  => $this->pluck_text( $row, 'bookingpress_service_description', '' ),
			'servicePosition'     => (int) $this->pluck( $row, 'bookingpress_service_position', 0 ),
			'colorMode'           => (string) $this->pluck( $row, 'bookingpress_color_mode', 'preset' ),
			'colorScheme'         => $this->pluck( $row, 'bookingpress_color_scheme', null ),
			'avatarUrl'           => '',
			'meta'                => array(),
		);
	}

	/**
	 * Bulk-load servicesmeta for the given normalized rows and attach to each.
	 *
	 * One query covers all rows.
	 *
	 * @param array<int, array<string, mixed>> $rows
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function attach_meta_to_rows( array $rows ) {
		if ( empty( $rows ) ) {
			return $rows;
		}

		$ids = array_filter( array_map( static function ( $r ) {
			return isset( $r['serviceId'] ) ? (int) $r['serviceId'] : 0;
		}, $rows ) );
		if ( empty( $ids ) ) {
			return $rows;
		}

		$cache_key = 'meta_bulk_' . md5( implode( ',', $ids ) );
		$meta_map  = $this->remember( $cache_key, function () use ( $ids ) {
			global $wpdb;
			$meta_table   = $this->meta_table();
			$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

			$results = $wpdb->get_results(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT bookingpress_service_id AS sid, bookingpress_servicemeta_name AS name, bookingpress_servicemeta_value AS value FROM `{$meta_table}` WHERE bookingpress_service_id IN ({$placeholders})",
					...$ids
				),
				ARRAY_A
			);

			$out = array();
			if ( is_array( $results ) ) {
				foreach ( $results as $r ) {
					$sid = (int) $r['sid'];
					if ( ! isset( $out[ $sid ] ) ) {
						$out[ $sid ] = array();
					}
					$out[ $sid ][ (string) $r['name'] ] = $r['value'];
				}
			}
			return $out;
		} );

		foreach ( $rows as &$row ) {
			$sid          = isset( $row['serviceId'] ) ? (int) $row['serviceId'] : 0;
			$row['meta']  = isset( $meta_map[ $sid ] ) ? $meta_map[ $sid ] : array();
			if ( ! empty( $row['meta']['service_image_details'] ) ) {
				$img_meta = $row['meta']['service_image_details'];
				if ( preg_match( '/s:3:"url";s:\d+:"([^"]+)";/', $img_meta, $matches ) ) {
					$row['avatarUrl'] = (string) $matches[1];
				}
			}
		}
		unset( $row );

		return $rows;
	}
}
