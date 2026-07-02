import { __ } from '@wordpress/i18n';
import React, { useEffect } from "react";

const bg = `${EASYCOMMERCE.assets}admin/img/icons/bg.png`;

const Business = ({ formValues, setFormValues }) => {

    const { business_type: businessTypes, countries } = EASYCOMMERCE;
    
     useEffect(() => {
        if (!formValues?.country) return;

        const fetchCurrency = async () => {
            try {
                const res = await fetch(
                    `${EASYCOMMERCE.rest_base}/geo/currencies?country_code=${formValues.country}`
                );

                if (!res.ok) return;

                const result = await res.json();

                const currency = result?.data?.currencies?.currency;
                const symbol = result?.data?.currencies?.currency_symbol;

                if (!currency) return;

                setFormValues(prev => ({
                    ...prev,
                    currency,
                    currency_symbol: symbol,
                }));
            } catch (e) {
                console.error('Error fetching currency:', e);
            }
        };

        fetchCurrency();
    }, [formValues.country]);

    const handleChange = (field) => (e) =>
        setFormValues({ ...formValues, [field]: e.target.value });

    const renderSelect = (id, name, label, value, options, description) => (
        <div className="flex flex-col space-y-2">
            <label htmlFor={id} className="text-ec-body text-[16px] font-medium">
                {label}
            </label>
            <select
                id={id}
                name={name}
                className="easycommerce-wizard-input w-full"
                value={value}
                onChange={handleChange(id)}
            >
                {Object.entries(options).map(([val, text]) => (
                    <option key={val} value={val}>
                        {text}
                    </option>
                ))}
            </select>
            <p className="text-[#606060] text-[14px] mt-1">
                {description}
            </p>
        </div>
    );

    return (
        <div
            className="bg-cover bg-center w-full flex flex-col items-center"
        >
            <div className="mb-8 font-inter">
                <h2 className='text-2xl text-center  text-ec-title font-medium'>
                    {__('Business Setup', 'easycommerce')}
                </h2>

                <p className="text-[#606060] text-[16px] text-center mt-2 max-w-xl">
                    {__('Fill in your business details so your store functions seamlessly and is ready for customers. You can adjust these settings anytime.', 'easycommerce')}
                </p>
            </div>

            <div className="w-full">
                <div className="flex flex-col w-full space-y-6">

                    {/* Business Name */}
                    <div className="flex flex-col space-y-2">
                        <label htmlFor="store_name" className="text-ec-body text-[16px] font-medium">
                            {__('Store Name', 'easycommerce')}
                        </label>
                        <input
                            id="store_name"
                            type="text"
                            name="general-business-store_name"
                            className="easycommerce-wizard-input w-full"
                            placeholder="Write your business name"
                            value={formValues.store_name}
                            onChange={(e) =>
                                setFormValues((prev) => ({ ...prev, store_name: e.target.value }))
                            }
                        />
                        <p className="text-[#606060] text-[14px] mt-1">
                            {__('Provide the name your store will use in your storefront and communications.', 'easycommerce')}
                        </p>
                    </div>

                    {/* Business Type */}
                    {renderSelect(
                        "business_type",
                        "general-business-business_type",
                        "Business Type",
                        formValues.business_type,
                        businessTypes,
                        __( 'Pick the type that best describes your business. You can update it later if needed.', 'easycommerce' )
                    )}

                    {/* Business Address */}
                    {renderSelect(
                        "country",
                        "general-business-country",
                        "Business Country",
                        formValues.country,
                        countries,
                        __( 'Select the country where your business is located to configure taxes, currency, and shipping correctly.', 'easycommerce' )
                    )}

                    {/* Email */}
                    <div className="flex flex-col space-y-2">
                        <label htmlFor="email" className="text-ec-body text-[16px] font-medium">
                            Store Email
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="general-business-business_email"
                            className="easycommerce-wizard-input w-full"
                            placeholder="Write your business email"
                            value={formValues.email}
                            onChange={(e) =>
                                setFormValues((prev) => ({ ...prev, email: e.target.value }))
                            }
                        />
                        <p className="text-[#606060] text-[14px] mt-1">
                            {__('Enter the email address your store will use for notifications and customer communications.', 'easycommerce')}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Business;