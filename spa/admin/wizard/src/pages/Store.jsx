import React, {useState} from "react";
import { __ } from '@wordpress/i18n';

const bg = `${EASYCOMMERCE.assets}admin/img/icons/bg.png`;

const Store = ({ formValues, setFormValues, importDemoChecked, setImportDemoChecked }) => {

    const pageFields = [
        {
            name: "shop",
            label: __('Shop Page', 'easycommerce'),
            description: __('Choose the page where your products will appear. You can also select the “<code>– Create New Page –</code>” option and we’ll set it up for you.', 'easycommerce'),
        },
        {
            name: "checkout",
            label: __('Checkout Page', 'easycommerce'),
            description: __('Choose the page customers will use to complete checkout. This page should include the shortcode: <code>[easycommerce-checkout]</code>. You may also select the “<code>– Create New Page –</code>” option and we’ll create it for you.', 'easycommerce'),
        },
        {
            name: "dashboard",
            label: __('Customer Dashboard', 'easycommerce'),
            description: __('Choose the page customers will use to manage their orders and account. This page should include the shortcode: <code>[easycommerce-dashboard]</code>. You can also select the “<code>– Create New Page –</code>” option and we’ll take care of it.', 'easycommerce'),
        },
        {
            name: "payment",
            label: __('Payment Page', 'easycommerce'),
            description: __('Choose the page where customers can pay for pending orders. This page should include the shortcode: <code>[easycommerce-payment]</code>. You can also select the "– Create New Page –" option and we’ll set it up for you.', 'easycommerce'),
        },
    ];

    return (
        <div
            className="bg-cover bg-center w-full flex flex-col items-center"
        >
            <div className="mb-8 font-inter">
                <h2 className='text-2xl text-center  text-ec-title font-medium'>
                    {__('Store Setup', 'easycommerce')}
                </h2>

                <p className="text-[#606060] text-[16px] text-center mt-2 max-w-xl">
                    {__('Your store needs a Shop page, Checkout page, and Customer Dashboard to work properly. Choose existing pages or let us create them for you.', 'easycommerce')}
                </p>
            </div>

            <div className="w-full font-inter">
                <div className="flex flex-col w-full space-y-6">
                    <div className="">
                        <div className="flex flex-col gap-3 space-y-4">

                            {pageFields.map(({ name, label, description }) => (
                                <div key={name} className="flex flex-col space-y-2">
                                    <label className="text-ec-body text-[16px] font-medium">
                                        {label}
                                    </label>
                                    <select
                                        className="easycommerce-wizard-input w-full"
                                        name={`general-store-${name}`}
                                        value={formValues[name] || ""}
                                        onChange={(e) =>
                                            setFormValues((prev) => ({
                                                ...prev,
                                                [name]: e.target.value,
                                            }))
                                        }
                                    >
                                        <option value="create">- Create New Page -</option>
                                        {Object.keys(EASYCOMMERCE.pages).map((pageId) => (
                                            <option key={pageId} value={pageId}>
                                                {EASYCOMMERCE.pages[pageId]}
                                            </option>
                                        ))}
                                    </select>
                                    <p
                                      className="text-[#606060] text-[14px] mt-1"
                                      dangerouslySetInnerHTML={{ __html: description }}
                                    />

                                </div>
                            ))}
                        </div>
                    </div>
                     {/* import demo products */}
                    <div className="">
                        <label className="text-ec-body text-[16px] font-medium">
                            Demo Products
                        </label>
                        <div className="flex flex-col space-y-4">
                            <div className="flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    id="demoProducts"
                                    className="easycommerce-checkbox-input relative pointer checked:bg-ec-primary"
                                    checked={importDemoChecked}
                                    onChange={(e) => setImportDemoChecked(e.target.checked)}
                                />
                                <label
                                    htmlFor="demoProducts"
                                    className="text-[#606060] text-[14px] my-[8px]"
                                >
                                    Import demo products to preview your storefront and test features. Remove them anytime.
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Store;
