import React, { useEffect, useState } from "react";
import { __ } from "@wordpress/i18n";

const downArrow = `${EASYCOMMERCE.assets}admin/img/icons/arrowDown.png`;

const BillingFields = ({
    billingData,
    updateBillingData,
    setBillingAsShipping,
}) => {
    const [sameAsShipping, setSameAsShipping] = useState(false);

    useEffect(() => {
        if (sameAsShipping) {
            setBillingAsShipping();
        }
    }, [sameAsShipping]);

    return (
        <div className="p-3">
            <div className="flex items-center justify-between cursor-pointer easycommerce-new-order-billing">
                <label className="text-ec-body font-inter font-medium cursor-pointer text-base leading-[26px]">
                    {__("Billing address", "easycommerce")}
                </label>
                <img src={downArrow} className="w-3 h-[7px]" />
            </div>

            <div className="easycommerce-new-order-billing-details hidden">
                <div className="flex items-center justify-start border-b border-ec-border pb-5 my-6">
                    <label
                        htmlFor="easycommerce-same-as-shipping"
                        className="flex items-center"
                    >
                        <input
                            id="easycommerce-same-as-shipping"
                            className="easycommerce-input-checkoutbox"
                            type="checkbox"
                            name="same_as_Shipping"
                            checked={sameAsShipping}
                            onChange={() => setSameAsShipping(!sameAsShipping)}
                        />
                        <span className="text-ec-body ml-3 font-inter font-normal text-base leading-[26px]">
                            {__("Same as Shipping", "easycommerce")}
                        </span>
                    </label>
                </div>
                <label
                    htmlFor="easycommerce-new-order-billing-first-name"
                    className="block mb-4"
                >
                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                        {__("First Name", "easycommerce")}
                    </span>
                    <input
                        id="easycommerce-new-order-billing-first-name"
                        type="text"
                        placeholder={__("Your first Name", "easycommerce")}
                        name="billing-first_name"
                        value={billingData.billing_first_name}
                        onChange={(e) =>
                            updateBillingData(
                                "billing_first_name",
                                e.target.value
                            )
                        }
                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm
                        placeholder:text-sm font-inter text-sm text-ec-body"
                    />
                </label>
                <label
                    htmlFor="easycommerce-new-order-billing-last-name"
                    className="block mb-4"
                >
                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                        {__("Last Name", "easycommerce")}
                    </span>
                    <input
                        id="easycommerce-new-order-billing-last-name"
                        type="text"
                        name="billing-last_name"
                        placeholder={__("Your last Name", "easycommerce")}
                        value={billingData.billing_last_name}
                        onChange={(e) =>
                            updateBillingData(
                                "billing_last_name",
                                e.target.value
                            )
                        }
                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm
                        placeholder:text-sm font-inter text-sm text-ec-body"
                    />
                </label>
                <label
                    htmlFor="easycommerce-new-order-billing-email"
                    className="block mb-4"
                >
                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                        {__("Email", "easycommerce")}
                    </span>
                    <input
                        id="easycommerce-new-order-billing-email"
                        type="email"
                        name="billing-email"
                        placeholder={__("Enter your email address", "easycommerce")}
                        value={billingData.billing_email}
                        onChange={(e) =>
                            updateBillingData("billing_email", e.target.value)
                        }
                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm
                        placeholder:text-sm font-inter text-sm text-ec-body"
                    />
                </label>
                <label
                    htmlFor="easycommerce-new-order-billing-phone"
                    className="block mb-4"
                >
                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                        {__("Phone", "easycommerce")}
                    </span>
                    <input
                        id="easycommerce-new-order-billing-phone"
                        type="tel"
                        name="billing-phone"
                        placeholder={__("Enter your phone number", "easycommerce")}
                        value={billingData.billing_phone}
                        onChange={(e) =>
                            updateBillingData("billing_phone", e.target.value)
                        }
                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm placeholder:text-sm font-inter text-sm text-ec-body"
                    />
                </label>
                <label
                    htmlFor="easycommerce-new-order-billing-address-1"
                    className="block mb-4"
                >
                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                        {__("Address line 1", "easycommerce")}
                    </span>
                    <input
                        id="easycommerce-new-order-billing-address-1"
                        type="text"
                        name="billing-address_1"
                        placeholder={__("Your address", "easycommerce")}
                        value={billingData.billing_address_1}
                        onChange={(e) =>
                            updateBillingData(
                                "billing_address_1",
                                e.target.value
                            )
                        }
                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm placeholder:text-sm font-inter text-sm text-ec-body"
                    />
                </label>
                <label
                    htmlFor="easycommerce-new-order-billing-address-2"
                    className="block mb-4"
                >
                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                        {__("Address line 2", "easycommerce")}
                    </span>
                    <input
                        id="easycommerce-new-order-billing-address-2"
                        type="text"
                        name="billing-address_2"
                        placeholder={__("Your address", "easycommerce")}
                        value={billingData.billing_address_2}
                        onChange={(e) =>
                            updateBillingData(
                                "billing_address_2",
                                e.target.value
                            )
                        }
                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm placeholder:text-sm font-inter text-sm text-ec-body"
                    />
                </label>

                <div className="grid grid-cols-2 gap-[10px] mb-6">
                    <div>
                        <label
                            htmlFor="easycommerce-new-order-billing-country"
                            className="block mb-1 text-ec-body text-base font-inter leading-8"
                        >
                            {__("Country", "easycommerce")}
                        </label>
                        <select
                            className="easycommerce-order-input easycommerce-order-select w-full h-[42px]
                            text-base text-ec-body font-inter rounded-md"
                            id="easycommerce-new-order-billing-country"
                            name="billing-country"
                            value={billingData.billing_country}
                            onChange={(e) =>
                                updateBillingData(
                                    "billing_country",
                                    e.target.value
                                )
                            }
                        >
                            <option value="">{__("Select Country", "easycommerce")}</option>
                            {EASYCOMMERCE.countries &&
                                Object.entries(EASYCOMMERCE.countries).map(
                                    ([code, countryName]) => (
                                        <option key={code} value={code}>
                                            {countryName}
                                        </option>
                                    )
                                )}
                        </select>
                    </div>
                    <div>
                        <label
                            htmlFor="easycommerce-new-order-billing-city"
                            className="block mb-1 text-ec-body text-base font-inter leading-8"
                        >
                            {__("City", "easycommerce")}
                        </label>
                        <input
                            id="easycommerce-new-order-billing-city"
                            type="text"
                            placeholder={__("City", "easycommerce")}
                            name="billing-city"
                            value={billingData.billing_city}
                            onChange={(e) =>
                                updateBillingData(
                                    "billing_city",
                                    e.target.value
                                )
                            }
                            className="easycommerce-order-input w-full h-[42px] border border-ec-border
                            rounded-sm placeholder:text-sm font-inter text-sm text-ec-body"
                        />
                    </div>
                </div>
                <div className="grid grid-cols-2 gap-[10px] mb-6">
                    <div>
                        <label
                            htmlFor="easycommerce-new-order-billing-state"
                            className="block mb-1 text-ec-body text-base font-inter leading-8"
                        >
                            {__("State", "easycommerce")}
                        </label>
                        <input
                            id="easycommerce-new-order-billing-state"
                            type="text"
                            placeholder={__("State", "easycommerce")}
                            name="billing-state"
                            value={billingData.billing_state}
                            onChange={(e) =>
                                updateBillingData(
                                    "billing_state",
                                    e.target.value
                                )
                            }
                            className="easycommerce-order-input w-full h-[42px] border border-ec-border
                            rounded-sm placeholder:text-sm font-inter text-sm text-ec-body"
                        />
                    </div>
                    <div>
                        <label
                            htmlFor="easycommerce-new-order-billing-postcode"
                            className="block mb-1 text-ec-body text-base font-inter leading-8"
                        >
                            {__("ZIP", "easycommerce")}
                        </label>
                        <input
                            id="easycommerce-new-order-billing-postcode"
                            type="text"
                            placeholder={__("Zip code", "easycommerce")}
                            name="billing-postcode"
                            value={billingData.billing_postcode}
                            onChange={(e) =>
                                updateBillingData(
                                    "billing_postcode",
                                    e.target.value
                                )
                            }
                            className="easycommerce-order-input w-full h-[42px] border border-ec-border
                            rounded-sm placeholder:text-sm font-inter text-sm text-ec-body"
                        />
                    </div>
                </div>
            </div>
        </div>
    );
};

export default BillingFields;
