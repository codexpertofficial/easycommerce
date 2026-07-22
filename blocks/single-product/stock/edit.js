import { __, _n, sprintf } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";
import { createInterpolateElement, useEffect, useState } from "@wordpress/element";

import "./index.css";
import Inspector from "./inspector";

const Edit = (props) => {
    const [productStock, setProductStock] = useState("");

    // get Product Data
    const postId = wp.data.select("core/editor").getCurrentPostId();
    const postType = wp.data.select("core/editor").getCurrentPostType();

    //get attributes
    const { attributes, setAttributes } = props;
    const { showStock } = attributes;

    useEffect(() => {
        const fetchProducts = async () => {
            try {
                const response = await fetch(
                    EASYCOMMERCE.rest_base + "/products/" + postId
                );
                const data = await response.json();

                setProductStock(data.data.stock);
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
                {showStock && productStock > 0 && (
                    <span className="text-ec-body font-inter text-base leading-[26px]">
                        {createInterpolateElement(
                            sprintf(
                                // translators: %d: number of items left in stock.
                                _n(
                                    "Only <strong>%d</strong> item left in stock",
                                    "Only <strong>%d</strong> items left in stock",
                                    productStock,
                                    "easycommerce"
                                ),
                                productStock
                            ),
                            {
                                strong: <strong />,
                            }
                        )}
                    </span>
                )}
            </div>
        </>
    ) : (
        <div {...blockProps}>
            <p>{__("Post type is not product.", "easycommerce")}</p>
        </div>
    );
};

export default Edit;
