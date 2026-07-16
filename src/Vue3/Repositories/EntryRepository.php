<?php
/**
 * EntryRepository — stage-1 write target (`bookingpress_entries`).
 *
 * Per the §M0.10 2-step contract:
 * - `SubmissionService::submit()` (on-site / zero-price) and
 *   `PaymentService::paypal_validate()` (PayPal) insert one row here.
 * - `SubmissionService::finalize_booking()` reads the row and may mark it
 *   processed after stage 2 completes.
 *
 * @package BookingPress\Vue3\Repositories
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.10
 */

namespace BookingPress\Vue3\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EntryRepository extends BaseRepository {

	/** Pending entry — payment not yet confirmed. */
	const STATUS_PENDING   = 2;
	const STATUS_APPROVED  = 1;
	const STATUS_CANCELED  = 3;

	/**
	 * @inheritDoc
	 */
	protected function table_suffix() {
		return 'bookingpress_entries';
	}

	/**
	 * @inheritDoc
	 */
	protected function cache_namespace() {
		return 'entries';
	}

	/**
	 * Insert a stage-1 pending entry.
	 *
	 * Defensively filters the input to columns that actually exist on this
	 * install (the `bookingpress_entries` table has had columns added by
	 * upgrade routines over time; older installs may lack newer columns).
	 *
	 * @param array $data Map of `bookingpress_*` column → value.
	 *
	 * @return int The new entry id, or 0 on failure.
	 */
	public function insert_pending( array $data ) {
		global $wpdb;

		// Always set status to Pending unless caller overrides.
		if ( ! isset( $data['bookingpress_appointment_status'] ) ) {
			$data['bookingpress_appointment_status'] = self::STATUS_PENDING;
		}
		if ( ! isset( $data['bookingpress_created_at'] ) ) {
			$data['bookingpress_created_at'] = current_time( 'mysql' );
		}

		$data = $this->filter_to_existing_columns( $data );
		if ( empty( $data ) ) {
			return 0;
		}

		$ok = $wpdb->insert( $this->table(), $data );
		if ( false === $ok ) {
			return 0;
		}
		return (int) $wpdb->insert_id;
	}

	/**
	 * Read one entry by id.
	 *
	 * Bypasses cache because entries are short-lived staging rows that the
	 * confirm step needs the latest copy of. (We intentionally don't cache
	 * here.)
	 *
	 * @param int $entry_id
	 *
	 * @return array<string, mixed>|null Raw row keyed by column name, or null.
	 */
	public function find( $entry_id ) {
		$entry_id = (int) $entry_id;
		if ( $entry_id <= 0 ) {
			return null;
		}
		global $wpdb;
		$table = $this->table();
		$raw   = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM `{$table}` WHERE bookingpress_entry_id = %d LIMIT 1",
				$entry_id
			),
			ARRAY_A
		);
		return is_array( $raw ) ? $raw : null;
	}

	/**
	 * Find every entry of a multi-appointment ORDER, in insert order (the primary
	 * entry — inserted first — comes first). Used by the shared-payment finalize
	 * path to materialise one booking per entry.
	 *
	 * Returns an empty array when the order id is empty or the
	 * `bookingpress_order_id` column does not exist (a Lite-only install), so the
	 * caller falls back to the single entry — keeping Lite single-booking.
	 *
	 * @param int $order_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function find_by_order( $order_id ) {
		$order_id = (int) $order_id;
		if ( $order_id <= 0 || ! $this->has_column( 'bookingpress_order_id' ) ) {
			return array();
		}
		global $wpdb;
		$table = $this->table();
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM `{$table}` WHERE bookingpress_order_id = %d ORDER BY bookingpress_entry_id ASC",
				$order_id
			),
			ARRAY_A
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Mark an entry as processed (stage-2 has materialised the booking).
	 *
	 * Updates the status column. Failure to mark processed is non-fatal —
	 * the legacy `bookingpress_clear_pending_entries` cron prunes stragglers.
	 *
	 * @param int    $entry_id
	 * @param int	 $status One of the STATUS_* constants.
	 *
	 * @return bool
	 */
	public function mark_processed( $entry_id, $status = self::STATUS_APPROVED ) {
		$entry_id = (int) $entry_id;
		if ( $entry_id <= 0 ) {
			return false;
		}
		global $wpdb;
		$ok = $wpdb->update(
			$this->table(),
			array( 'bookingpress_appointment_status' => (int) $status ),
			array( 'bookingpress_entry_id' => $entry_id ),
			array( '%d' ),
			array( '%d' )
		);
		return false !== $ok;
	}

	/**
	 * Drop columns from $data that don't exist on this install's table.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function filter_to_existing_columns( array $data ) {
		$out = array();
		foreach ( $data as $col => $val ) {
			if ( $this->has_column( $col ) ) {
				$out[ $col ] = $val;
			}
		}
		return $out;
	}
}
