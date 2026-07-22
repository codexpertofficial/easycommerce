import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";
import { useEffect } from "@wordpress/element";
import Inspector from "./inspector";

const Edit = (props) => {
    // get Product Data
    const postId = wp.data.select("core/editor").getCurrentPostId();
    const postType = wp.data.select("core/editor").getCurrentPostType();

    //get attributes
    const { attributes } = props;

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
            {/* <Inspector {...props} /> */}
            <div {...blockProps}>
                <div className="flex items-center mb-2">
                   <p className="font-inter font-normal text-sm leading-7 text-[#120350] mr-4">
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
                        when an unknown printer took a galley of type and scrambled it to make a type specimen book.
                        It has survived not only five centuries, but also the leap into electronic typesetting,
                        remaining essentially unchanged.
                   </p>
                </div>
            </div>
        </>
    ) : (
        <div {...blockProps}>
            <p>{__("Post type is not product.", "easycommerce")}</p>
        </div>
    );
};

export default Edit;
