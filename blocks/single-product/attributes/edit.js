import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";
import { useEffect, useState } from "@wordpress/element";

import "./index.css";

const Edit = (props) => {
    const [productVariations, setProductVariations] = useState([]);
    const [isLoading, setIsLoading] = useState(true);

    // get Product Data
    const postId = wp.data.select("core/editor").getCurrentPostId();
    const postType = wp.data.select("core/editor").getCurrentPostType();

    //get attributes
    const { attributes, setAttributes } = props;

    useEffect(() => {
        const fetchProducts = async () => {
            try {
                const response = await fetch(
                    EASYCOMMERCE.rest_base + "/products/" + postId
                );
                const data = await response.json();
                setIsLoading(false);

                setProductVariations(data.data.variations);
            } catch (error) {;
            }
        };
        fetchProducts();
    }, []);

    const blockProps = useBlockProps();

    const { className, ...restBlockProps } = blockProps;

    // Function to get all common attributes
    const getCommonAttributes = (productVariations) => {
        const commonAttributes = {};

        productVariations.forEach((variation) => {
            const { attributes } = variation;
            attributes.forEach((attr) => {
                if (!commonAttributes[attr.attribute_slug]) {
                    commonAttributes[attr.attribute_slug] = new Map();
                }
                commonAttributes[attr.attribute_slug].set(attr.value_slug, {
                    value_slug: attr.value_slug,
                    value: attr.value,
                    type: attr.type
                });
            });
        });

        const result = {};
        for (const key in commonAttributes) {
            result[key] = Array.from(commonAttributes[key].values());
        }

        return result;
    };

    // Get common attributes
    const commonAttributes = getCommonAttributes(productVariations);

    return postType === "product" ? (
        <>
            <div className={`${className} easycommerce-attributes-wrapper`} {...restBlockProps}>
                {!isLoading &&
                    Object.keys(commonAttributes).map((items, index) => (
                        <div key={index} className={`attribute_${items} easycommerce-vs-wrapper border-b-[1px] mb-3 pb-3 border-ec-table-stock`}>
                            <p className="easycommerce-tax-name mb-2 capitalize">
                                {items}
                            </p>
                            <div className="flex items-center gap-3 mb-6 flex-wrap">
                                {commonAttributes[items].map((item, index) => {
                                    const { value_slug, value, type } = item;
                                    let style = {};
                                    let showText = true;
                                    if (type === 'Color') {
                                        style = { backgroundColor: value };
                                        showText = false;
                                    } else if (type === 'Image') {
                                        style = { backgroundImage: `url(${value})` };
                                        showText = false;
                                    }
                                    return (
                                        <span
                                            key={index}
                                            className={`easycommerce-vs-label easycommerce-vs-content inline-block h-[40px] rounded-lg cursor-pointer border-2 transition-all p-[6px] border-ec-table-stock bg-white type-${type.toLowerCase()}`}
                                        >
                                            <span className="inner-box flex items-center h-full w-full rounded-sm bg-cover bg-center font-medium" style={style}>
                                                {showText && value}
                                            </span>
                                        </span>
                                    );
                                })}
                            </div>
                        </div>
                    ))}
            </div>
        </>
    ) : (
        <div className={className} {...restBlockProps}>
            <p>{__("Post type is not product.", "easycommerce")}</p>
        </div>
    );
};

export default Edit;

{
    /* {Object.keys(commonAttributes).length > 0 &&
        Object.keys(commonAttributes).map((items, index) => (
            <>
                {commonAttributes[items].length > 0 && (
                    <div key={index}>
                         <label className="block text-ec-body font-inter font-normal text-base leading-[26px] mb-4">
                             {items}:
                         </label>
                        <div className="flex items-center gap-3 mb-6 flex-wrap">
                            {commonAttributes[items].map(
                                (item, index) => (
                                     <span
                                         key={index}
                                         className="inline-block py-3 px-5 bg-[#1203500D] rounded-md text-ec-body
                                         font-inter text-base leading-[26px] font-semibold cursor-pointer
                                         hover:bg-ec-primary hover:text-white transition-all"
                                     >
                                        {item}
                                    </span>
                                )
                            )}
                        </div>
                    </div>
                )}
            </>
        ))} */
}
