<?php
/**
 * No-membership slot block class.
 *
 * @since 4.2.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc\Blocks;

use Unlock_Protocol\Inc\Traits\Singleton;

/**
 * Class No_Membership_Content_Block
 *
 * A container block, only insertable inside unlock-protocol/unlock-box, that
 * lets an admin author arbitrary WordPress content (any blocks, not just a
 * styled button) to show when the visitor has a wallet session but no valid
 * membership for the configured lock(s).
 *
 * Same mechanism as No_Session_Content_Block: static InnerBlocks content
 * wrapped in a `data-unlock-slot="no-membership"` element, picked up by
 * Unlock::extract_slot_content() from the parent block's rendered content.
 *
 * @since 4.2.0
 */
class No_Membership_Content_Block {

	use Singleton;

	/**
	 * Construct method.
	 *
	 * @since 4.2.0
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'register_block_type' ) );
	}

	/**
	 * Register block type.
	 *
	 * @since 4.2.0
	 */
	public function register_block_type() {
		register_block_type(
			'unlock-protocol/no-membership-content',
			array(
				'supports' => array(
					'align' => false,
				),
			)
		);
	}
}
