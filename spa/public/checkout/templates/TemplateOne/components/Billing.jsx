import React from "react";

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
                        Billing Address
                    </h3>
                </div>
                <div className="grid grid-cols-2 gap-[10px]">
                    <div className="mb-6">
                        <label
                            className="text-ec-body font-inter font-medium 
                        text-base leading-[26px] mb-1 block"
                        >
                            First Name
                        </label>
                        <input
                            type="text"
                            placeholder="Your first name"
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
                            Last Name
                        </label>
                        <input
                            type="text"
                            placeholder="Your last name"
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
                            Email
                        </label>
                        <input
                            type="Email"
                            placeholder="Your email"
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
                            Phone
                        </label>
                        <input
                            type="text"
                            placeholder="Enter your phone number"
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
                        Address line 1
                    </label>
                    <input
                        type="text"
                        placeholder="Your address"
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
                        Address line 2
                    </label>
                    <input
                        type="text"
                        placeholder="Your address"
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
                            City
                        </label>
                        <select
                            className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                            name="billingCity"
                        >
                            <option value="" disabled selected hidden>
                                Select your City
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
                            State
                        </label>
                        <select
                            className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                            name="billingState"
                        >
                            <option value="" disabled selected hidden>
                                Select your State
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
                            Country
                        </label>
                        <select
                            className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                            name="billingCountry"
                        >
                            <option value="" disabled selected hidden>
                                Select your country
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
                            Postal Code
                        </label>
                        <input
                            type="text"
                            name="billingPostcode"
                            placeholder="Write here"
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
