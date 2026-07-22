import React, { useState } from "react";
import { __ } from "@wordpress/i18n";

//Images and Icons
const billingImage = `${EASYCOMMERCE.assets}public/img/checkout/billing.png`;

const Shipping = () => {
    const [isChecked, setIsChecked] = useState(true);
    return (
        <>
            <div className="border border-ec-border bg-white rounded-lg p-[30px] mb-5">
                <div className="flex items-center justify-between">
                    <div className="flex items-center">
                        <img
                            src={billingImage}
                            className="w-[53px] h-[53px] mr-4"
                        />
                        <h3 className="easycommerce-billing-title font-inter leading-8 font-semibold text-xl mb-0">
                            {__( "Shipping Address", "easycommerce" )}
                        </h3>
                    </div>

                    <label
                        htmlFor="easycommerce-checkout-same-as-shipping"
                        className="flex iteams-center cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            id="easycommerce-checkout-same-as-shipping"
                            className="easycommerce-input-checkoutbox"
                            name="sameAsShipping"
                            onChange={() => setIsChecked(!isChecked)}
                            checked={isChecked}
                        />
                        <span className="text-ec-body font-inter font-medium text-base leading-[26px] ml-2">
                            {__( "Same as Billing", "easycommerce" )}
                        </span>
                    </label>
                </div>
                {/* Shipping Address */}
                <div className="easycommerce-checkout-shipping-same-as-address hidden mt-[30px]">
                    <div className="grid grid-cols-2 gap-[10px]">
                        <div className="mb-6">
                            <label
                                className="text-ec-body font-inter font-medium 
                        text-base leading-[26px] mb-1 block"
                            >
                                {__( "First Name", "easycommerce" )}
                            </label>
                            <input
                                type="text"
                                placeholder={__( "Your first name", "easycommerce" )}
                                className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                                name="shippingFirstName"
                            />
                        </div>
                        <div className="mb-6">
                            <label
                                label
                                className="text-ec-body font-inter font-medium 
                        text-base leading-[26px] mb-1 block"
                            >
                                {__( "Last Name", "easycommerce" )}
                            </label>
                            <input
                                type="text"
                                placeholder={__( "Your last name", "easycommerce" )}
                                className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                                name="shippingLastName"
                            />
                        </div>
                    </div>
                    <div className="grid grid-cols-2 gap-[10px] mb-6">
                        <div>
                            <label
                                label
                                className="text-ec-body font-inter font-medium 
                        text-base leading-[26px] mb-1 block"
                            >
                                {__( "Email", "easycommerce" )}
                            </label>
                            <input
                                type="Email"
                                placeholder={__( "Your email", "easycommerce" )}
                                className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                                name="shippingEmail"
                            />
                        </div>
                        <div>
                            <label
                                label
                                className="text-ec-body font-inter font-medium 
                        text-base leading-[26px] mb-1 block"
                            >
                                {__( "Phone", "easycommerce" )}
                            </label>
                            <input
                                type="text"
                                placeholder={__( "Enter your phone number", "easycommerce" )}
                                className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                                name="shippingPhone"
                            />
                        </div>
                    </div>
                    <div className="mb-6">
                        <label
                            label
                            className="text-ec-body font-inter font-medium 
                        text-base leading-[26px] mb-1 block"
                        >
                            {__( "Address line 1", "easycommerce" )}
                        </label>
                        <input
                            type="text"
                            placeholder={__( "Your address", "easycommerce" )}
                            className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                            name="shippingAddress1"
                        />
                    </div>
                    <div className="mb-6">
                        <label
                            label
                            className="text-ec-body font-inter font-medium 
                        text-base leading-[26px] mb-1 block"
                        >
                            {__( "Address line 2", "easycommerce" )}
                        </label>
                        <input
                            type="text"
                            placeholder={__( "Your address", "easycommerce" )}
                            className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                            name="shippingAddress2"
                        />
                    </div>
                    <div className="grid grid-cols-2 gap-[10px] mb-6">
                        <div>
                            <label
                                label
                                className="text-ec-body font-inter font-medium 
                        text-base leading-[26px] mb-1 block"
                            >
                                {__( "City", "easycommerce" )}
                            </label>
                            <select
                                className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                                name="shippingCity"
                            >
                                <option value="" disabled selected hidden>
                                    {__( "Select your City", "easycommerce" )}
                                </option>
                                <option value="dhaka">Savar</option>
                                <option value="dhaka">Dhaka</option>
                                <option value="khulna">Khulna</option>
                            </select>
                        </div>
                        <div>
                            <label
                                className="text-ec-body font-inter font-medium 
                        text-base leading-[26px] mb-1 block"
                            >
                                {__( "State", "easycommerce" )}
                            </label>
                            <select
                                className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                                name="shippingState"
                            >
                                <option value="" disabled selected hidden>
                                    {__( "Select your State", "easycommerce" )}
                                </option>
                                <option value="dhaka">Savar</option>
                                <option value="dhaka">Dhaka</option>
                                <option value="khulna">Khulna</option>
                            </select>
                        </div>
                    </div>
                    <div className="grid grid-cols-2 gap-[10px] mb-6">
                        <div>
                            <label
                                label
                                className="text-ec-body font-inter font-medium 
                        text-base leading-[26px] mb-1 block"
                            >
                                {__( "Country", "easycommerce" )}
                            </label>
                            <select
                                className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                                name="shippingCountry"
                            >
                                <option value="" disabled selected hidden>
                                    {__( "Select your country", "easycommerce" )}
                                </option>
                                <option value="dhaka">Savar</option>
                                <option value="dhaka">Dhaka</option>
                                <option value="khulna">Khulna</option>
                            </select>
                        </div>
                        <div>
                            <label
                                label
                                className="text-ec-body font-inter font-medium 
                        text-base leading-[26px] mb-1 block"
                            >
                                {__( "Postal Code", "easycommerce" )}
                            </label>
                            <input
                                type="text"
                                name="shippingPostcode"
                                placeholder={__( "Write here", "easycommerce" )}
                                className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

export default Shipping;
