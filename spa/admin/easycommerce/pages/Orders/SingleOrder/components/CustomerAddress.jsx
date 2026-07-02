import React, { useState } from 'react';

const applyFilters = (hookName, ...params) => {
    if (
        window.easycommerceFilters &&
        typeof window.easycommerceFilters[hookName] === 'function'
    ) {
        const result = window.easycommerceFilters[hookName](...params);
        return Array.isArray(result) ? result : params[0];
    }
    return params[0];
};

const CustomerAddress = ({ order }) => {
    const [activeTab, setActiveTab] = useState('billing');

    const billingFields = [
        {
            label: "Name",
            value: `${order?.meta?.billing?.first_name || ''} ${order?.meta?.billing?.last_name || ''}`.trim(),
        },
        {
            label: "Email",
            value: order?.meta?.billing?.email,
        },
        {
            label: "Phone Number",
            value: order?.meta?.billing?.phone,
        },
        {
            label: "Address Line 1",
            value: order?.meta?.billing?.address_1,
        },
        {
            label: "Address Line 2",
            value: order?.meta?.billing?.address_2,
        },
        {
            label: "City",
            value: order?.meta?.billing?.city,
        },
        {
            label: "State",
            value: order?.meta?.billing?.state,
        },
        {
            label: "Country",
            value: order?.meta?.billing?.country,
        },
    ];

    const defaultBillingFields =
        applyFilters(
            "easycommerce_admin_side_billing_fields",
            billingFields,
            order
        );

    const shippingFields = [
        {
            label: "Name",
            value: `${order?.meta?.shipping?.first_name || ''} ${order?.meta?.shipping?.last_name || ''}`.trim(),
        },
        {
            label: "Email",
            value: order?.meta?.shipping?.email,
        },
        {
            label: "Phone Number",
            value: order?.meta?.shipping?.phone,
        },
        {
            label: "Address Line 1",
            value: order?.meta?.shipping?.address_1,
        },
        {
            label: "Address Line 2",
            value: order?.meta?.shipping?.address_2,
        },
        {
            label: "City",
            value: order?.meta?.shipping?.city,
        },
        {
            label: "State",
            value: order?.meta?.shipping?.state,
        },
        {
            label: "Country",
            value: order?.meta?.shipping?.country,
        },
    ];

    const defaultShippingFields =
        applyFilters(
            "easycommerce_admin_side_shipping_fields",
            shippingFields,
            order
        );

    const hasShippingData = defaultShippingFields.some(
        (field) => field.value && field.value.trim() !== ""
    );

    const renderAddressFields = (fields) => (
        <div className="px-6 py-[8px]">
            {fields.map((field, index) => (
                <div key={index} className='py-2 flex justify-between align-center border-b border-ec-table-stock last:border-b-0'>
                    <p className='text-ec-title font-inter font-normal text-base leading-[26px]'>
                        {field.label} :
                    </p>
                    <p className="pb-[3px] text-ec-body font-inter text-sm font-normal leading-[26px] text-right">
                        {field.value || "N/A"}
                    </p>
                </div>
            ))}
        </div>
    );

    return (
        <div className="w-[550px] bg-white rounded-2xl">
            <div className="flex flex-col border-b border-ec-table-stock pt-4 px-6">
                <h3 className="text-ec-title text-xl font-medium font-inter leading-8 pb-4">
                   Address
                </h3>
            </div>
            
            <div className="grid grid-cols-1 p-6 pt-0">
                <div id="easycommerce-settings-submenus" className="submenu-scroll-container mt-6">
                    <ul id="easycommerce-settings-submenus-list" className="terms-filters-container flex gap-2 border-b border-ec-primary">
                        <li id="billing" className={`text-center inline-block ${activeTab === 'billing' ? 'active' : ''}`}>
                            <a
                                onClick={() => setActiveTab('billing')}
                                className={`block !py-2 text-ec-body text-[14px] hover:text-ec-body focus:outline-none focus:shadow-none focus:text-ec-body ${
                                    activeTab === 'billing' 
                                        ? 'text-ec-primary hover:text-ec-primary' 
                                        : ''
                                }`}
                            >
                                Billing Address
                            </a>
                        </li>

                        {hasShippingData && (
                            <li id="shipping" className={`text-center inline-block ${activeTab === 'shipping' ? 'active' : ''}`}>
                                <a
                                    onClick={() => setActiveTab('shipping')}
                                    className={`block !py-2 text-ec-body text-[14px] hover:text-ec-body focus:outline-none focus:shadow-none focus:text-ec-body ${
                                        activeTab === 'shipping' 
                                            ? 'text-ec-primary hover:text-ec-primary' 
                                            : ''
                                    }`}
                                >
                                    Shipping Address
                                </a>
                            </li>
                        )}
                    </ul>
                </div>

                <div className="border border-ec-table-stock rounded-lg mt-4">
                    {activeTab === 'billing' && renderAddressFields(defaultBillingFields)}
                    {activeTab === 'shipping' && hasShippingData && renderAddressFields(defaultShippingFields)}
                </div>
            </div>
        </div>
    );
};

export default CustomerAddress;
