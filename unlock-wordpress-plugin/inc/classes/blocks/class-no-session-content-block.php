<?php
/**
 * No-session slot block class.
 *
 * @since 4.2.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc\Blocks;

use Unlock_Protocol\Inc\Traits\Singleton;

/**
 * Class No_Session_Content_Block
 *
 * A container block, only insertable inside unlock-protocol/unlock-box, that
 * lets an admin author arbitrary WordPress content (any blocks, not just a
 * styled button) to show in place of the default login prompt when the
 * visitor has no wallet session yet.
 *
 * This block has no render_callback: it saves its InnerBlocks content
 * statically, wrapped in a `data-unlock-slot="no-session"` element. The
 * parent unlock-box block (see Unlock::render_content() and
 * Unlock::extract_slot_content()) looks for that wrapper inside its own
 * rendered content to decide what to show, and falls back to the default
 * login button when this block isn't present.
 *
 * @since 4.2.0
 */
class No_Session_Content_Block {

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
			'unlock-protocol/no-session-content',
			array(
				'supports' => array(
					'align' => false,
				),
			)
		);
	}
}
