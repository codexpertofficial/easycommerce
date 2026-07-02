import { __ } from "@wordpress/i18n";
import { PanelBody, ToggleControl } from "@wordpress/components";
import { InspectorControls } from "@wordpress/block-editor";

const Inspector = (props) => {
    //get attributes
    const { attributes, setAttributes } = props;
    const { showStock } = attributes;

    return (
        <>
            <InspectorControls>
                <PanelBody title={__("Settings", "easycommerce")}>
                </PanelBody>
            </InspectorControls>
        </>
    );
};

export default Inspector;
