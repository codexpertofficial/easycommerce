import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";
import { useEffect, useState } from "@wordpress/element";
import Inspector from "./inspector";
import parse from "html-react-parser";
import Review from "./components/review";

const Edit = (props) => {
    // get Product Data
    const postType = wp.data.select("core/editor").getCurrentPostType();
    const postId = wp.data.select("core/editor").getCurrentPostId();
    const [productDetails, setProductDetails] = useState("");

    //get attributes
    const { attributes } = props;
    const { showRating } = attributes;

    useEffect(() => {
        const fetchProducts = async () => {
            try {
                const response = await fetch(
                    EASYCOMMERCE.rest_base + "/products/" + postId
                );
                const data = await response.json();

                setProductDetails(data.data.description);
            } catch (error) {
            }
        };
        fetchProducts();
    }, []);

    const [activeTab, setActiveTab] = useState("details");
    const tabs = [
        { key: "details", label: __("Product Details", "easycommerce") },
        { key: "review", label: __("Review", "easycommerce") },
    ];

    const blockProps = useBlockProps();

    return postType === "product" ? (
        <>
            <Inspector {...props} />
            <div {...blockProps}>
                <div className="easycommerce-product-details">
                    <div className="flex border-b gap-12 border-[#DBDBDB] ">
                        {tabs.map((tab, index) => (
                            <button
                                key={index}
                                className={`py-2 font-inter font-normal text-base leading-[26px] focus:outline-none first:pl-0 last:pr-0 -mb-[1px] ${
                                    activeTab === tab.key
                                        ? "border-b border-ec-primary text-ec-primary"
                                        : "text-ec-body hover:text-ec-primary"
                                }`}
                                onClick={() => setActiveTab(tab.key)}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>
                    <div className="mt-8">
                        {activeTab === "details" && (
                            <>{parse(productDetails)}</>
                        )}
                        {activeTab === "review" && <Review postId={postId} />}
                    </div>
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
