import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, SelectControl, Placeholder, Disabled } from "@wordpress/components";
import { useEffect, useRef } from "@wordpress/element";

const ServerSideRender = wp.serverSideRender;

const Notice = () => (
    <Placeholder label={__("Checkout", "easycommerce")}>
        {__("The checkout form renders here on the front end.", "easycommerce")}
    </Placeholder>
);

const Edit = (props) => {
    const { attributes, setAttributes } = props;
    const { template, columns } = attributes;

    const ref = useRef();
    // useBlockProps merges this ref; setting ref on the div replaces it and the block toolbar loses its anchor.
    const blockProps = useBlockProps({ ref });

    // The storefront CSS is scoped to body.easycommerce, a class body_class only adds on the front end.
    useEffect(() => {
        const body = ref.current?.ownerDocument?.body;
        body?.classList.add("easycommerce");
    }, []);

    return (
        <>
            <InspectorControls>
                <PanelBody title={__("Settings", "easycommerce")}>
                    <SelectControl
                        label={__("Template", "easycommerce")}
                        value={template}
                        options={[
                            { label: __("Template 1", "easycommerce"), value: "template-1" },
                            { label: __("Template 2", "easycommerce"), value: "template-2" },
                            { label: __("Template 3", "easycommerce"), value: "template-3" },
                        ]}
                        onChange={(value) => setAttributes({ template: value })}
                    />
                    <SelectControl
                        label={__("Columns", "easycommerce")}
                        value={columns}
                        options={[
                            { label: __("One column", "easycommerce"), value: "1" },
                            { label: __("Two columns", "easycommerce"), value: "2" },
                        ]}
                        onChange={(value) => setAttributes({ columns: value })}
                    />
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                {ServerSideRender ? (
                    // Disabled keeps the previewed checkout form inert inside the canvas.
                    <Disabled>
                        <ServerSideRender
                            block="easycommerce/checkout"
                            attributes={attributes}
                            EmptyResponsePlaceholder={Notice}
                        />
                    </Disabled>
                ) : (
                    <Notice />
                )}
            </div>
        </>
    );
};

export default Edit;
