import React, { useEffect, useState } from "react";
import { useDispatch } from "react-redux";

import { __ } from "@wordpress/i18n";

import { addToastData } from "../../../redux-store/slices/toastSlice";

import "./style.css";

// Components
import General from "./elements/General";
import Offers from "./elements/Offers";
import Settings from "./elements/Settings";
import FormSkeleton from "./elements/FormSkeleton";
import Dropdown from "../../../../common/components/inputs/Dropdown";

const CouponDetails = ({ id = null, setBreadcrumbTitle }) => {
    const [ currentStatus, setCurrentStatus ] = useState('active');
    const dispatch = useDispatch();
    const [isLoading, setIsLoading] = useState(id ? true : false);
    const [couponData, setCouponData] = useState({
        id: id,
        active: true,
        name: "",
        code: "",
        offer: null,
        type: "percentage",
        rules: {
            products: [],
            min_spend: "",
            max_spend: "",
            start_date: "",
            end_date: "",
        },
    });

    const statusOptions = [
        { value: "active", label: __("Active", "easycommerce") },
        { value: "inactive", label: __("Inactive", "easycommerce") },
    ];

    const handleCouponData = (key, value) => {
        setCouponData(prevState => {
            return {...prevState, [key]: value};
        });
    };

    const handleCouponRules = (key, value) => {
        setCouponData({
            ...couponData,
            rules: { ...couponData.rules, [key]: value },
        });
    };

    const handleAddProduct = (product) => {
        setCouponData({
            ...couponData,
            rules: {
                ...couponData.rules,
                products: [...couponData.rules.products, product],
            },
        });
    };

    const handleRemoveProduct = (index) => {
        setCouponData({
            ...couponData,
            rules: {
                ...couponData.rules,
                products: couponData.rules.products.filter(
                    (_, i) => i !== index
                ),
            },
        });
    };

    const handleFreeProduct = (product) => {
        setCouponData({
            ...couponData,
            offer: [...couponData.offer ?? [], product],
        });
    };

    const handleRemoveFreeProduct = (index) => {
        setCouponData({
            ...couponData,
            offer: [].filter.call(
                couponData.offer,
                (_, i) => i !== index
            ),
        });
    }

    const validateCoupon = (data) => {
        if (!data.name) return { message: __("Name is required", "easycommerce") };
        if (!data.code) return { message: __("Code is required", "easycommerce") };
        if (!data.offer && data.type !== 'free_shipping') return { message: __("offer is required", "easycommerce") };

        return true;
    };

    const convertRulesToArray = (rules) => {
        return Object.keys(rules).map((key) => {
            return { type: key, value: rules[key] };
        });
    };

    const convertCouponResponse = (apiResponse) => {
        const transformedResponse = {
            id: apiResponse.id,
            active: apiResponse.active,
            name: apiResponse.name || "",
            code: apiResponse.code || "",
            offer: apiResponse.offer || null,
            type: apiResponse.type || "percentage",
            rules: {
                products: [],
                min_spend: "",
                max_spend: "",
                start_date: "",
                end_date: "",
            },
        };

        apiResponse.rules.forEach((rule) => {
            switch (rule.type) {
                case "products":
                    transformedResponse.rules.products = JSON.parse(
                        rule.value
                    ).map((item) => ({
                        id: item.id,
                        title: item.title,
                    }));
                    break;
                case "min_spend":
                    transformedResponse.rules.min_spend = JSON.parse(
                        rule.value
                    );
                    break;
                case "max_spend":
                    transformedResponse.rules.max_spend = JSON.parse(
                        rule.value
                    );
                    break;
                case "start_date":
                    transformedResponse.rules.start_date = JSON.parse(
                        rule.value
                    );
                    break;
                case "end_date":
                    transformedResponse.rules.end_date = JSON.parse(rule.value);
                    break;
            }
        });

        return transformedResponse;
    };

    const handleSaveCoupon = () => {
        const url = id
            ? `${EASYCOMMERCE.rest_base}/coupons/${id}`
            : `${EASYCOMMERCE.rest_base}/coupons`;
        const data = {
            ...couponData,
            rules: convertRulesToArray(couponData.rules),
        };

        if (!id) delete data.id;

        const validation = validateCoupon(data);

        if (validation !== true) {
            dispatch(
                addToastData({ type: "error", message: validation.message })
            );
            return;
        }

        easycommerce_modal(true);
        fetch(url, {
            method: id ? "PUT" : "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify(data),
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success && data.data?.id) {
                    dispatch(
                        addToastData({
                            type: "success",
                            message: data.data.message,
                        })
                    );

                    window.location.href = "#/coupons";
                } else {
                    dispatch(
                        addToastData({
                            type: "error",
                            message: data.data.message,
                        })
                    );
                }
            });
    };

    useEffect(() => {
        if (!id) return;

        setIsLoading(true);
        fetch(`${EASYCOMMERCE.rest_base}/coupons/${id}`,{
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            }
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success && data.data?.id) {
                    const convertedData = convertCouponResponse(data.data);
                    setCouponData(convertedData);
                    setCurrentStatus(convertedData.active ? "active" : "inactive");
                    setBreadcrumbTitle(data.data.code)
                }
                setIsLoading(false);
            });
    }, [id]);

    if (!couponData) return null;

    return (
        <>
            <div className="flex justify-between items-center mb-4">
                <div className="product-panel-title w-full">
                    <h3>
                        {!id ? __("Create Coupon", "easycommerce") : __("Edit Coupon", "easycommerce")}
                    </h3>
                </div>

                <div className="flex items-center justify-end gap-3">
                    

                    <div className="w-[100px] h-[41px] bg-white rounded-lg ec-product-status-dropdown">
                        <Dropdown
                            options={statusOptions}
                            value={couponData.active ? "active" : "inactive"}
                            placeholder={__("Select status", "easycommerce")}
                            onChange={(selectedOption) => {
                                const isActive = selectedOption.value === "active";
                                handleCouponData("active", isActive);
                                setCurrentStatus(selectedOption.value);
                            }}
                            minWidthClass="w-full"
                        />
                    </div>

                    <button
                        className="easycommerce-primary-button h-[41px] px-[22px]"
                        onClick={handleSaveCoupon}
                    >
                        {currentStatus === 'inactive'
                            ? __('Save as Inactive', 'easycommerce')
                            : __('Save as Active', 'easycommerce')}
                    </button>
                </div>
            </div>

            {!isLoading ? (
                <div className="border border-solid border-ec-table-stock rounded-xl min-h-screen">
                    <div className="grid grid-cols-2 gap-6">
                        <div className="flex flex-col gap-6">
                            <General
                                couponData={couponData}
                                handleCouponData={handleCouponData}
                            />
                            <Offers
                                couponData={couponData}
                                handleCouponData={handleCouponData}
                                handleFreeProduct={handleFreeProduct}
                                handleRemoveFreeProduct={handleRemoveFreeProduct}
                            />
                        </div>
                        <Settings
                            couponData={couponData}
                            handleCouponData={handleCouponRules}
                            handleAddProduct={handleAddProduct}
                            handleRemoveProduct={handleRemoveProduct}
                        />
                    </div>
                </div>
            ) : (
                <FormSkeleton />
            )}
        </>
    );
};

export default CouponDetails;
