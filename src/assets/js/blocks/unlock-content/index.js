import { __ } from "@wordpress/i18n";
import { registerBlockType } from "@wordpress/blocks";
import { InnerBlocks } from "@wordpress/block-editor";
import Edit from "./edit";
import { DEFAULT_APPEARANCE } from "./appearance-panel";

/**
 * Register the block.
 */
registerBlockType("unlock-protocol/unlock-box", {
  title: __("Unlock Protocol", "unlock-protocol"),

  category: "common",

  icon: "lock",

  description: __(
    "A block to add lock(s) to the content inside of WordPress. This lets you restrict access to some section in your post.",
    "unlock-protocol"
  ),

  attributes: {
    locks: {
      type: "array",
      default: [],
    },
    ethereumNetworks: {
      type: "array",
      default: [],
    },
    // Per-block appearance override for the "no session" state.
    loginAppearance: {
      type: "object",
      default: DEFAULT_APPEARANCE,
    },
    // Per-block appearance override for the "no membership" state.
    noMembershipAppearance: {
      type: "object",
      default: DEFAULT_APPEARANCE,
    },
  },

  supports: {
    align: true,
  },

  /**
   * @see ./edit.js
   */
  edit: Edit,

  save: () => {
    return <InnerBlocks.Content />;
  },
});
