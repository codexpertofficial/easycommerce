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

const AddressSection = ({ title = "Billing", address }) => {
    if (!address || (Array.isArray(address) && address.length === 0)) return null;

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
        <div className="flex flex-col">
            <div className="w-full p-4 pb-3 border border-b-0 border-ec-border rounded-tl-lg rounded-tr-lg">
                <h3 className="font-inter easycommerce-dashboard-order-section-title">
                    {title} Address
                </h3>
            </div>

            <div className="w-full h-full flex flex-col gap-1 p-4 pt-10 border border-ec-border rounded-bl-lg rounded-br-lg">
                {filteredAddressFields.map((field, index) => (
                    <div
                        key={index}
                        className="pb-[3px] flex justify-start gap-1 text-ec-placeholder font-inter text-sm leading-[26px] font-normal"
                    >
                        <p>{field.label}:</p>
                        <p
                            className={`text-ec-body ${
                                field.label === "Email" ? "break-all" : ""
                            }`}
                        >
                            {field.value || "N/A"}
                        </p>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default AddressSection;
