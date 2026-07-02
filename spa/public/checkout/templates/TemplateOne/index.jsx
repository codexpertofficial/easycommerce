import React, { useState } from "react";
import Billing from "./components/Billing";
import Cart from "./components/Cart";
import OrderSummary from "./components/OrderSummary";
import PaymentMethod from "./components/PaymentMethod";
import Shipping from "./components/Shipping";

//Images and Icons
const infoIcon = `${EASYCOMMERCE.assets}public/icons/info-13-13.png`;

const TemplateOne = () => {
    return (
        <>
            <div className="easycommerce-checkout-wrapper easycommerce-checkout-template-one">
                <form className="grid grid-cols-2 gap-10 mt-[35px]">
                    <div>
                        <Billing />
                        <Shipping />
                    </div>
                    <div>
                        <Cart />
                        <OrderSummary />
                        <PaymentMethod />
                        <button
                            type="submit"
                            className="easycommerce-checkout-main-btn text-white w-full flex justify-center  items-center gap-2 font-inter bg-ec-primary group border border-ec-primary py-[11px] px-8 rounded-lg  font-normal hover:text-white hover:bg-ec-secondary focus:shadow-none focus:text-white hover:border-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out duration-500 leading-[26px]"
                        >
                            Confirm Order
                        </button>
                        <p
                            className="flex items-center text-[12px] font-normal font-inter leading-5
                        text-ec-secondary mt-3 text-center justify-center"
                        >
                            <img src={infoIcon} className="mr-[10px]" /> Learn
                            more Taxes and Shipping information
                        </p>
                    </div>
                </form>
            </div>
        </>
    );
};

export default TemplateOne;
