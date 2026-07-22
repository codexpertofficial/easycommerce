import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import AddressFields from "./AddressFields";

const billingIcon = (
    <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
);
const shippingIcon = (
    <path strokeLinecap="round" strokeLinejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
);

/**
 * Billing / Shipping addresses in a single card with two tabs.
 */
const AddressTabs = ({ billing, shipping }) => {
    const [active, setActive] = useState("billing");

    const tabs = [
        { id: "billing", label: __( "Billing Address", "easycommerce" ), icon: billingIcon },
        { id: "shipping", label: __( "Shipping Address", "easycommerce" ), icon: shippingIcon },
    ];

    const address = active === "billing" ? billing : shipping;

    return (
        <div className="flex flex-col h-full bg-white border border-ec-border rounded-2xl overflow-hidden shadow-[0_2px_16px_-8px_rgba(18,3,80,0.10)]">
            <div className="w-full flex items-center gap-6 px-5 border-b border-ec-border bg-ec-table-bg">
                {tabs.map((tab) => {
                    const isActive = active === tab.id;
                    return (
                        <button
                            key={tab.id}
                            type="button"
                            onClick={() => setActive(tab.id)}
                            className={`inline-flex items-center gap-2 py-4 -mb-px border-t-0 border-x-0 border-b-2 rounded-none !bg-transparent shadow-none cursor-pointer font-inter text-base font-semibold transition-colors duration-200 ${
                                isActive
                                    ? "text-ec-primary border-ec-primary"
                                    : "text-ec-placeholder border-transparent hover:text-ec-body"
                            }`}
                        >
                            <svg
                                className="w-4 h-4"
                                data-slot="icon"
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="1.7"
                                viewBox="0 0 24 24"
                            >
                                {tab.icon}
                            </svg>
                            {tab.label}
                        </button>
                    );
                })}
            </div>

            <AddressFields address={address} />
        </div>
    );
};

export default AddressTabs;
