import { __ } from "@wordpress/i18n";
import { registerSlotBlock } from "../slot-block";

registerSlotBlock({
  name: "unlock-protocol/no-membership-content",
  title: __("Unlock: No Membership Content", "unlock-protocol"),
  description: __(
    "Content shown when the visitor is logged in but doesn't have a valid membership. Only works nested inside an Unlock Protocol block.",
    "unlock-protocol"
  ),
  slot: "no-membership",
  editorLabel: __("Shown when the visitor has no valid membership:", "unlock-protocol"),
});
