import { __ } from "@wordpress/i18n";
import { PanelBody, ToggleControl } from "@wordpress/components";
import { InspectorControls } from "@wordpress/block-editor";

const Inspector = (props) => {
    //get attributes
    const { attributes, setAttributes } = props;
    const { showRating } = attributes;

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
                </PanelBody>
            </InspectorControls>
        </>
    );
};

export default Inspector;
