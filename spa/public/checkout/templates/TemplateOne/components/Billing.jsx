import React from "react";
import { __ } from "@wordpress/i18n";

//Images and Icons
const billingImage = `${EASYCOMMERCE.assets}public/img/checkout/billing.png`;

const Billing = () => {
    return (
        <>
            <div className="border border-ec-border bg-white rounded-lg p-[30px] mb-5">
                <div className="flex items-center mb-[30px]">
                    <img
                        src={billingImage}
                        className="w-[53px] h-[53px] mr-4"
                    />
                    <h3 className="easycommerce-billing-title font-inter leading-8 font-semibold text-xl mb-0">
                        {__( "Billing Address", "easycommerce" )}
                    </h3>
                </div>
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
                            name="billingFirstName"
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
                            name="billingLastName"
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
                            name="billingEmail"
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
                            name="billingPhone"
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
                        name="billingAddress1"
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
                        name="billingAddress2"
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
                            name="billingCity"
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
                            name="billingState"
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
                            name="billingCountry"
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
                            name="billingPostcode"
                            placeholder={__( "Write here", "easycommerce" )}
                            className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                        />
                    </div>
                </div>
            </div>
        </>
    );
};

export default Billing;
