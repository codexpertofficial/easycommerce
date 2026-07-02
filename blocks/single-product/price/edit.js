import { useBlockProps } from "@wordpress/block-editor";
import { useEffect } from "@wordpress/element";
import Inspector from "./inspector";

const Edit = (props) => {
    // get Product Data
    const postId = wp.data.select("core/editor").getCurrentPostId();
    const postType = wp.data.select("core/editor").getCurrentPostType();

    //get attributes
    const { attributes } = props;
    const { taxInclued } = attributes;

    useEffect(() => {
        const fetchProducts = async () => {
            try {
                const response = await fetch(
                    EASYCOMMERCE.rest_base + "/products/" + postId
                );
                const data = await response.json();
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
                <div className="flex items-center mb-2">
                    <price className="font-inter font-bold text-xl leading-7 text-[#120350] mr-4">
                        $10.00
                    </price>
                    <del className="text-ec-secondary font-inter font-normal text-lg leading-6">
                        $600.00
                    </del>
                </div>
            </div>
        </>
    ) : (
        <div {...blockProps}>
            <p>Post type is not product.</p>
        </div>
    );
};

export default Edit;
