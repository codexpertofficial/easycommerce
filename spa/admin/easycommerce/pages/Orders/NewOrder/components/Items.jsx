import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import { useDispatch } from "react-redux";

// redux slice
import { addToastData } from "../../../../redux-store/slices/toastSlice";

// icons
const addItemIcon = `${EASYCOMMERCE.assets}admin/img/icons/item-add-icon.png`;
const deleteItemIcon = `${EASYCOMMERCE.assets}admin/img/icons/item-delete-icon.png`;
const deleteHoverItemIcon = `${EASYCOMMERCE.assets}admin/img/icons/item-delete-hover-icon.png`;

const Items = ({ user_id, setCartItemsHash }) => {
    const dispatch = useDispatch();
    const [products, setProducts] = useState([]);
    const [suggestions, setSuggestions] = useState([]);
    const [showSuggestion, setShowSuggestion] = useState(false);
    const [currentIndex, setCurrentIndex] = useState(null);

    const handleProductTitleChange = (e, index) => {
        const productName = e.target.value;

        updateProductTitle(productName, index);

        if (productName.length < 3) {
            return;
        }

        fetch(
            `${EASYCOMMERCE.rest_base}/products?s=${productName}&show_prices=true`,{
                headers: {
                    "Content-Type": "application/json",
                    "X-WP-Nonce": EASYCOMMERCE.nonce,
                },
            }
        )
            .then((res) => res.json())
            .then((data) => {
                if (data.success && data.data?.products) {
                    setSuggestions(data.data.products);
                    setShowSuggestion(true);
                }
            });
    };

    const updateProductTitle = (title, index) => {
        const updatedProducts = products.map((product, i) => {
            if (i === index) {
                return {
                    ...product,
                    title: title,
                };
            }

            return product;
        });

        setProducts(updatedProducts);
    };

    const selectProduct = (item, index) => {
        const updatedProducts = products.map((product, i) => {
            if (i === index) {
                const attributes = Object.keys(item.attributes);

                // if no attributes are available set price id to 0
                if (attributes.length === 0) {
                    return {
                        id: item.id,
                        title: item.title,
                        quantity: 0,
                        price: 0,
                        price_id: 0,
                        attributes: {},
                    };
                } else {
                    // convert it into a object with empty value
                    const updatedAttributes = attributes.reduce((acc, curr) => {
                        return {
                            ...acc,
                            [curr]: "",
                        };
                    }, {});

                    return {
                        id: item.id,
                        title: item.title,
                        quantity: 0,
                        price: 0,
                        price_id: updatedAttributes,
                        attributes: item.attributes,
                    };
                }
            }

            return product;
        });

        setProducts(updatedProducts);

        setShowSuggestion(false);
        setSuggestions([]);
        setCurrentIndex(null);
    };

    const handleQuantityChange = (qty, productId) => {
        // first check if price_id is not 0 and price_id object has no empty key value pair
        if (qty === 0) {
            return;
        }

        const priceId = products.find(
            (product) => product.id === productId
        ).price_id;

        const emptyKeys = Object.keys(priceId).filter(
            (key) => priceId[key] === ""
        );

        if (emptyKeys.length > 0) {
            dispatch(
                addToastData({
                    type: "error",
                    message: __("Select all attributes", "easycommerce"),
                })
            );

            return;
        }

        const params = {
            attributes: priceId,
        };

        const queryString = new URLSearchParams(params.attributes).toString();

        fetch(
            `${EASYCOMMERCE.rest_base}/products/${productId}/variations?${queryString}`,
            {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "X-WP-Nonce": EASYCOMMERCE.nonce,
                },
            }
        )
            .then((res) => res.json())
            .then((data) => {
                if (data.success && data.data?.variations) {
                    const updatedProducts = products.map((product) => {
                        if (product.id === productId) {
                            return {
                                ...product,
                                quantity: Number(qty),
                                price:
                                    Number(qty) *
                                        parseFloat(
                                            data.data.variations[0].sale_price
                                        ) ||
                                    Number(qty) *
                                        parseFloat(
                                            data.data.variations[0].price
                                        ),
                            };
                        }

                        return product;
                    });

                    setProducts(updatedProducts);
                } else {
                    dispatch(
                        addToastData({
                            type: "error",
                            message: __("No Attributes found!", "easycommerce"),
                        })
                    );
                }
            });
    };

    const handleAttributeChange = (key, value, productIndex) => {
        if (!key || !value) {
            return;
        }

        const updatedProducts = products.map((product, index) => {
            if (index === productIndex) {
                return {
                    ...product,
                    price_id: {
                        ...product.price_id,
                        [key]: value,
                    },
                };
            }

            return product;
        });

        setProducts(updatedProducts);
    };

    const handleAddToCart = () => {
        if (!user_id) {
            dispatch(
                addToastData({
                    type: "error",
                    message: __("Please select a customer before adding to cart", "easycommerce"),
                })
            );

            return;
        }

        // find if product quantity is empty
        const emptyProducts = products.filter((product) => {
            return product.quantity === 0;
        });

        if (emptyProducts.length > 0) {
            dispatch(
                addToastData({
                    type: "error",
                    message: __("Please select quantity for all products", "easycommerce"),
                })
            );

            return;
        }

        const cartData = {
            user_id: user_id,
            products: products.map((product) => {
                return {
                    id: product.id,
                    price_id: product.price_id,
                    quantity: product.quantity,
                };
            }),
        };

        easycommerce_modal(true);

        fetch(`${EASYCOMMERCE.rest_base}/cart`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify(cartData),
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success && data.data?.hash) {
                    dispatch(
                        addToastData({
                            type: "success",
                            message: __("Product added to cart", "easycommerce"),
                        })
                    );

                    setCartItemsHash(data.data.hash);
                }
            });
    };

    const addProduct = (index = null) => {
        // if index is null, add product at the end or else add product after that index
        if (index === null) {
            setProducts([
                ...products,
                {
                    id: null,
                    title: "",
                    price_id: {},
                    quantity: 0,
                    price: 0,
                    attributes: {},
                },
            ]);
        } else {
            setProducts([
                ...products.slice(0, index + 1),
                {
                    id: null,
                    title: "",
                    price_id: {},
                    quantity: 0,
                    price: 0,
                    attributes: {},
                },
                ...products.slice(index + 1),
            ]);
        }
    };

    const removeProduct = (productIndex) => {
        setProducts(products.filter((_, index) => index !== productIndex));
    };

    return (
        <>
            <div className="w-full px-4 py-6 mb-6 border-b border-[#DBDBDB] flex flex-col justify-center">
                <h3 className="text-ec-body font-inter font-semibold text-xl leading-8">
                    {__("Items", "easycommerce")}
                </h3>
            </div>

            <div className="p-4">
                {products.length > 0 && (
                    <div className="px-[18px] flex justify-between gap-[6px]">
                        <div className="w-[65%]">
                            <label className="text-ec-body block font-inter font-medium text-xs leading-5 mb-[6px]">
                                {__("Product name", "easycommerce")}
                            </label>
                        </div>
                        <div className="w-[10%]">
                            <label className="text-ec-body block font-inter font-medium text-xs leading-5 mb-[6px]">
                                {__("QTY", "easycommerce")}
                            </label>
                        </div>
                        <div className="w-[10%]">
                            <label className="text-ec-body block font-inter font-medium text-xs leading-5 mb-[6px]">
                                {__("Prices", "easycommerce")}
                            </label>
                        </div>

                        <div className="w-[15%]"></div>
                    </div>
                )}

                {/* Items */}
                {products.length > 0 &&
                    products.map((item, productIndex) => {
                        return (
                            <div
                                key={productIndex}
                                className="p-[18px] flex justify-evenly gap-[6px] items-start border-b border-ec-border"
                            >
                                <div className="w-[63%] relative flex flex-col gap-3">
                                    <input
                                        onFocus={() => {
                                            setShowSuggestion(true);
                                            setCurrentIndex(productIndex);
                                        }}
                                        onBlur={() => {
                                            setTimeout(() => {
                                                setShowSuggestion(false);
                                                setCurrentIndex(null);
                                            }, 150);
                                        }}
                                        onChange={(e) =>
                                            handleProductTitleChange(
                                                e,
                                                productIndex
                                            )
                                        }
                                        type="text"
                                        value={item.title}
                                        placeholder={__("Product name here", "easycommerce")}
                                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm 
                                        placeholder:text-sm font-inter text-sm text-ec-body"
                                    />
                                    {showSuggestion &&
                                        currentIndex === productIndex &&
                                        suggestions.length > 0 && (
                                            <ul
                                                className="absolute right-0 top-12 z-10 w-full bg-white border border-ec-border p-3 
                                                rounded-xl"
                                            >
                                                {suggestions.map(
                                                    (item, index) => {
                                                        return (
                                                            <li
                                                                key={index}
                                                                className="p-3 font-inter font-normal text-base leading-[26px] 
                                                                hover:bg-[#F8F8F8] rounded-[4px] cursor-pointer mb-0"
                                                                onMouseDown={() => {
                                                                    selectProduct(
                                                                        item,
                                                                        currentIndex
                                                                    );
                                                                }}
                                                            >
                                                                {item.title}
                                                            </li>
                                                        );
                                                    }
                                                )}
                                            </ul>
                                        )}
                                    <div className="w-full flex flex-wrap justify-start items-center gap-2">
                                        {products[productIndex]?.attributes &&
                                            Object.keys(
                                                products[productIndex]
                                                    .attributes
                                            ).map((attr) => (
                                                <select
                                                    className="easycommerce-new-order-attribute h-[42px]"
                                                    key={attr}
                                                    value={
                                                        products[productIndex]
                                                            .price_id[attr]
                                                    }
                                                    onChange={(e) =>
                                                        handleAttributeChange(
                                                            attr,
                                                            e.target.value,
                                                            productIndex
                                                        )
                                                    }
                                                >
                                                    <option value="">
                                                        {attr}
                                                    </option>
                                                    {products[
                                                        productIndex
                                                    ].attributes[attr].map(
                                                        (value, index) => (
                                                            <option
                                                                key={index}
                                                                value={value}
                                                            >
                                                                {value}
                                                            </option>
                                                        )
                                                    )}
                                                </select>
                                            ))}
                                    </div>
                                </div>
                                <div className="w-[10%]">
                                    <input
                                        type="number"
                                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm 
                                        placeholder:text-base placeholder:leading-[26px] font-inter text-base text-ec-body 
                                        leading-[26px]"
                                        placeholder="0"
                                        step={1}
                                        min={0}
                                        value={item.quantity}
                                        onChange={(e) =>
                                            handleQuantityChange(
                                                Number(e.target.value),
                                                item.id
                                            )
                                        }
                                        disabled={item.id === null}
                                    />
                                </div>
                                <div className="w-[12%]">
                                    <input
                                        type="text"
                                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm 
                                        placeholder:text-base placeholder:leading-[26px] font-inter text-base text-ec-body 
                                        leading-[26px] read-only:bg-white read-only:text-ec-body"
                                        placeholder="00.00"
                                        value={item.price}
                                        readOnly
                                    />
                                </div>
                                <div className="w-[15%] h-[42px] flex justify-end items-center gap-2">
                                    <button
                                        className="w-8 h-8 flex items-center justify-center bg-[#F8F8F8] hover:bg-[#e1e1fd] rounded"
                                        title={__("Add new item", "easycommerce")}
                                        onClick={() => addProduct(productIndex)}
                                    >
                                        <img
                                            src={addItemIcon}
                                            alt={__("icon", "easycommerce")}
                                            className="w-[14px] h-4 pointer-events-none"
                                        />
                                    </button>
                                    <button
                                        className="w-8 h-8 group flex items-center justify-center bg-[#F8F8F8] 
                                        hover:bg-[#FF3A520D] rounded"
                                        title={__("Delete item", "easycommerce")}
                                        onClick={() =>
                                            removeProduct(productIndex)
                                        }
                                    >
                                        <img
                                            src={deleteItemIcon}
                                            alt={__("icon", "easycommerce")}
                                            className="w-[10px] h-[14px] pointer-events-none group-hover:hidden block"
                                        />
                                        <img
                                            src={deleteHoverItemIcon}
                                            alt={__("icon", "easycommerce")}
                                            className="w-[10px] h-[14px] pointer-events-none group-hover:block hidden"
                                        />
                                    </button>
                                </div>
                            </div>
                        );
                    })}

                <div
                    className={`flex ${
                        products.length > 0 ? "justify-end" : "justify-center"
                    } mt-6`}
                >
                    {products.length > 0 && (
                        <button
                            onClick={handleAddToCart}
                            type="button"
                            className="flex justify-center items-center gap-[8px] font-inter bg-white group border border-ec-primary py-[11px] px-4 rounded-lg text-ec-primary hover:text-white hover:bg-ec-primary focus:shadow-none focus:text-white focus:bg-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out duration-500"
                        >
                            {__("Add to Cart", "easycommerce")}
                        </button>
                    )}

                    {products.length === 0 && (
                        <button
                            type="button"
                            className="flex justify-center items-center gap-[8px] font-inter bg-white group border border-ec-primary py-[11px] px-4 rounded-lg text-ec-primary hover:text-white hover:bg-ec-primary focus:shadow-none focus:text-white focus:bg-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out duration-500"
                            onClick={addProduct}
                        >
                            <svg
                                class="w-4 h-4 font-medium"
                                data-slot="icon"
                                fill="none"
                                stroke-width="1.5"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 4.5v15m7.5-7.5h-15"
                                ></path>
                            </svg>
                            <span>{__("Add Product", "easycommerce")}</span>
                        </button>
                    )}
                </div>
            </div>
        </>
    );
};

export default Items;
