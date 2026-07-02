import { useBlockProps } from "@wordpress/block-editor";
import { Slot } from '@wordpress/components';
import Quantity from "./components/quantity";

const Edit = () => {
    const blockProps = useBlockProps();

    // get Product Data
    const postId = wp.data.select("core/editor").getCurrentPostId();
    const postType = wp.data.select("core/editor").getCurrentPostType();

    return postType === "product" ? (
        <div {...blockProps}>
            <div className="easycommerce-add-to-cart-quantity-block mb-8">
                <label className="block text-ec-body font-inter font-normal text-base leading-[26px] mb-4">
                    Quantity
                </label>
                <div className="flex items-center gap-6">
                    <Quantity />
                </div>
            </div>
            <div>
                <button className="w-full font-inter font-semibold text-base leading-[26px] bg-[#1203500D] p-3 rounded-md bg-ec-primary text-white transition-all">
                    Add to cart
                </button>
            </div>
            <Slot name="easycommerce.blocks.add-to-cart.edit" props={{ postId, postType, blockProps }} />
        </div>
    ) : (
        <div {...blockProps}>
            <p>Post type is not product.</p>
        </div>
    );
};

export default Edit;
