<?php
/**
 * FormFieldRepository — reads `bookingpress_form_fields`.
 *
 * Defensively supports both Lite-only schema (10 base columns) and
 * Lite + Pro schema (which adds `bookingpress_field_meta_key`,
 * `bookingpress_field_type`, `bookingpress_field_options`, and
 * `bookingpress_is_customer_field`). Columns not present on the current
 * install resolve to safe defaults.
 *
 * @package BookingPress\Vue3\Repositories
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.8, §M0.11
 */

namespace BookingPress\Vue3\Repositories;

use BookingPress\Vue3\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FormFieldRepository extends BaseRepository {

	/**
	 * The eight reserved built-in field names. Anything not in this set is
	 * treated as a custom field (see §M0.8 + §M0.11).
	 *
	 * @var array<int, string>
	 */
	const BUILTIN_NAMES = array(
		'fullname',
		'firstname',
		'lastname',
		'email_address',
		'phone_number',
		'note',
		'username',
		'terms_and_conditions',
	);

	/**
	 * @inheritDoc
	 */
	protected function table_suffix() {
		return 'bookingpress_form_fields';
	}

	/**
	 * Return all non-hidden form fields, ordered by position, normalized.
	 *
	 * Output shape (camelCase):
	 *   array<int, array{
	 *     fieldId:        int,
	 *     fieldName:      string,        // e.g. 'fullname', 'email_address', or a Pro custom name
	 *     fieldLabel:     string,
	 *     fieldPlaceholder: string,
	 *     fieldRequired:  bool,
	 *     fieldErrorMessage: string,
	 *     fieldIsHide:    bool,
	 *     fieldPosition:  float,
	 *     fieldIsDefault: bool,
	 *     // Pro-extended (null on Lite-only):
	 *     fieldMetaKey:    string|null,
	 *     fieldType:       string|null,  // 'Text' | 'Email' | 'Phone' | 'Textarea' | 'terms_and_conditions' | custom
	 *     fieldOptions:    array|null,
	 *     isCustomerField: bool|null,
	 *     // Derived:
	 *     isBuiltin:      bool,
	 *     isCustom:       bool
	 *   }>
	 *
	 * @param array $context Optional filter context.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_all( array $context = array() ) {
		$rows = $this->remember( 'all', function () {
			global $wpdb;
			$table = $this->table();
			$raw   = $wpdb->get_results(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM `{$table}` ORDER BY bookingpress_field_position ASC, bookingpress_form_field_id ASC",
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
		 * Filter the customer-form-fields payload before it leaves the repository.
		 *
		 * @param array $rows    Normalized field rows.
		 * @param array $context Caller context.
		 */
		$filtered = apply_filters( Hooks::FILTER_FORM_FIELDS, $rows, $context );
		return is_array( $filtered ) ? $filtered : $rows;
	}

	/**
	 * Return only the fields the booking form will actually render.
	 *
	 * Lite-side default: drop rows with `fieldIsHide === true`. Pro overrides
	 * this via {@see Hooks::FILTER_FORM_FIELDS} when it needs different
	 * visibility rules.
	 *
	 * @param array $context Optional filter context.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_visible( array $context = array() ) {
		$all = $this->get_all( $context );
		return array_values( array_filter( $all, static function ( $r ) {
			return empty( $r['fieldIsHide'] );
		} ) );
	}

	/**
	 * Normalize one raw `bookingpress_form_fields` row.
	 *
	 * @param array $row Raw row from SELECT *.
	 *
	 * @return array<string, mixed>
	 */
	private function normalize_row( array $row ) {
		$name = (string) $this->pluck( $row, 'bookingpress_form_field_name', '' );

		$meta_key      = $this->has_column( 'bookingpress_field_meta_key' )
			? $this->pluck( $row, 'bookingpress_field_meta_key', null )
			: null;
		$field_type    = $this->has_column( 'bookingpress_field_type' )
			? $this->pluck( $row, 'bookingpress_field_type', null )
			: null;
		$field_options = $this->has_column( 'bookingpress_field_options' )
			? $this->parse_options( $this->pluck( $row, 'bookingpress_field_options', null ) )
			: null;
		$is_customer   = $this->has_column( 'bookingpress_is_customer_field' )
			? ( '1' === (string) $this->pluck( $row, 'bookingpress_is_customer_field', '0' ) )
			: null;

		$is_builtin = in_array( $name, self::BUILTIN_NAMES, true );

		return array(
			'fieldId'           => (int) $this->pluck( $row, 'bookingpress_form_field_id', 0 ),
			'fieldName'         => $name,
			'fieldLabel'        => (string) $this->pluck( $row, 'bookingpress_field_label', '' ),
			'fieldPlaceholder'  => (string) $this->pluck( $row, 'bookingpress_field_placeholder', '' ),
			'fieldRequired'     => '1' === (string) $this->pluck( $row, 'bookingpress_field_required', '0' ),
			'fieldErrorMessage' => (string) $this->pluck( $row, 'bookingpress_field_error_message', '' ),
			'fieldIsHide'       => '1' === (string) $this->pluck( $row, 'bookingpress_field_is_hide', '0' ),
			'fieldPosition'     => (float) $this->pluck( $row, 'bookingpress_field_position', 0 ),
			'fieldIsDefault'    => '1' === (string) $this->pluck( $row, 'bookingpress_field_is_default', '0' ),
			'fieldMetaKey'      => $meta_key,
			'fieldType'         => $field_type,
			'fieldOptions'      => $field_options,
			'isCustomerField'   => $is_customer,
			'isBuiltin'         => $is_builtin,
			'isCustom'          => ! $is_builtin,
		);
	}

	/**
	 * Parse `bookingpress_field_options` — may be JSON, may be PHP-serialized.
	 *
	 * @param mixed $raw
	 *
	 * @return array|null
	 */
	private function parse_options( $raw ) {
		if ( empty( $raw ) ) {
			return null;
		}
		if ( is_array( $raw ) ) {
			return $raw;
		}
		$str = (string) $raw;

		// JSON shape sniff.
		if ( '' !== $str && ( '{' === $str[0] || '[' === $str[0] ) ) {
			$decoded = json_decode( $str, true );
			if ( is_array( $decoded ) ) {
				return $decoded;
			}
		}

		// PHP-serialized fallback.
		if ( 0 === strpos( $str, 'a:' ) || 0 === strpos( $str, 's:' ) || 0 === strpos( $str, 'O:' ) ) {
			$unserialized = BookingPress\BookingPressLoader::bpa_safe_maybe_unserialize( $str );
			if ( is_array( $unserialized ) ) {
				return $unserialized;
			}
		}

		return null;
	}

	public function get_field_meta( $field_id = ''){
		$rows = $this->remember( 'field_'.$field_id, function () use ( $field_id ) {
			global $wpdb;
			$table = $this->table();
			$raw   = $wpdb->get_var(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->prepare( 
					"SELECT bookingpress_field_meta_key FROM `{$table}` WHERE bookingpress_form_field_id = %d ORDER BY bookingpress_field_position ASC, bookingpress_form_field_id ASC",
					$field_id
				)
			);
			return $raw;
		} );

		return $rows;
	}
}
