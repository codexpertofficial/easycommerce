import { useBlockProps } from "@wordpress/block-editor";
import { useEffect, useState } from "@wordpress/element";

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
                        Only <strong>{productStock}</strong> items left in stock
                    </span>
                )}
            </div>
        </>
    ) : (
        <div {...blockProps}>
            <p>Post type is not product.</p>
        </div>
    );
};

export default Edit;
