<?php
/**
 * PaymentTransactionRepository — `bookingpress_payment_transactions` (legacy
 * `tbl_bookingpress_payment_logs`).
 *
 * Inserted at stage 2 of the §M0.10 2-step contract; updated by
 * `PaymentService::paypal_confirm` when the capture resolves.
 *
 * @package BookingPress\Vue3\Repositories
 */

namespace BookingPress\Vue3\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PaymentTransactionRepository extends BaseRepository {

	// Payment-status values (the canonical map lives in Pro
	// class.bookingpress_pro_global_options.php; Lite's own global options only
	// expose 1 + 2, the rest are Pro concepts but stored in the same column).
	const STATUS_PAID               = 1;
	const STATUS_PENDING            = 2;
	const STATUS_REFUNDED           = 3;
	const STATUS_PARTIALLY_PAID     = 4;
	const STATUS_PARTIALLY_REFUNDED = 5;

	/**
	 * @inheritDoc
	 */
	protected function table_suffix() {
		return 'bookingpress_payment_transactions';
	}

	/**
	 * @inheritDoc
	 */
	protected function cache_namespace() {
		return 'payments';
	}

	/**
	 * Insert a new payment row.
	 *
	 * @param array $data Map of column → value.
	 *
	 * @return int New payment id, or 0 on failure.
	 */
	public function insert( array $data ) {
		global $wpdb;

		if ( ! isset( $data['bookingpress_created_at'] ) ) {
			$data['bookingpress_created_at'] = current_time( 'mysql' );
		}
		if ( ! isset( $data['bookingpress_payment_date_time'] ) ) {
			$data['bookingpress_payment_date_time'] = current_time( 'mysql' );
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
	 * Update an existing payment row's status (used by `paypal_confirm`).
	 *
	 * @param int                 $payment_id
	 * @param int                 $status     One of the STATUS_* constants.
	 * @param array<string,mixed> $extra      Additional columns to update
	 *                                        (e.g. `bookingpress_transaction_id`,
	 *                                        `bookingpress_payment_response`).
	 *
	 * @return bool
	 */
	public function update_status( $payment_id, $status, array $extra = array() ) {
		$payment_id = (int) $payment_id;
		if ( $payment_id <= 0 ) {
			return false;
		}
		global $wpdb;
		$update = array_merge(
			array( 'bookingpress_payment_status' => (int) $status ),
			$extra
		);
		$update = $this->filter_to_existing_columns( $update );

		$ok = $wpdb->update(
			$this->table(),
			$update,
			array( 'bookingpress_payment_log_id' => $payment_id )
		);
		return false !== $ok;
	}

	/**
	 * Read one payment row by id.
	 *
	 * @param int $payment_id
	 *
	 * @return array<string, mixed>|null
	 */
	public function find( $payment_id ) {
		$payment_id = (int) $payment_id;
		if ( $payment_id <= 0 ) {
			return null;
		}
		global $wpdb;
		$table = $this->table();
		$raw   = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM `{$table}` WHERE bookingpress_payment_log_id = %d LIMIT 1",
				$payment_id
			),
			ARRAY_A
		);
		return is_array( $raw ) ? $raw : null;
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
