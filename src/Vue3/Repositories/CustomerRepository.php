<?php
/**
 * CustomerRepository — reads `bookingpress_customers`.
 *
 * M2 ships read-only. The write side (guest signup at submit time) lands
 * in M3 alongside `SubmissionService` so the schema for new rows is owned
 * by that pipeline.
 *
 * @package BookingPress\Vue3\Repositories
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.8 (logged-in prefill)
 */

namespace BookingPress\Vue3\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CustomerRepository extends BaseRepository {

	/**
	 * `bookingpress_user_type` value for end-customers vs admin/staff rows.
	 *
	 * Per §M0.8: logged-in prefill matches the customer row where
	 * `bookingpress_user_type = 2` (i.e. the WP user is a booking customer,
	 * not a staff member managed in admin).
	 */
	const USER_TYPE_CUSTOMER = 2;

	/**
	 * @inheritDoc
	 */
	protected function table_suffix() {
		return 'bookingpress_customers';
	}

	/**
	 * Find a customer row by WordPress user id.
	 *
	 * Returns the row only when `bookingpress_user_type = 2` (per §M0.8 the
	 * customer-row lookup) so admin/staff rows that share a WP user id are
	 * not surfaced as prefill targets.
	 *
	 * @param int $wp_user_id
	 *
	 * @return array<string, mixed>|null
	 */
	public function find_by_wp_user_id( $wp_user_id ) {
		$wp_user_id = (int) $wp_user_id;
		if ( $wp_user_id <= 0 ) {
			return null;
		}

		return $this->remember( 'by_wpuser_' . $wp_user_id, function () use ( $wp_user_id ) {
			global $wpdb;
			$table = $this->table();
			$raw   = $wpdb->get_row(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT * FROM `{$table}` WHERE bookingpress_wpuser_id = %d AND bookingpress_user_type = %d ORDER BY bookingpress_customer_id ASC LIMIT 1",
					$wp_user_id,
					self::USER_TYPE_CUSTOMER
				),
				ARRAY_A
			);
			return is_array( $raw ) ? $this->normalize_row( $raw ) : null;
		} );
	}

	/**
	 * Find a customer row by id.
	 *
	 * @param int $customer_id
	 *
	 * @return array<string, mixed>|null
	 */
	public function find( $customer_id ) {
		$customer_id = (int) $customer_id;
		if ( $customer_id <= 0 ) {
			return null;
		}
		return $this->remember( 'by_id_' . $customer_id, function () use ( $customer_id ) {
			global $wpdb;
			$table = $this->table();
			$raw   = $wpdb->get_row(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT * FROM `{$table}` WHERE bookingpress_customer_id = %d LIMIT 1",
					$customer_id
				),
				ARRAY_A
			);
			return is_array( $raw ) ? $this->normalize_row( $raw ) : null;
		} );
	}

	/**
	 * Find a customer row by email (case-insensitive).
	 *
	 * Used at submit time for guest-checkout duplicate detection. M2 exposes
	 * it as a read; the M3 SubmissionService decides whether to attach to
	 * the existing row or create a new one.
	 *
	 * @param string $email
	 *
	 * @return array<string, mixed>|null
	 */
	public function find_by_email( $email ) {
		$email = strtolower( trim( (string) $email ) );
		if ( '' === $email ) {
			return null;
		}
		return $this->remember( 'by_email_' . md5( $email ), function () use ( $email ) {
			global $wpdb;
			$table = $this->table();
			$raw   = $wpdb->get_row(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT * FROM `{$table}` WHERE LOWER(bookingpress_user_email) = %s ORDER BY bookingpress_customer_id ASC LIMIT 1",
					$email
				),
				ARRAY_A
			);
			return is_array( $raw ) ? $this->normalize_row( $raw ) : null;
		} );
	}

	/**
	 * Find an existing customer row by email or create a new guest one.
	 *
	 * Idempotent — calling twice with the same email returns the same id.
	 * Used by `SubmissionService::submit()` for the guest-checkout path
	 * (§M0.8 prefill order step 2).
	 *
	 * @param array $signup_data Required keys (others optional):
	 *                           - `userEmail`            string
	 *                           - `userFirstname`        string
	 *                           - `userLastname`         string
	 *                           - `userPhone`            string
	 *                           - `userCountryDialCode`  string
	 *                           - `userTimezone`         string
	 *
	 * @return int The customer id (existing or newly inserted).
	 */
	public function find_or_create_guest( array $signup_data ) {
		$email      = isset( $signup_data['userEmail'] ) ? strtolower( trim( (string) $signup_data['userEmail'] ) ) : '';
		$wp_user_id = isset( $signup_data['wpUserId'] ) ? (int) $signup_data['wpUserId'] : 0;

		if ( '' === $email ) {
			// Legacy parity (class.bookingpress_customers.php bookingpress_create_customer,
			// the `$bookingpress_existing_user_id` branch): when the email arrives
			// empty — the field is optional and was cleared, or its visibility is
			// Hidden — a LOGGED-IN visitor still books under their own customer
			// row, resolved by WP user id. Without this the appointment stored
			// customer_id 0 and never appeared in the customer's My Bookings.
			if ( $wp_user_id <= 0 ) {
				return 0;
			}
			$existing_wp = $this->find_by_wp_user_id( $wp_user_id );
			if ( $existing_wp && ! empty( $existing_wp['customerId'] ) ) {
				return (int) $existing_wp['customerId'];
			}
			// No customer row yet for this WP user → create one bound to it
			// (legacy inserts a customer row with the blank email in the same
			// situation). Falls through to the shared insert below.
		} else {
			$existing = $this->find_by_email( $email );
			if ( $existing && ! empty( $existing['customerId'] ) ) {
				return (int) $existing['customerId'];
			}
		}

		global $wpdb;

		$fullname = trim(
			(string) ( isset( $signup_data['userFirstname'] ) ? $signup_data['userFirstname'] : '' )
			. ' '
			. (string) ( isset( $signup_data['userLastname'] ) ? $signup_data['userLastname'] : '' )
		);
		if ( '' === $fullname ) {
			$fullname = isset( $signup_data['userName'] ) && '' !== (string) $signup_data['userName']
				? (string) $signup_data['userName']
				: $email;
		}
		if ( '' === $fullname && $wp_user_id > 0 && function_exists( 'get_userdata' ) ) {
			$wp_user = get_userdata( $wp_user_id );
			if ( $wp_user && ! empty( $wp_user->display_name ) ) {
				$fullname = (string) $wp_user->display_name;
			}
		}

		$insert = array(
			'bookingpress_wpuser_id'             => isset( $signup_data['wpUserId'] ) ? (int) $signup_data['wpUserId'] : 0,
			'bookingpress_user_login'            => '',
			'bookingpress_user_status'           => 1,
			'bookingpress_user_type'             => self::USER_TYPE_CUSTOMER,
			'bookingpress_user_name'             => (string) ( isset( $signup_data['userName'] ) ? $signup_data['userName'] : $fullname ),
			'bookingpress_user_firstname'        => (string) ( isset( $signup_data['userFirstname'] ) ? $signup_data['userFirstname'] : '' ),
			'bookingpress_user_lastname'         => (string) ( isset( $signup_data['userLastname'] ) ? $signup_data['userLastname'] : '' ),
			'bookingpress_customer_full_name'    => $fullname,
			'bookingpress_user_email'            => $email,
			'bookingpress_user_phone'            => (string) ( isset( $signup_data['userPhone'] ) ? $signup_data['userPhone'] : '' ),
			'bookingpress_user_country_dial_code' => (string) ( isset( $signup_data['userCountryDialCode'] ) ? $signup_data['userCountryDialCode'] : '' ),
			'bookingpress_user_timezone'         => (string) ( isset( $signup_data['userTimezone'] ) ? $signup_data['userTimezone'] : '' ),
			'bookingpress_user_created'          => current_time( 'mysql' ),
		);
		$insert = $this->filter_to_existing_columns( $insert );

		$ok = $wpdb->insert( $this->table(), $insert );
		if ( false === $ok ) {
			return 0;
		}
		$new_id = (int) $wpdb->insert_id;
		$this->invalidate( 'by_email_' . md5( $email ) );
		if ( $wp_user_id > 0 ) {
			$this->invalidate( 'by_wpuser_' . $wp_user_id );
		}
		return $new_id;
	}

	/**
	 * Attach a WordPress user id to an existing customer row.
	 *
	 * Called from `SubmissionService::finalize_booking()` after a guest
	 * booking finalizes and the `allow_wp_user_create` setting fires the
	 * `wp_create_user()` step. Mirrors the legacy update inside
	 * `bookingpress_create_customer()` that writes `bookingpress_wpuser_id`
	 * and `bookingpress_user_login` onto the customer row.
	 *
	 * @param int    $customer_id
	 * @param int    $wp_user_id
	 * @param string $user_login Email-as-login per legacy convention.
	 *
	 * @return bool
	 */
	public function attach_wp_user( $customer_id, $wp_user_id, $user_login = '' ) {
		$customer_id = (int) $customer_id;
		$wp_user_id  = (int) $wp_user_id;
		if ( $customer_id <= 0 || $wp_user_id <= 0 ) {
			return false;
		}

		$update = array(
			'bookingpress_wpuser_id' => $wp_user_id,
		);
		if ( '' !== (string) $user_login ) {
			$update['bookingpress_user_login'] = (string) $user_login;
		}
		$update = $this->filter_to_existing_columns( $update );
		if ( empty( $update ) ) {
			return false;
		}

		global $wpdb;
		$ok = $wpdb->update(
			$this->table(),
			$update,
			array( 'bookingpress_customer_id' => $customer_id )
		);
		$this->invalidate( 'by_id_' . $customer_id );
		$this->invalidate( 'by_wpuser_' . $wp_user_id );
		if ( '' !== (string) $user_login ) {
			$this->invalidate( 'by_email_' . md5( strtolower( trim( (string) $user_login ) ) ) );
		}
		return false !== $ok;
	}

	/**
	 * Narrow-purpose update for the prefill round-trip.
	 *
	 * Accepts the same camelCase keys returned by `find_*` methods and
	 * translates them to the underlying `bookingpress_*` columns.
	 *
	 * @param int   $customer_id
	 * @param array $fields Map of camelCase key → value.
	 *
	 * @return bool
	 */
	public function update( $customer_id, array $fields ) {
		$customer_id = (int) $customer_id;
		if ( $customer_id <= 0 || empty( $fields ) ) {
			return false;
		}

		$map = array(
			'userFirstname'       => 'bookingpress_user_firstname',
			'userLastname'        => 'bookingpress_user_lastname',
			'userName'            => 'bookingpress_user_name',
			'userPhone'           => 'bookingpress_user_phone',
			'userCountryDialCode' => 'bookingpress_user_country_dial_code',
			'userTimezone'        => 'bookingpress_user_timezone',
			'customerFullName'    => 'bookingpress_customer_full_name',
		);
		$update = array();
		foreach ( $map as $camel => $col ) {
			if ( array_key_exists( $camel, $fields ) ) {
				$update[ $col ] = (string) $fields[ $camel ];
			}
		}
		$update = $this->filter_to_existing_columns( $update );
		if ( empty( $update ) ) {
			return false;
		}

		global $wpdb;
		$ok = $wpdb->update(
			$this->table(),
			$update,
			array( 'bookingpress_customer_id' => $customer_id )
		);
		$this->invalidate( 'by_id_' . $customer_id );
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

	/**
	 * @param array $row Raw row from SELECT *.
	 *
	 * @return array<string, mixed>
	 */
	private function normalize_row( array $row ) {
		return array(
			'customerId'          => (int) $this->pluck( $row, 'bookingpress_customer_id', 0 ),
			'wpUserId'            => (int) $this->pluck( $row, 'bookingpress_wpuser_id', 0 ),
			'userLogin'           => (string) $this->pluck( $row, 'bookingpress_user_login', '' ),
			'userStatus'          => (int) $this->pluck( $row, 'bookingpress_user_status', 0 ),
			'userType'            => (int) $this->pluck( $row, 'bookingpress_user_type', 0 ),
			'userName'            => (string) $this->pluck( $row, 'bookingpress_user_name', '' ),
			'userFirstname'       => (string) $this->pluck( $row, 'bookingpress_user_firstname', '' ),
			'userLastname'        => (string) $this->pluck( $row, 'bookingpress_user_lastname', '' ),
			'customerFullName'    => (string) $this->pluck( $row, 'bookingpress_customer_full_name', '' ),
			'userEmail'           => (string) $this->pluck( $row, 'bookingpress_user_email', '' ),
			'userPhone'           => (string) $this->pluck( $row, 'bookingpress_user_phone', '' ),
			'userCountryPhone'    => (string) $this->pluck( $row, 'bookingpress_user_country_phone', '' ),
			'userCountryDialCode' => (string) $this->pluck( $row, 'bookingpress_user_country_dial_code', '' ),
			'userTimezone'        => (string) $this->pluck( $row, 'bookingpress_user_timezone', '' ),
		);
	}
}
