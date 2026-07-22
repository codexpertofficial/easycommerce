import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";
import { useEffect, useState } from "@wordpress/element";

const Edit = () => {
    const [productThumbnail, setProductThumbnail] = useState("");

    // get Product Data
    const postType = wp.data.select("core/editor").getCurrentPostType();
    const postId = wp.data.select("core/editor").getCurrentPostId();

    useEffect(() => {
        const fetchProducts = async () => {
            try {
                const response = await fetch(
                    EASYCOMMERCE.rest_base + "/products/" + postId
                );
                const product = await response.json();

                setProductThumbnail(product.data.thumbnail);
            } catch (error) {
            }
        };
        fetchProducts();
    }, []);

    const blockProps = useBlockProps();
    return postType === "product" ? (
        <div {...blockProps}>
            <div className="w-full h-auto py-[45px] px-[35px] border-[#1203500D] border rounded-xl bg-[#F6F6F6]">
                <img
                    className="w-full h-full object-cover "
                    src={productThumbnail}
                />
            </div>
        </div>
    ) : (
        <div {...blockProps}>
            <p>{__("Post type is not product.", "easycommerce")}</p>
        </div>
    );
};

export default Edit;
