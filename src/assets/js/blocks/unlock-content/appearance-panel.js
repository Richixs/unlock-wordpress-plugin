import { __ } from "@wordpress/i18n";
import {
  PanelBody,
  ToggleControl,
  TextControl,
  TextareaControl,
  Button,
} from "@wordpress/components";
import { PanelColorSettings, MediaUpload, MediaUploadCheck } from "@wordpress/block-editor";

/**
 * Default shape for a per-block appearance override. Mirrors the default
 * declared in class-unlock-box-block.php on the PHP side.
 */
export const DEFAULT_APPEARANCE = {
  useGlobal: true,
  text: "",
  description: "",
  bgColor: "",
  textColor: "",
  bgImage: "",
  blurred: false,
};

/**
 * Inspector panel letting an admin override, for this block only, the
 * appearance used when rendering either the "no session" or the
 * "no membership" state. Leaving "useGlobal" on keeps using the site-wide
 * settings configured in the plugin's admin screen, unchanged.
 */
export const AppearanceOverridePanel = ({ title, value, onChange }) => {
  const appearance = { ...DEFAULT_APPEARANCE, ...value };

  const update = (changes) => {
    onChange({ ...appearance, ...changes });
  };

  return (
    <PanelBody title={title} initialOpen={false}>
      <ToggleControl
        label={__("Use the site-wide appearance settings", "unlock-protocol")}
        checked={appearance.useGlobal}
        onChange={(useGlobal) => update({ useGlobal })}
      />

      {!appearance.useGlobal && (
        <>
          <TextControl
            label={__("Button text", "unlock-protocol")}
            value={appearance.text}
            onChange={(text) => update({ text })}
          />

          <ToggleControl
            label={__("Show a description and background image", "unlock-protocol")}
            checked={appearance.blurred}
            onChange={(blurred) => update({ blurred })}
          />

          {appearance.blurred && (
            <>
              <TextareaControl
                label={__("Description", "unlock-protocol")}
                value={appearance.description}
                onChange={(description) => update({ description })}
              />

              <MediaUploadCheck>
                <MediaUpload
                  onSelect={(media) => update({ bgImage: media.url })}
                  allowedTypes={["image"]}
                  render={({ open }) => (
                    <Button variant="secondary" onClick={open}>
                      {appearance.bgImage
                        ? __("Change background image", "unlock-protocol")
                        : __("Select background image", "unlock-protocol")}
                    </Button>
                  )}
                />
              </MediaUploadCheck>

              {appearance.bgImage && (
                <Button
                  variant="link"
                  isDestructive
                  onClick={() => update({ bgImage: "" })}
                >
                  {__("Remove background image", "unlock-protocol")}
                </Button>
              )}
            </>
          )}

          <PanelColorSettings
            title={__("Colors", "unlock-protocol")}
            initialOpen={false}
            colorSettings={[
              {
                value: appearance.bgColor,
                onChange: (bgColor) => update({ bgColor }),
                label: __("Background color", "unlock-protocol"),
              },
              {
                value: appearance.textColor,
                onChange: (textColor) => update({ textColor }),
                label: __("Text color", "unlock-protocol"),
              },
            ]}
          />
        </>
      )}
    </PanelBody>
  );
};
