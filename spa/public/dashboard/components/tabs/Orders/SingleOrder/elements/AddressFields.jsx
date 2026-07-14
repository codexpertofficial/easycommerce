import React from "react";

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
                No address provided.
            </div>
        );
    }

    const addressFields = [
        {
            label: "Full Name",
            value:
                address["first_name"] || address["last_name"]
                    ? `${address["first_name"] || ""} ${address["last_name"] || ""}`.trim()
                    : "N/A",
        },
        { label: "Email", value: address["email"] },
        { label: "Phone", value: address["phone"] },
        { label: "Address 1", value: address["address_1"] },
        { label: "Address 2", value: address["address_2"] },
        {
            label: "City",
            value:
                address["city"] || address["postcode"]
                    ? `${address["city"] || ""} ${address["postcode"] || ""}`.trim()
                    : "N/A",
        },
        { label: "State", value: address["state"] },
        { label: "Country", value: address["country"] },
        { label: "Company", value: address["company"] },
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
                            field.label === "Email" ? "break-all" : ""
                        }`}
                    >
                        {field.value || "N/A"}
                    </p>
                </div>
            ))}
        </div>
    );
};

export default AddressFields;
