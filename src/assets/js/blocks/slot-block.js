import { registerBlockType } from "@wordpress/blocks";
import { InnerBlocks, useBlockProps } from "@wordpress/block-editor";

/**
 * Registers a "slot" container block: an InnerBlocks area only insertable
 * inside unlock-protocol/unlock-box, letting an admin author arbitrary
 * WordPress content (any blocks — not just a styled button) for one of the
 * plugin's non-membership states.
 *
 * The block has no dynamic render_callback on the PHP side: it saves its
 * InnerBlocks content statically, wrapped in a `data-unlock-slot` element.
 * Unlock::extract_slot_content() (class-unlock.php) looks for that wrapper
 * inside the parent block's rendered content to decide what to show, and
 * falls back to the built-in button when the slot isn't present.
 */
export const registerSlotBlock = ({ name, title, description, slot, editorLabel }) => {
  registerBlockType(name, {
    title,
    category: "common",
    icon: "lock",
    description,
    parent: ["unlock-protocol/unlock-box"],
    supports: {
      align: false,
    },
    edit: () => {
      const blockProps = useBlockProps({ className: "unlock-slot-editor" });
      return (
        <div {...blockProps}>
          <p className="unlock-slot-editor__label">{editorLabel}</p>
          <InnerBlocks />
        </div>
      );
    },
    save: () => {
      const blockProps = useBlockProps.save();
      return (
        <div {...blockProps} data-unlock-slot={slot}>
          <InnerBlocks.Content />
        </div>
      );
    },
  });
};
