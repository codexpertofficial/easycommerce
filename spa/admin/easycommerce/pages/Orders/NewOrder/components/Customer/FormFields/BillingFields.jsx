import React, { useEffect, useState } from "react";

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
                    Billing address
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
                            Same as Shipping
                        </span>
                    </label>
                </div>
                <label
                    htmlFor="easycommerce-new-order-billing-first-name"
                    className="block mb-4"
                >
                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                        First Name
                    </span>
                    <input
                        id="easycommerce-new-order-billing-first-name"
                        type="text"
                        placeholder="Your first Name"
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
                        Last Name
                    </span>
                    <input
                        id="easycommerce-new-order-billing-last-name"
                        type="text"
                        name="billing-last_name"
                        placeholder="Your last Name"
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
                        Email
                    </span>
                    <input
                        id="easycommerce-new-order-billing-email"
                        type="email"
                        name="billing-email"
                        placeholder="Enter your email address"
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
                        Phone
                    </span>
                    <input
                        id="easycommerce-new-order-billing-phone"
                        type="tel"
                        name="billing-phone"
                        placeholder="Enter your phone number"
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
                        Address line 1
                    </span>
                    <input
                        id="easycommerce-new-order-billing-address-1"
                        type="text"
                        name="billing-address_1"
                        placeholder="Your address"
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
                        Address line 2
                    </span>
                    <input
                        id="easycommerce-new-order-billing-address-2"
                        type="text"
                        name="billing-address_2"
                        placeholder="Your address"
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
                            Country
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
                            <option value="">Select Country</option>
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
                            City
                        </label>
                        <input
                            id="easycommerce-new-order-billing-city"
                            type="text"
                            placeholder="City"
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
                            State
                        </label>
                        <input
                            id="easycommerce-new-order-billing-state"
                            type="text"
                            placeholder="State"
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
                            ZIP
                        </label>
                        <input
                            id="easycommerce-new-order-billing-postcode"
                            type="text"
                            placeholder="Zip code"
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
