import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";
import { useEffect, useState } from "@wordpress/element";

import Inspector from "./inspector";

const Edit = (props) => {
    const [productTitle, setProductTitle] = useState("");

    // get Product Data
    const postId = wp.data.select("core/editor").getCurrentPostId();
    const postType = wp.data.select("core/editor").getCurrentPostType();

    //get attributes
    const { attributes, setAttributes } = props;

    const { color } = attributes;
    const { decoration } = attributes;
    const { fontSize } = attributes;
    const { fontWeight } = attributes;
    const { lineHeight } = attributes;
    const { spacing } = attributes;
    const { textTransform } = attributes;
    const { style } = attributes;

    useEffect(() => {
        const fetchProducts = async () => {
            try {
                const response = await fetch(
                    EASYCOMMERCE.rest_base + "/products/" + postId
                );
                const data = await response.json();

                setProductTitle(data.data.title);
            } catch (error) {
            }
        };
        fetchProducts();
    }, []);

    const blockProps = useBlockProps();

    return postType === "product" ? (
        <>
            <Inspector {...props} />
            <div {...blockProps}>
                <h2>
                    <span
                        className="easycommerce-product-title text-ec-body font-inter font-medium text-[30px] leading-10"
                        style={{
                            color: color,
                            textDecoration: decoration,
                            fontSize: fontSize + "px",
                            fontWeight: fontWeight,
                            lineHeight: lineHeight + "px",
                            letterSpacing: spacing,
                            textTransform: textTransform,
                            fontStyle: style,
                        }}
                    >
                        {productTitle}
                    </span>
                </h2>
            </div>
        </>
    ) : (
        <div {...blockProps}>
            <p>{__("Post type is not product.", "easycommerce")}</p>
        </div>
    );
};

export default Edit;
