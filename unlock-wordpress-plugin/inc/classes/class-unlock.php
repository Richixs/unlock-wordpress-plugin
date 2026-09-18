<?php
/**
 * Unlock class.
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc;

use Unlock_Protocol\Inc\Traits\Singleton;

/**
 * Class Unlock
 *
 * @since 3.0.0
 */
class Unlock {

	use Singleton;

	/**
	 * How many times render_login_button() has run during this request.
	 *
	 * A page can have several locked blocks; if none of them has a session,
	 * showing the full styled "log in" prompt (description, background
	 * image, etc.) once per block is repetitive and confusing. Only the
	 * first occurrence gets the full treatment — see
	 * render_repeated_login_notice().
	 *
	 * @since 4.1.0
	 *
	 * @var int
	 */
	private static $login_button_render_count = 0;

	/**
	 * Returns the locksmith base URL used to validate the auth tokens.
	 */
	public static function get_locksmith_validate_url_base() {
		$settings = get_option( 'unlock_protocol_settings', array() );

		$locksmith_url_base = 'https://locksmith.unlock-protocol.com/api/oauth';
		if (isset($settings['general']['locksmith_url_base']) && 
			filter_var($settings['general']['locksmith_url_base'], FILTER_VALIDATE_URL)) {
			$locksmith_url_base = $settings['general']['locksmith_url_base'];
		}
		return $locksmith_url_base;
	}

	/**
	 * Returns the checkout base URL used to for both auth and checkout.
	 */
	public static function get_checkout_url_base() {
		$settings = get_option( 'unlock_protocol_settings', array() );
		$checkout_url_base = 'https://app.unlock-protocol.com/checkout';
		if (isset($settings['general']['checkout_url_base']) && 
			filter_var($settings['general']['checkout_url_base'], FILTER_VALIDATE_URL)) {
			$checkout_url_base = $settings['general']['checkout_url_base'];
		}
		return $checkout_url_base;
	}

	/**
	 * Post call to validate if a user has access to a content.
	 *
	 * @param string $url Network RPC endpoint.
	 * @param string $lock_address Lock address.
	 * @param string $user_ethereum_address User ethereum address.
	 *
	 * @since 3.0.0
	 *
	 * @return float|int|\WP_Error
	 */
	public static function has_access( $networks, $locks, $user_ethereum_address = null ) {

		$has_unlocked = false;

		foreach($locks as $lock) {
			if (!$has_unlocked) {
				// Find the URL!
				foreach($networks as $network) {
					if ($network["network_id"] == $lock['network']) {
						$url = $network["network_rpc_endpoint"];
					}
				}

				$validation = self::validate( $url, $lock['address'], $user_ethereum_address );

				if ( is_wp_error( $validation ) || ! isset( $validation['result'] ) ) {
					break;
				}
				$has_unlocked = hexdec( $validation['result'] ) == 1;
			}
		}

		return $has_unlocked;
	}

