import React from "react";
import { __ } from "@wordpress/i18n";

const applyFilters = (hookName, ...params) => {
    if (
        window.easycommerceFilters &&
        typeof window.easycommerceFilters[hookName] === "function"
    ) {
        const result = window.easycommerceFilters[hookName](...params);
        return Array.isArray(result) ? result : params[0];
    }
    return params[0];
};

/**
 * Renders an address as a label/value grid (no card chrome). Wrapped by
 * AddressTabs, which supplies the card, header and Billing/Shipping tabs.
 */
const AddressFields = ({ address }) => {
    if (!address || (Array.isArray(address) && address.length === 0)) {
        return (
            <div className="w-full p-6 text-sm text-ec-placeholder">
                {__( "No address provided.", "easycommerce" )}
            </div>
        );
    }

    const addressFields = [
        {
            label: __( "Full Name", "easycommerce" ),
            value:
                address["first_name"] || address["last_name"]
                    ? `${address["first_name"] || ""} ${address["last_name"] || ""}`.trim()
                    : __( "N/A", "easycommerce" ),
        },
        { key: "email", label: __( "Email", "easycommerce" ), value: address["email"] },
        { label: __( "Phone", "easycommerce" ), value: address["phone"] },
        { label: __( "Address 1", "easycommerce" ), value: address["address_1"] },
        { label: __( "Address 2", "easycommerce" ), value: address["address_2"] },
        {
            label: __( "City", "easycommerce" ),
            value:
                address["city"] || address["postcode"]
                    ? `${address["city"] || ""} ${address["postcode"] || ""}`.trim()
                    : __( "N/A", "easycommerce" ),
        },
        { label: __( "State", "easycommerce" ), value: address["state"] },
        { label: __( "Country", "easycommerce" ), value: address["country"] },
        { label: __( "Company", "easycommerce" ), value: address["company"] },
    ];

    const filteredAddressFields = applyFilters(
        "easycommerce_order_address",
        addressFields,
        address
    );

    return (
        <div className="w-full h-full grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 p-6">
            {filteredAddressFields.map((field, index) => (
                <div key={index} className="flex flex-col gap-0.5 font-inter min-w-0">
                    <p className="text-xs font-medium uppercase tracking-wide text-ec-placeholder">
                        {field.label}
                    </p>
                    <p
                        className={`text-sm text-ec-body font-medium ${
                            field.key === "email" ? "break-all" : ""
                        }`}
                    >
                        {field.value || __( "N/A", "easycommerce" )}
                    </p>
                </div>
            ))}
        </div>
    );
};

export default AddressFields;
