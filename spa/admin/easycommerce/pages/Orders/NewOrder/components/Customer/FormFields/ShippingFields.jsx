import React from "react";
import { __ } from "@wordpress/i18n";

const downArrow = `${EASYCOMMERCE.assets}admin/img/icons/arrowDown.png`;

const ShippingFields = ({ shippingData, updateShippingData }) => {
    return (
        <div className="p-3 pb-0">
            <div
                className="easycommerce-new-order-shipping flex items-center justify-between cursor-pointer
                border-b border-ec-border pb-3"
            >
                <label
                    className="text-ec-body font-inter font-medium
                    text-base leading-[26px] cursor-pointer"
                >
                    {__("Shipping Address", "easycommerce")}
                </label>
                <img src={downArrow} className="w-3 h-[7px]" />
            </div>

            <div className="easycommerce-new-order-shipping-details mt-6 hidden">
                <label
                    htmlFor="easycommerce-new-order-shipping-first-name"
                    className="block mb-4"
                >
                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                        {__("First Name", "easycommerce")}
                    </span>
                    <input
                        id="easycommerce-new-order-shipping-first-name"
                        type="text"
                        name="shipping-first_name"
                        placeholder={__("Your first Name", "easycommerce")}
                        value={shippingData.shipping_first_name}
                        onChange={(e) =>
                            updateShippingData(
                                "shipping_first_name",
                                e.target.value
                            )
                        }
                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm
                        placeholder:text-sm font-inter text-sm text-ec-body"
                    />
                </label>
                <label
                    htmlFor="easycommerce-new-order-shipping-last-name"
                    className="block mb-4"
                >
                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                        {__("Last Name", "easycommerce")}
                    </span>
                    <input
                        id="easycommerce-new-order-shipping-last-name"
                        type="text"
                        name="shipping-last_name"
                        placeholder={__("Your last Name", "easycommerce")}
                        value={shippingData.shipping_last_name}
                        onChange={(e) =>
                            updateShippingData(
                                "shipping_last_name",
                                e.target.value
                            )
                        }
                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm placeholder:text-sm
                        font-inter text-sm text-ec-body"
                    />
                </label>
                <label
                    htmlFor="easycommerce-new-order-shipping-email"
                    className="block mb-4"
                >
                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                        {__("Email", "easycommerce")}
                    </span>
                    <input
                        id="easycommerce-new-order-shipping-email"
                        type="email"
                        name="shipping-email"
                        placeholder={__("Enter your email address", "easycommerce")}
                        value={shippingData.shipping_email}
                        onChange={(e) =>
                            updateShippingData("shipping_email", e.target.value)
                        }
                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm placeholder:text-sm
                        font-inter text-sm text-ec-body"
                    />
                </label>
                <label
                    htmlFor="easycommerce-new-order-shipping-phone"
                    className="block mb-4"
                >
                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                        {__("Phone", "easycommerce")}
                    </span>
                    <input
                        id="easycommerce-new-order-shipping-phone"
                        type="tel"
                        name="shipping-phone"
                        placeholder={__("Enter your phone number", "easycommerce")}
                        value={shippingData.shipping_phone}
                        onChange={(e) =>
                            updateShippingData("shipping_phone", e.target.value)
                        }
                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm
                        placeholder:text-sm font-inter text-sm text-ec-body"
                    />
                </label>
                <label
                    htmlFor="easycommerce-new-order-shipping-address-1"
                    className="block mb-4"
                >
                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                        {__("Address line 1", "easycommerce")}
                    </span>
                    <input
                        id="easycommerce-new-order-shipping-address-1"
                        type="text"
                        name="shipping-address_1"
                        placeholder={__("Your address", "easycommerce")}
                        value={shippingData.shipping_address_1}
                        onChange={(e) =>
                            updateShippingData(
                                "shipping_address_1",
                                e.target.value
                            )
                        }
                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm
                        placeholder:text-sm font-inter text-sm text-ec-body"
                    />
                </label>
                <label
                    htmlFor="easycommerce-new-order-shipping-address-2"
                    className="block mb-4"
                >
                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                        {__("Address line 2", "easycommerce")}
                    </span>
                    <input
                        id="easycommerce-new-order-shipping-address-2"
                        type="text"
                        name="shipping-address_2"
                        placeholder={__("Your address", "easycommerce")}
                        value={shippingData.shipping_address_2}
                        onChange={(e) =>
                            updateShippingData(
                                "shipping_address_2",
                                e.target.value
                            )
                        }
                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm
                        placeholder:text-sm font-inter text-sm text-ec-body"
                    />
                </label>

                <div className="grid grid-cols-2 gap-[10px] mb-6">
                    <div>
                        <label
                            label
                            className="block mb-1 text-ec-body text-base font-inter leading-8"
                        >
                            {__("Country", "easycommerce")}
                        </label>
                        <select
                            className="easycommerce-order-input easycommerce-order-select w-full h-[42px]
                            text-base text-ec-body font-inter rounded-md"
                            name="shipping-country"
                            value={shippingData.shipping_country}
                            onChange={(e) =>
                                updateShippingData(
                                    "shipping_country",
                                    e.target.value
                                )
                            }
                        >
                            <option value="">{__("Select Country", "easycommerce")}</option>
                            {EASYCOMMERCE.shipping.countries &&
                                Object.entries(
                                    EASYCOMMERCE.shipping.countries
                                ).map(([code, countryName]) => (
                                    <option key={code} value={code}>
                                        {countryName}
                                    </option>
                                ))}
                        </select>
                    </div>
                    <div>
                        <label
                            htmlFor="easycommerce-new-order-shipping-city"
                            className="block mb-1 text-ec-body text-base font-inter leading-8"
                        >
                            {__("City", "easycommerce")}
                        </label>
                        <input
                            id="easycommerce-new-order-shipping-city"
                            type="text"
                            placeholder={__("City", "easycommerce")}
                            name="shipping-city"
                            value={shippingData.shipping_city}
                            onChange={(e) =>
                                updateShippingData(
                                    "shipping_city",
                                    e.target.value
                                )
                            }
                            className="easycommerce-order-input w-full h-[42px] border border-ec-border
                            rounded-sm placeholder:text-sm font-inter text-sm
                            text-ec-body"
                        />
                    </div>
                </div>
                <div className="grid grid-cols-2 gap-[10px] mb-6">
                    <div>
                        <label className="block mb-1 text-ec-body text-base font-inter leading-8">
                            {__("State", "easycommerce")}
                        </label>
                        <input
                            id="easycommerce-new-order-shipping-state"
                            type="text"
                            placeholder={__("State", "easycommerce")}
                            name="shipping-state"
                            value={shippingData.shipping_state}
                            onChange={(e) =>
                                updateShippingData(
                                    "shipping_state",
                                    e.target.value
                                )
                            }
                            className="easycommerce-order-input w-full h-[42px] border border-ec-border
                            rounded-sm placeholder:text-sm font-inter text-sm
                            text-ec-body"
                        />
                    </div>
                    <div>
                        <label
                            htmlFor="easycommerce-new-order-shipping-postcode"
                            className="block mb-1 text-ec-body text-base font-inter leading-8"
                        >
                            {__("ZIP", "easycommerce")}
                        </label>
                        <input
                            id="easycommerce-new-order-shipping-postcode"
                            type="text"
                            placeholder={__("Zip code", "easycommerce")}
                            name="shipping-postcode"
                            value={shippingData.shipping_postcode}
                            onChange={(e) =>
                                updateShippingData(
                                    "shipping_postcode",
                                    e.target.value
                                )
                            }
                            className="easycommerce-order-input w-full h-[42px] border border-ec-border
                            rounded-sm placeholder:text-sm font-inter text-sm
                            text-ec-body"
                        />
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ShippingFields;
