import { __ } from "@wordpress/i18n";
import { PanelBody, TextControl } from "@wordpress/components";
import { InspectorControls } from "@wordpress/block-editor";

const Inspector = (props) => {
    //get attributes
    const { attributes, setAttributes } = props;
    const { GalleryItem } = attributes;

    return (
        <>
            <InspectorControls>
                <PanelBody title={__("Settings", "easycommerce")}>
                    <TextControl
                        label={__("Number of Gallery Item", "easycommerce")}
                        value={GalleryItem}
                        onChange={(value) => {
                            const minValue = 2;
                            const newValue = Math.max(
                                minValue,
                                parseInt(value, 10) || 0
                            );
                            setAttributes({
                                GalleryItem: newValue,
                            });
                        }}
                    />
                </PanelBody>
            </InspectorControls>
        </>
    );
};

export default Inspector;
