import React from "react";
import { __ } from "@wordpress/i18n";
import { useDispatch } from "react-redux";

// redux slice
import { addToastData } from "../../../../redux-store/slices/toastSlice";

const Total = ({ order }) => {
    const dispatch = useDispatch();

    const createOrder = () => {
        if (!order?.customer) {
            dispatch(
                addToastData({
                    type: "error",
                    message: __("Please select a customer", "easycommerce"),
                })
            );
            return;
        }

        if (!order?.items) {
            dispatch(
                addToastData({
                    message: __("Please add items to cart", "easycommerce"),
                    type: "error",
                })
            );
            return;
        }

        easycommerce_modal(true);

        fetch(`${EASYCOMMERCE.rest_base}/orders`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify(order),
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success) {
                    dispatch(
                        addToastData({
                            type: "success",
                            message: __("Order created successfully", "easycommerce"),
                        })
                    );

                    window.location.hash = `#/orders`;
                } else {
                    dispatch(
                        addToastData({
                            type: "error",
                            message: __("Failed to create order", "easycommerce"),
                        })
                    );
                }
            });
    };

    return (
        <>
            <div className="w-full px-4 py-6 mb-6 border-b border-[#DBDBDB] flex flex-col justify-center">
                <h3 className="text-ec-body font-inter font-semibold text-xl leading-8">
                    {__("Total", "easycommerce")}
                </h3>
            </div>

            <div className="px-4 pb-4">
                <div
                    className="py-5 px-3 flex items-center justify-between border-b 
                    border-ec-border border-dotted"
                >
                    <label className="text-ec-body block text-sm font-normal leading-4 font-inter">
                        {__("Item Total", "easycommerce")}
                    </label>
                    <span className="text-ec-body block font-inter font-medium text-sm leading-4">
                        $12.90
                    </span>
                </div>
                <div
                    className="py-5 px-3 flex items-start xl:items-center justify-between border-b 
                    border-ec-border border-dotted"
                >
                    <div className="flex flex-col xl:flex-row gap-3 items-start xl:items-center">
                        <label className="text-ec-body block text-sm font-normal leading-4 font-inter">
                            {__("Shipping", "easycommerce")}
                        </label>
                        <select className="easycommerce-order-input easycommerce-order-select">
                            <option value="flat discount">{__("Flat discount", "easycommerce")}</option>
                            <option value="Another discount">
                                {__("Another discount", "easycommerce")}
                            </option>
                        </select>
                    </div>
                    <span className="text-ec-body block font-inter font-medium text-sm leading-4">
                        $12.90
                    </span>
                </div>
                <div
                    className="py-5 px-3 flex items-center justify-between border-b 
                    border-ec-border border-dotted"
                >
                    <label className="text-ec-body block text-sm font-normal leading-4 font-inter">
                        {__("Discount", "easycommerce")}
                    </label>
                    <span className="text-ec-body block font-inter font-medium text-sm leading-4">
                        <span className="mr-1">$</span>
                        <input
                            type="text"
                            className="easycommerce-order-input w-[80px] h-[32px] border border-ec-border 
                            rounded-sm placeholder:text-base placeholder:leading-[26px] font-inter"
                        />
                    </span>
                </div>
                <div
                    className="py-5 px-3 flex items-start xl:items-center justify-between border-b 
                    border-ec-border border-dotted"
                >
                    <div className="flex flex-col xl:flex-row gap-3 items-start xl:items-center">
                        <label className="text-ec-body text-sm font-normal leading-4 font-inter">
                            {__("Coupon", "easycommerce")}
                        </label>
                        <div>
                            <input
                                type="text"
                                className="easycommerce-order-input w-[120px]"
                                placeholder={__("Coupon code", "easycommerce")}
                            />
                            <button
                                type="button"
                                className="py-2 px-[10px] text-ec-secondary bg-[#F8F8F8] font-inter font-normal text-sm leading-[26px]"
                            >
                                {__("Apply", "easycommerce")}
                            </button>
                        </div>
                    </div>
                    <span className="text-ec-body block font-inter font-medium text-sm leading-4">
                        $12.90
                    </span>
                </div>
                <div
                    className="py-5 px-3 flex items-center justify-between border-b 
                    border-ec-border border-dotted"
                >
                    <label className="text-ec-body block text-sm font-normal leading-4 font-inter">
                        {__("Tax", "easycommerce")}
                    </label>
                    <span className="text-ec-body block font-inter font-medium text-sm leading-4">
                        $12.90
                    </span>
                </div>
                <div className="py-5 px-3 flex items-center justify-between border-t-2 border-ec-border">
                    <label className="text-ec-body block text-base font-medium leading-4 font-inter">
                        {__("Total offer", "easycommerce")}
                    </label>
                    <span className="text-ec-body block font-inter font-semibold text-base leading-4">
                        $4312.90
                    </span>
                </div>
                <div className="flex flex-col xl:flex-row items-center justify-between gap-3 mt-6">
                    <button
                        type="button"
                        className="w-full xl:w-1/2 flex justify-center items-center gap-[8px] font-inter 
                        bg-white group border border-ec-primary py-[11px] px-4 rounded-lg text-ec-primary 
                        hover:text-white hover:bg-ec-primary focus:shadow-none focus:text-white 
                        focus:bg-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out 
                        duration-500"
                        onClick={() => (window.location.hash = `#/orders`)}
                    >
                        {__("Cancel Order", "easycommerce")}
                    </button>
                    <button
                        type="button"
                        className="w-full xl:w-1/2 flex justify-center items-center gap-[8px] font-inter 
                        bg-ec-primary group border border-ec-primary py-[11px] px-4 rounded-lg text-white 
                        hover:text-white hover:bg-ec-secondary focus:shadow-none focus:text-white 
                        focus:bg-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out 
                        duration-500"
                        onClick={createOrder}
                    >
                        {__("Create Order", "easycommerce")}
                    </button>
                </div>
            </div>
        </>
    );
};

export default Total;