	/**
	 * Post call to validate.
	 *
	 * @param string $url Network RPC endpoint.
	 * @param string $lock_address Lock address.
	 * @param string $user_ethereum_address User ethereum address.
	 *
	 * @since 3.0.0
	 *
	 * @return float|int|\WP_Error
	 */
	public static function validate( $url, $lock_address, $user_ethereum_address = null ) {
		$user_ethereum_address = $user_ethereum_address ? $user_ethereum_address : up_get_user_ethereum_address();
		$user_ethereum_address = substr( $user_ethereum_address, 2 );

		$params = apply_filters(
			'unlock_protocol_user_validate_params',
			array(
				'method'  => 'eth_call',
				'params'  => array(
					array(
						'to'   => $lock_address,
						'data' => sprintf( '0x6d8ea5b4000000000000000000000000%s', $user_ethereum_address ),
					),
					'latest',
				),
				'id'      => 31337,
				'jsonrpc' => '2.0',
			)
		);

		$args = array(
			'body'        => wp_json_encode( $params ),
			'redirection' => '30',
			'httpversion' => '1.0',
			'blocking'    => true,
		);

		$response = wp_remote_post( esc_url( $url ), $args );

		if ( is_wp_error( $response ) ) {
			return new \WP_Error( 'unlock_validate_error', $response );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return $body;
	}

	/**
	 * Get checkout url.
	 *
	 * @param string $lock_address Lock address.
	 * @param string $network_id Network ID.
	 * @param string $redirect_uri Redirect URI.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_checkout_url( $locks, $redirect_uri ) {
		$paywall_locks = array();
		$settings = get_option( 'unlock_protocol_settings', array() );
		// Let's add the default setup in the config too!

		$default_paywall_config = array();
		if (isset($settings['general']['custom_paywall_config'])) {
			$default_paywall_config = json_decode($settings['general']['custom_paywall_config'], true);
		}

		foreach ($locks as $lock) {
			$paywall_locks[$lock["address"]] = array('network' => (int) $lock["network"],);
		}

		$paywall_config = apply_filters(
			'unlock_protocol_paywall_config',
			array_merge(
				$default_paywall_config ?? array(), 
				array(
					'locks'       => $paywall_locks,
					'pessimistic' => true,
				)
			)
		);
		$checkout_url = add_query_arg(
			array(
				'redirectUri'   => $redirect_uri,
				'paywallConfig' => wp_json_encode( $paywall_config ),
			),
			self::get_checkout_url_base()
		);

		return $checkout_url;
	}

	/**
	 * Get client id.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_client_id() {
		return apply_filters( 'unlock_protocol_get_client_id', wp_parse_url( home_url(), PHP_URL_HOST ) );
	}

	/**
	 * Get redirect uri.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_redirect_uri() {
		return apply_filters( 'unlock_protocol_get_redirect_uri', wp_login_url() );
	}

	/**
	 * Validate auth code.
	 *
	 * @param string $code Authorization code.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Error
	 */
	public static function validate_auth_code( $code ) {
		$params = apply_filters(
			'unlock_protocol_validate_auth_code_params',
			array(
				'grant_type'   => 'authorization_code',
				'client_id'    => self::get_client_id(),
				'redirect_uri' => self::get_redirect_uri(),
				'code'         => $code,
			)
		);

		$args = array(
			'body'        => $params,
			'redirection' => '30',
			'httpversion' => '1.0',
			'blocking'    => true,
		);

		$response = wp_remote_post( esc_url( self::get_locksmith_validate_url_base() ), $args );

		if ( is_wp_error( $response ) ) {
			return new \WP_Error( 'unlock_validate_auth_code', $response );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! array_key_exists( 'me', $body ) ) {
			return new \WP_Error( 'unlock_validate_auth_code', __( 'Invalid Account', 'unlock-protocol' ) );
		}

		return $body['me'];
	}

	/**
	 * Get login url.
	 *
	 * @param string $redirect_uri Redirect URI.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_login_url( $redirect_uri = null ) {
		$login_url = add_query_arg(
			array(
				'client_id'    => self::get_client_id(),
				'redirect_uri' => $redirect_uri ? $redirect_uri : self::get_redirect_uri(),
				'state'        => wp_create_nonce( 'unlock_login_state' ),
			),
			self::get_checkout_url_base()
		);

		return apply_filters( 'unlock_protocol_get_login_url', $login_url );
	}

	/**
	 * Get networks list.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function networks_list() {
		$networks = array(
			'mainnet'  => array(
				'network_name'         => 'goerli',
				'network_id'           => 5,
				'network_rpc_endpoint' => 'https://rpc.unlock-protocol.com/5',
			),
			'mainnet'  => array(
				'network_name'         => 'mainnet',
				'network_id'           => 1,
				'network_rpc_endpoint' => 'https://rpc.unlock-protocol.com/1',
			),
			'xdai'     => array(
				'network_name'         => 'gnosis chain',
				'network_id'           => 100,
				'network_rpc_endpoint' => 'https://rpc.unlock-protocol.com/100',
			),
			'polygon'  => array(
				'network_name'         => 'polygon',
				'network_id'           => 137,
				'network_rpc_endpoint' => 'https://rpc.unlock-protocol.com/137',
			),
			'optimism'     => array(
				'network_name'         => 'Optimism',
				'network_id'           => 10,
				'network_rpc_endpoint' => 'https://rpc.unlock-protocol.com/10',
			),
			'arbitrum' => array(
				'network_name'         => 'arbitrum',
				'network_id'           => 42161,
				'network_rpc_endpoint' => 'https://rpc.unlock-protocol.com/42161',
			),
			'binance'  => array(
				'network_name'         => 'BNB Chain',
				'network_id'           => 56,
				'network_rpc_endpoint' => 'https://rpc.unlock-protocol.com/56',
			),
		);

		return apply_filters( 'unlock_protocol_network_list', $networks );
	}


	/**
	 * Resolve a single appearance setting, preferring a per-block override
	 * over the site-wide general setting.
	 *
	 * A block only overrides appearance when its override array explicitly
	 * sets `useGlobal` to false; otherwise the general setting is used, which
	 * keeps existing sites (and blocks with no override at all) behaving
	 * exactly as before this was introduced.
	 *
	 * @param array  $override    Per-block appearance override, if any.
	 * @param string $key         Key inside $override (and inside the general settings' matching shape).
	 * @param string $general_key Key used to read the site-wide general setting.
	 * @param mixed  $default     Fallback default when neither is set.
	 *
	 * @since 4.1.0
	 *
	 * @return mixed
	 */
	private static function get_appearance_setting( $override, $key, $general_key, $default = '' ) {
		$use_override = ! empty( $override ) && empty( $override['useGlobal'] ) && array_key_exists( $key, $override );

		if ( $use_override ) {
			return $override[ $key ];
		}

		return up_get_general_settings( $general_key, $default );
	}

	/**
	 * Render checkout button.
	 *
	 * @param array $locks    locks.
	 * @param array $override Optional per-block appearance override for the
	 *                        "no membership" state. See get_appearance_setting().
	 *
	 * @return mixed|void
	 */
	public static function render_checkout_button( $locks, $override = array() ) {
		$checkout_url = Unlock::get_checkout_url( $locks, get_permalink() );

		$checkout_button_text       = self::get_appearance_setting( $override, 'text', 'checkout_button_text', __( 'Purchase this', 'unlock-protocol' ) );
		$checkout_button_bg_color   = self::get_appearance_setting( $override, 'bgColor', 'checkout_button_bg_color', '#000' );
		$checkout_button_text_color = self::get_appearance_setting( $override, 'textColor', 'checkout_button_text_color', '#fff' );
		$blurred_image_activated    = wp_validate_boolean( self::get_appearance_setting( $override, 'blurred', 'checkout_blurred_image_button', false ) );

		$template_data = array(
			'checkout_url'               => $checkout_url,
			'checkout_button_text'       => $checkout_button_text,
			'checkout_button_bg_color'   => $checkout_button_bg_color,
			'checkout_button_text_color' => $checkout_button_text_color,
			'blurred_image_activated'    => $blurred_image_activated,
		);

		// Fetching some more data if blurred image button type is activated.
		if ( $blurred_image_activated ) {
			$checkout_button_description = self::get_appearance_setting( $override, 'description', 'checkout_button_description', __( 'To view this content please', 'unlock-protocol' ) );
			$checkout_bg_image           = self::get_appearance_setting( $override, 'bgImage', 'checkout_bg_image', '' );

			$template_data['checkout_button_description'] = $checkout_button_description;
			$template_data['checkout_bg_image']           = $checkout_bg_image;
		}

		$html_template = unlock_protocol_get_template( 'login/checkout-button', $template_data );

		return apply_filters( 'unlock_protocol_checkout_content', $html_template, $template_data );
	}

	/**
	 * Render login button.
	 *
	 * @param array $override Optional per-block appearance override for the
	 *                        "no session" state. See get_appearance_setting().
	 *
	 * @return mixed|void
	 */
	public static function render_login_button( $override = array() ) {
		self::$login_button_render_count++;

		if ( self::$login_button_render_count > 1 ) {
			return self::render_repeated_login_notice( $override );
		}

		$login_button_text       = self::get_appearance_setting( $override, 'text', 'login_button_text', __( 'Login with Unlock', 'unlock-protocol' ) );
		$login_button_bg_color   = self::get_appearance_setting( $override, 'bgColor', 'login_button_bg_color', '#000' );
		$login_button_text_color = self::get_appearance_setting( $override, 'textColor', 'login_button_text_color', '#fff' );
		$blurred_image_activated = wp_validate_boolean( self::get_appearance_setting( $override, 'blurred', 'login_blurred_image_button', false ) );

		$template_data = array(
			'login_url'               => Unlock::get_login_url( get_permalink() ),
			'login_button_text'       => $login_button_text,
			'login_button_bg_color'   => $login_button_bg_color,
			'login_button_text_color' => $login_button_text_color,
			'blurred_image_activated' => $blurred_image_activated,
		);

		// Fetching some more data if blurred image button type is activated.
		if ( $blurred_image_activated ) {
			$login_button_description = self::get_appearance_setting( $override, 'description', 'login_button_description', __( 'To view this content please', 'unlock-protocol' ) );
			$login_bg_image           = self::get_appearance_setting( $override, 'bgImage', 'login_bg_image', '' );

			$template_data['login_button_description'] = $login_button_description;
			$template_data['login_bg_image']           = $login_bg_image;
		}

		$html_template = unlock_protocol_get_template( 'login/button', $template_data );

		return apply_filters( 'unlock_protocol_login_content', $html_template, $template_data );
	}

	/**
	 * Compact markup used from the second locked block onward, on a page
	 * where the visitor has no wallet session. Keeps a working login link
	 * without repeating the full styled prompt (description, background
	 * image) for every locked section.
	 *
	 * @param array $override Optional per-block appearance override; only
	 *                        its 'text' is used here, everything else
	 *                        belongs to the full prompt.
	 *
	 * @since 4.1.0
	 *
	 * @return mixed|void
	 */
	private static function render_repeated_login_notice( $override ) {
		$login_button_text = self::get_appearance_setting( $override, 'text', 'login_button_text', __( 'Login with Unlock', 'unlock-protocol' ) );

		$html_template = sprintf(
			'<p class="unlock-login-repeated-notice"><a href="%1$s">%2$s</a></p>',
			esc_url( Unlock::get_login_url( get_permalink() ) ),
			esc_html( $login_button_text )
		);

		return apply_filters( 'unlock_protocol_login_content_repeated', $html_template, $override );
	}

	/**
	 * Render block.
	 *
	 * @param array  $locks      List of attributes passed in block.
	 * @param string $content    post content.
	 * @param array  $appearance Optional per-block appearance overrides, keyed
	 *                           'login' and 'noMembership'. Callers that don't
	 *                           pass this (e.g. the full-post lock flow) keep
	 *                           using the site-wide general settings exactly
	 *                           as before.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML elements.
	 */
	public static function render_content( $locks, $content, $appearance = array() ) {
		// Bail out if current user is admin or the author.
		if ( current_user_can( 'manage_options' ) || ( get_the_author_meta( 'ID' ) === get_current_user_id() ) ) {
			return $content;
		}

		if (
			! is_user_logged_in() ||
			( is_user_logged_in() && ! up_get_user_ethereum_address() )
		) {
			return Unlock::render_login_button( isset( $appearance['login'] ) ? $appearance['login'] : array() );
		}

		$settings = get_option( 'unlock_protocol_settings', array() );
		$networks = isset( $settings['networks'] ) ? $settings['networks'] : array();

		if ( !Unlock::has_access( $networks, $locks ) ) {
			return Unlock::render_checkout_button( $locks, isset( $appearance['noMembership'] ) ? $appearance['noMembership'] : array() );
		}
		return $content;
	}



}
