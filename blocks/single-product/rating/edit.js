import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";
import StarRating from "./components/rating";
import Inspector from "./inspector";
import { useEffect, useState } from "react";

const Edit = (props) => {
    const [rating, setRating] = useState("");

    // get Product Data
    const postType = wp.data.select("core/editor").getCurrentPostType();
    const postId = wp.data.select("core/editor").getCurrentPostId();

    //get attributes
    const { attributes } = props;
    const { showRating } = attributes;
    const { isBorder } = attributes;

    useEffect(() => {
        const fetchProducts = async () => {
            fetch(EASYCOMMERCE.rest_base + "/products/" + postId)
                .then((res) => res.json())
                .then((data) => {
                    setRating(data.data.rating);
                });
        };
        fetchProducts();
    }, []);

    const blockProps = useBlockProps();

    return postType === "product" ? (
        <>
            <Inspector {...props} />
            <div {...blockProps}>
                {rating && (
                    <>
                        <div
                            className="flex items-center gap-4"
                            style={{
                                borderBottom: isBorder
                                    ? "1px solid var(--color-ec-border)"
                                    : "",
                                paddingBottom: isBorder ? "24px" : "",
                            }}
                        >
                            {showRating && <StarRating rating={5} />}
                        </div>
                    </>
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
