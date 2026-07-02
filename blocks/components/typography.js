import { __ } from "@wordpress/i18n";
import { SelectControl, RangeControl } from "@wordpress/components";
import { useEffect, useState } from "@wordpress/element";

function TypographyControls({ attributes, setAttributes, attributeNames }) {
    const [showControls, setShowControls] = useState(true);

    return (
        <div className="easycommerce-typography-main-wrapper">
            <div className="easycommerce-typography-settings-warpper">
                <i
                    className="fa-solid fa-pen-to-square easycommerce-show-typography"
                    onClick={() => setShowControls(!showControls)}
                ></i>
            </div>
            {showControls && (
                <>
                    <div className="easycommerce-typography-inner-wrapper">
                        <RangeControl
                            label="Font Size"
                            value={attributes[attributeNames[0]]}
                            onChange={(newVal) =>
                                setAttributes({ [attributeNames[0]]: newVal })
                            }
                            min={1}
                            max={100}
                        />
                        <SelectControl
                            label={__("Font Weight", "easycommerce")}
                            value={attributes[attributeNames[1]]}
                            options={[
                                { label: "100 (Thin)", value: "100" },
                                { label: "200 (Extra Light)", value: "200" },
                                { label: "300 (Light)", value: "300" },
                                { label: "400 (Normal)", value: "400" },
                                { label: "500 (Medium)", value: "500" },
                                { label: "600 (Semi Bold)", value: "600" },
                                { label: "700 (Bold)", value: "700" },
                                { label: "800 (Extra Bold)", value: "800" },
                                { label: "900 (Black)", value: "900" },
                                { label: "Default", value: "" },
                                { label: "Normal", value: "normal" },
                                { label: "Bold", value: "bold" },
                            ]}
                            onChange={(value) =>
                                setAttributes({ [attributeNames[1]]: value })
                            }
                        />
                        <SelectControl
                            label={__("Text Transform", "easycommerce")}
                            value={attributes[attributeNames[2]]}
                            options={[
                                { label: "Default", value: "" },
                                { label: "Uppercase", value: "uppercase" },
                                { label: "Lowercase", value: "lowercase" },
                                { label: "Capitalize", value: "capitalize" },
                                { label: "Normal", value: "none" },
                            ]}
                            onChange={(value) =>
                                setAttributes({ [attributeNames[2]]: value })
                            }
                        />
                        <SelectControl
                            label={__("Text Style", "easycommerce")}
                            value={attributes[attributeNames[3]]}
                            options={[
                                { label: "Default", value: "" },
                                { label: "Normal", value: "normal" },
                                { label: "Italic", value: "italic" },
                                { label: "Oblique", value: "oblique" },
                            ]}
                            onChange={(value) =>
                                setAttributes({ [attributeNames[3]]: value })
                            }
                        />
                        <SelectControl
                            label={__("Text Decoration", "easycommerce")}
                            value={attributes[attributeNames[4]]}
                            options={[
                                { label: "None", value: "none" },
                                { label: "Underline", value: "underline" },
                                { label: "Overline", value: "overline" },
                                {
                                    label: "Line Through",
                                    value: "line-through",
                                },
                            ]}
                            onChange={(value) =>
                                setAttributes({ [attributeNames[4]]: value })
                            }
                        />
                        <RangeControl
                            label="Line Height"
                            value={attributes[attributeNames[5]]}
                            onChange={(newVal) =>
                                setAttributes({ [attributeNames[5]]: newVal })
                            }
                            min={1}
                            max={100}
                        />
                        <RangeControl
                            label="Word Spacing"
                            value={attributes[attributeNames[6]]}
                            onChange={(newVal) =>
                                setAttributes({ [attributeNames[6]]: newVal })
                            }
                            min={1}
                            max={100}
                        />
                    </div>
                </>
            )}
        </div>
    );
}

export default TypographyControls;
