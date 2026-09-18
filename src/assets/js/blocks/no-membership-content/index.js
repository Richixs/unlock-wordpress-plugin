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
  modifierClass: "unlock-slot-editor--no-membership",
  dashicon: "cart",
  editorLabel: __("No valid membership", "unlock-protocol"),
  editorHint: __(
    "Only visitors with a session but no valid membership see this. Add blocks below — they replace the default purchase button entirely.",
    "unlock-protocol"
  ),
});
