<?php
/**
 * Unlock box dynamic block class.
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc\Blocks;

use Unlock_Protocol\Inc\Login;
use Unlock_Protocol\Inc\Traits\Singleton;
use Unlock_Protocol\Inc\Unlock;

/**
 * Class Unlock_Box_Block
 *
 * @since 3.0.0
 */
class Unlock_Box_Block {

	use Singleton;

	/**
	 * Construct method.
	 *
	 * @since 3.0.0
	 */
	protected function __construct() {

		$this->setup_hooks();

	}

	/**
	 * Setup hooks.
	 *
	 * @since 3.0.0
	 */
	protected function setup_hooks() {
		/**
		 * Actions.
		 */
		add_action( 'init', array( $this, 'register_block_type' ) );
	}

	/**
	 * Default shape for a per-block appearance override. Every field mirrors
	 * one of the general settings consumed by Unlock::get_appearance_setting();
	 * `useGlobal` stays true until an admin explicitly opts into a per-block
	 * look from the editor, so existing content keeps rendering the site-wide
	 * appearance unchanged.
	 *
	 * @since 4.1.0
	 *
	 * @return array
	 */
	private function default_appearance_attribute() {
		return array(
			'type'    => 'object',
			'default' => array(
				'useGlobal'   => true,
				'text'        => '',
				'description' => '',
				'bgColor'     => '',
				'textColor'   => '',
				'bgImage'     => '',
				'blurred'     => false,
			),
		);
	}

	/**
	 * Register block type.
	 *
	 * @since 3.0.0
	 */
	public function register_block_type() {
		register_block_type(
			'unlock-protocol/unlock-box',
			array(
				'render_callback' => array( $this, 'render_block' ),
				'attributes'      => array(
					'locks'      => array(
						'type'    => 'array',
						'default' => array(),
					),
					'ethereumNetworks' => array(
						'type'    => 'array',
						'default' => array(),
					),
					// Per-block appearance override for the "no session" state (see templates/login/button.php).
					'loginAppearance'         => $this->default_appearance_attribute(),
					// Per-block appearance override for the "no membership" state (see templates/login/checkout-button.php).
					'noMembershipAppearance'  => $this->default_appearance_attribute(),
				),
				'supports'        => array(
					'align' => true,
				),
			)
		);
	}

	/**
	 * Render block.
	 *
	 * @param array  $attributes List of attributes passed in block.
	 * @param string $content Block Content.
	 *
	 * @since 3.0.0
	 *
	 * @return string HTML elements.
	 */
	public function render_block( $attributes, $content ) {
		$locks      = $attributes['locks'];
		$appearance = array(
			'login'        => isset( $attributes['loginAppearance'] ) ? $attributes['loginAppearance'] : array(),
			'noMembership' => isset( $attributes['noMembershipAppearance'] ) ? $attributes['noMembershipAppearance'] : array(),
		);

		return Unlock::render_content( $locks, $content, $appearance );
	}
}
