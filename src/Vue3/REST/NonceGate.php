<?php
/**
 * NonceGate — single chokepoint for REST authentication.
 *
 * Verifies three tokens per plan §4 / §4.1:
 *
 *   1. `X-WP-Nonce` HTTP header → `wp_rest` action (WP REST default).
 *   2. `bp_v3_nonce` body param → `bookingpress_form_v3_nonce` action
 *      (per-render form CSRF defense-in-depth).
 *   3. `bp_v3_instance_token` body param → transient bound to the requesting
 *      `instanceId` (per-render anti-replay).
 *
 * Every controller delegates to {@see self::verify()} before doing any work.
 *
 * @package BookingPress\Vue3\REST
 * @see     docs/migration/BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md §4.1
 */

namespace BookingPress\Vue3\REST;

use BookingPress\Vue3\Services\NonceService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NonceGate {

	/** @var NonceService */
	private $nonces;

	public function __construct( ?NonceService $nonces = null ) {
		$this->nonces = $nonces ?: new NonceService();
	}

	/**
	 * Verify the three-token triple from a REST request.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return true|\WP_Error true on pass; WP_Error on any failure.
	 */
	public function verify( \WP_REST_Request $request ) {
		// 1. WP REST nonce — header only, no _wpnonce body fallback.
		$header = $request->get_header( 'x_wp_nonce' );
		if ( ! $header || ! wp_verify_nonce( (string) $header, 'wp_rest' ) ) {
			return new \WP_Error(
				'bp_v3_rest_invalid_nonce',
				'Invalid REST nonce.',
				array( 'status' => 403 )
			);
		}

		// 2. Form-render nonce — body only, namespaced field name.
		$form_nonce = (string) $request->get_param( 'bp_v3_nonce' );
		if ( '' === $form_nonce || ! wp_verify_nonce( $form_nonce, NonceService::NONCE_ACTION ) ) {
			return new \WP_Error(
				'bp_v3_invalid_form_nonce',
				'Invalid form nonce.',
				array( 'status' => 403 )
			);
		}

		// 3. Instance token — body, opaque, transient-backed.
		$instance_id    = (string) $request->get_param( 'instanceId' );
		$instance_token = (string) $request->get_param( 'bp_v3_instance_token' );
		if ( '' === $instance_id || ! $this->nonces->is_valid_instance_token( $instance_token, $instance_id ) ) {
			return new \WP_Error(
				'bp_v3_invalid_instance',
				'Unknown form instance.',
				array( 'status' => 400 )
			);
		}

		return true;
	}

	/**
	 * Is this request coming from our own site?
	 *
	 * Stands in for the nonce check that {@see self::refresh_nonce()} cannot
	 * perform on itself. Browsers always attach `Origin` to a cross-origin POST
	 * and script cannot forge it, so an Origin that matches the host we are
	 * being served from is a reliable "this came from our own page" signal.
	 *
	 * @return bool
	 */
	private static function is_same_origin_request() {
		$origin = get_http_origin();

		if ( ! $origin && ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			$origin = sanitize_url( wp_unslash( $_SERVER['HTTP_REFERER'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}

		if ( ! $origin ) {
			// Neither header present. A browser cannot be made to omit Origin on
			// a cross-origin POST, so this is either a same-origin call whose
			// headers a proxy stripped or a non-browser client with no ambient
			// cookies to abuse. Allow it.
			return true;
		}

		$origin_host = wp_parse_url( $origin, PHP_URL_HOST );
		if ( empty( $origin_host ) ) {
			return false; // "null" origin — sandboxed iframe, data:/file: document.
		}

		$allowed = array();
		foreach ( array( home_url(), site_url() ) as $url ) {
			$host = wp_parse_url( $url, PHP_URL_HOST );
			if ( ! empty( $host ) ) {
				$allowed[] = strtolower( $host );
			}
		}
		if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
			$host = strtolower( wp_unslash( $_SERVER['HTTP_HOST'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$allowed[] = explode( ':', $host )[0]; // Drop any :port.
		}

		return in_array( strtolower( $origin_host ), $allowed, true );
	}

	/**
	 * Re-issue the three tokens after the page cache served a stale set.
	 *
	 * All three are minted at render time and baked into the HTML, but a
	 * `wp_rest` nonce only lives 12-24h (`wp_nonce_tick()`) and the instance
	 * token 12h. Any page cache outlives them, at which point every call 403s
	 * until someone purges the cache by hand — this route is how the form
	 * repairs itself instead.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function refresh_nonce( \WP_REST_Request $request ) {
		nocache_headers();

		// This route hands back a *valid* `wp_rest` nonce and cannot demand one
		// of itself — a stale nonce is the whole reason the client is here. That
		// makes it a CSRF-token oracle unless the caller is proven local:
		// `rest_send_cors_headers()` echoes any Origin back with
		// `Access-Control-Allow-Credentials: true`, so a third-party page could
		// otherwise fetch this with a logged-in admin's cookies, read the nonce
		// out of the response and drive the entire REST API as that admin.
		if ( ! self::is_same_origin_request() ) {
			return new \WP_Error(
				'bp_v3_cross_origin_refused',
				'Cross-origin token refresh refused.',
				array( 'status' => 403 )
			);
		}

		$instance_id = (string) $request->get_param( 'instanceId' );
		if ( '' === $instance_id ) {
			return new \WP_Error(
				'bp_v3_invalid_instance',
				'Unknown form instance.',
				array( 'status' => 400 )
			);
		}

		// The client sends no `X-WP-Nonce` to this route, so core's
		// rest_cookie_check_errors() has already run wp_set_current_user( 0 ).
		// Restore the cookie-authenticated user before minting: wp_create_nonce()
		// hashes the current uid while wp_get_session_token() reads the real
		// logged-in cookie either way, so minting at uid 0 against a live session
		// token yields a nonce that can never verify on the follow-up call. That
		// mismatch is why the old refresh + retry always came back 403.
		if ( ! is_user_logged_in() ) {
			$cookie_user = wp_validate_auth_cookie( '', 'logged_in' );
			if ( $cookie_user ) {
				wp_set_current_user( $cookie_user );
			}
		}

		$nonces         = new NonceService();
		$instance_token = $nonces->issue_instance_token( $instance_id );

		return Response::ok(
			array(
				'wp_rest_nonce' => wp_create_nonce( 'wp_rest' ),
				'form_nonce'    => wp_create_nonce( NonceService::NONCE_ACTION ),
				'instanceId'    => $instance_id,
				'instanceToken' => $instance_token,
			)
		);
	}
}
