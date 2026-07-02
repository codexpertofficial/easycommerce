import { __ } from "@wordpress/i18n";
import { PanelBody, ToggleControl } from "@wordpress/components";
import { InspectorControls } from "@wordpress/block-editor";

const Inspector = (props) => {
    //get attributes
    const { attributes, setAttributes } = props;
    const { showRating, showSku, showFav } = attributes;
    const { isBorder } = attributes;

    return (
        <>
            <InspectorControls>
                <PanelBody title={__("Settings", "easycommerce")}>
                    <ToggleControl
                        checked={showRating}
                        label={__("Show Rating", "easycommerce")}
                        onChange={() =>
                            setAttributes({
                                showRating: !showRating,
                            })
                        }
                    />
                    <ToggleControl
                        checked={showSku}
                        label={__("Show SKU", "easycommerce")}
                        onChange={() =>
                            setAttributes({
                                showSku: !showSku,
                            })
                        }
                    />
                    <ToggleControl
                        checked={showFav}
                        label={__("Show Favourite", "easycommerce")}
                        onChange={() =>
                            setAttributes({
                                showFav: !showFav,
                            })
                        }
                    />
                </PanelBody>
                <PanelBody title={__("Styles", "easycommerce")}>
                    <ToggleControl
                        checked={isBorder}
                        label={__("Border", "easycommerce")}
                        onChange={() =>
                            setAttributes({
                                isBorder: !isBorder,
                            })
                        }
                    />
                </PanelBody>
            </InspectorControls>
        </>
    );
};

export default Inspector;
