import { __ } from "@wordpress/i18n";
import { PanelBody, ToggleControl } from "@wordpress/components";
import { InspectorControls } from "@wordpress/block-editor";

const Inspector = (props) => {
    //get attributes
    const { attributes, setAttributes } = props;
    const { taxInclued } = attributes;

    return (
        <>
            <InspectorControls>
                <PanelBody title={__("Settings", "easycommerce")}>
                    {/* <ToggleControl
                        checked={taxInclued}
                        label={__("Show Tax included", "easycommerce")}
                        onChange={() =>
                            setAttributes({
                                taxInclued: !taxInclued,
                            })
                        }
                    /> */}
                </PanelBody>
            </InspectorControls>
        </>
    );
};

export default Inspector;
