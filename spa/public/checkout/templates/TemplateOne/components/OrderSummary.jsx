import React from "react";
import { applyFilters } from '@wordpress/hooks';

//Images and Icons
const OrderSummaryImage = `${EASYCOMMERCE.assets}public/img/checkout/order-summary.png`;

const OrderSummary = () => {
    /**
     * Filters the order summary items displayed in the checkout.
     *
     * @since 1.0.0
     * @param {Array} orderSummaryItems Array of order summary item objects.
     */
    const defaultOrderSummaryItems = [
        {
            label: "Subtotal",
            price: "$334.00",
        },
        {
            label: "Shipping Eastimate",
            price: "$5.00",
        },
        {
            label: "Tax Eastimate",
            price: "$25.00",
        },
    ];
    const orderSummaryItems = applyFilters('easycommerce.checkout.order.summary.items', defaultOrderSummaryItems);

    /**
     * Filters the order total displayed in the checkout.
     *
     * @since 1.0.0
     * @param {string} orderTotal The order total string.
     */
    const orderTotal = applyFilters('easycommerce.checkout.order.total', "$2345.00");

    return (
        <>
            <div className="border border-ec-border bg-white rounded-lg p-[30px] mb-5">
                <div className="flex items-center mb-[30px]">
                    <img
                        src={OrderSummaryImage}
                        className="w-[53px] h-[53px] mr-4"
                    />
                    <h3
                        className="easycommerce-billing-title text-ec-body font-inter leading-8 font-semibold 
                    text-xl mb-0"
                    >
                        Order Summary
                    </h3>
                </div>
                <div>
                    <div className="easycommerce-coupon-wrapper relative mb-4">
                        <input type="text" placeholder="Discount code" />
                        <button
                            type="button"
                            className="absolute top-[9px] right-[9px] py-[7px] px-5 border border-ec-border 
                        rounded-[6px] bg-[#F8F8F8] text-base font-inter font-normal leading-[26px] shadow-none 
                        hover:bg-ec-secondary hover:border-ec-border hover:rounded-[6px] hover:text-white"
                        >
                            Apply
                        </button>
                    </div>
                    {orderSummaryItems.map((items, index) => (
                        <div className="flex items-center justify-between p-4 border-b border-dotted border-ec-border">
                            <label className="text-ec-body font-inter font-normal text-base leading-[26px]">
                                {items.label}
                            </label>
                            <span className="mb-0 text-ec-body font-inter font-medium text-base leading-[26px]">
                                {items.price}
                            </span>
                        </div>
                    ))}
                    <div className="flex items-center justify-between p-4 border-t border-ec-border">
                        <label className="text-ec-body font-inter font-bold text-base leading-[26px]">
                            Order Total
                        </label>
                        <span className="mb-0 text-ec-body font-inter font-bold text-base leading-[26px]">
                            {orderTotal}
                        </span>
                    </div>
                </div>
            </div>
        </>
    );
};

export default OrderSummary;
