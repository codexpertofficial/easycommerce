import { __ } from "@wordpress/i18n";
import {
    PanelBody,
    ToggleControl,
    ColorPalette,
    ColorPicker,
} from "@wordpress/components";
import { InspectorControls } from "@wordpress/block-editor";
import TypographyControls from "../../components/typography";

const Inspector = (props) => {
    //get attributes
    const { attributes, setAttributes } = props;
    const { color } = attributes;

    const colors = [
        { name: __("Body", "easycommerce"), color: "var(--color-ec-body)" },
        { name: __("Primary", "easycommerce"), color: "var(--color-ec-primary)" },
        { name: __("Body Light", "easycommerce"), color: "var(--color-ec-secondary)" },
        { name: __("Title", "easycommerce"), color: "#120350" },
    ];

    return (
        <>
            <InspectorControls>
                <PanelBody title={__("Styles", "easycommerce")}>
                    <ColorPicker
                        value={color}
                        onChange={(newColor) =>
                            setAttributes({
                                color: newColor,
                            })
                        }
                    />
                    <ColorPalette
                        colors={colors}
                        value={color}
                        onChange={(newColor) =>
                            setAttributes({
                                color: newColor,
                            })
                        }
                        disableCustomColors={true}
                    />
                    <TypographyControls
                        attributes={attributes}
                        setAttributes={setAttributes}
                        attributeNames={[
                            "fontSize",
                            "fontWeight",
                            "textTransform",
                            "fontStyle",
                            "decoration",
                            "lineHeight",
                            "spacing",
                        ]}
                    />
                </PanelBody>
            </InspectorControls>
        </>
    );
};

export default Inspector;
