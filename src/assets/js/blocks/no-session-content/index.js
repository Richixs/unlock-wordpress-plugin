import { __ } from "@wordpress/i18n";
import { registerSlotBlock } from "../slot-block";

registerSlotBlock({
  name: "unlock-protocol/no-session-content",
  title: __("Unlock: No Session Content", "unlock-protocol"),
  description: __(
    "Content shown when the visitor doesn't have a wallet session yet. Only works nested inside an Unlock Protocol block.",
    "unlock-protocol"
  ),
  slot: "no-session",
  modifierClass: "unlock-slot-editor--no-session",
  dashicon: "unlock",
  editorLabel: __("No wallet session", "unlock-protocol"),
  editorHint: __(
    "Only visitors without a wallet session see this. Add blocks below — they replace the default login button entirely.",
    "unlock-protocol"
  ),
});
