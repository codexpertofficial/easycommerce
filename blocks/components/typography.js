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
                            label={__("Font Size", "easycommerce")}
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
                                { label: __("100 (Thin)", "easycommerce"), value: "100" },
                                { label: __("200 (Extra Light)", "easycommerce"), value: "200" },
                                { label: __("300 (Light)", "easycommerce"), value: "300" },
                                { label: __("400 (Normal)", "easycommerce"), value: "400" },
                                { label: __("500 (Medium)", "easycommerce"), value: "500" },
                                { label: __("600 (Semi Bold)", "easycommerce"), value: "600" },
                                { label: __("700 (Bold)", "easycommerce"), value: "700" },
                                { label: __("800 (Extra Bold)", "easycommerce"), value: "800" },
                                { label: __("900 (Black)", "easycommerce"), value: "900" },
                                { label: __("Default", "easycommerce"), value: "" },
                                { label: __("Normal", "easycommerce"), value: "normal" },
                                { label: __("Bold", "easycommerce"), value: "bold" },
                            ]}
                            onChange={(value) =>
                                setAttributes({ [attributeNames[1]]: value })
                            }
                        />
                        <SelectControl
                            label={__("Text Transform", "easycommerce")}
                            value={attributes[attributeNames[2]]}
                            options={[
                                { label: __("Default", "easycommerce"), value: "" },
                                { label: __("Uppercase", "easycommerce"), value: "uppercase" },
                                { label: __("Lowercase", "easycommerce"), value: "lowercase" },
                                { label: __("Capitalize", "easycommerce"), value: "capitalize" },
                                { label: __("Normal", "easycommerce"), value: "none" },
                            ]}
                            onChange={(value) =>
                                setAttributes({ [attributeNames[2]]: value })
                            }
                        />
                        <SelectControl
                            label={__("Text Style", "easycommerce")}
                            value={attributes[attributeNames[3]]}
                            options={[
                                { label: __("Default", "easycommerce"), value: "" },
                                { label: __("Normal", "easycommerce"), value: "normal" },
                                { label: __("Italic", "easycommerce"), value: "italic" },
                                { label: __("Oblique", "easycommerce"), value: "oblique" },
                            ]}
                            onChange={(value) =>
                                setAttributes({ [attributeNames[3]]: value })
                            }
                        />
                        <SelectControl
                            label={__("Text Decoration", "easycommerce")}
                            value={attributes[attributeNames[4]]}
                            options={[
                                { label: __("None", "easycommerce"), value: "none" },
                                { label: __("Underline", "easycommerce"), value: "underline" },
                                { label: __("Overline", "easycommerce"), value: "overline" },
                                {
                                    label: __("Line Through", "easycommerce"),
                                    value: "line-through",
                                },
                            ]}
                            onChange={(value) =>
                                setAttributes({ [attributeNames[4]]: value })
                            }
                        />
                        <RangeControl
                            label={__("Line Height", "easycommerce")}
                            value={attributes[attributeNames[5]]}
                            onChange={(newVal) =>
                                setAttributes({ [attributeNames[5]]: newVal })
                            }
                            min={1}
                            max={100}
                        />
                        <RangeControl
                            label={__("Word Spacing", "easycommerce")}
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
