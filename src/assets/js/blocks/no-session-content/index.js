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
  editorLabel: __("Shown when the visitor has no wallet session:", "unlock-protocol"),
});
