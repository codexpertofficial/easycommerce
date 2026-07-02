import React, { useEffect, useState } from "react";
import { __ } from "@wordpress/i18n";

const Payment = ({ formValues, setFormValues }) => {
    const { active_payment_methods } = EASYCOMMERCE;
    const [allCurrencies, setAllCurrencies] = useState({});
    const [currenciesList, setCurrenciesList] = useState({});

    const fetchAllCurrencies = async () => {
        try {

            const res = await fetch(`${EASYCOMMERCE.rest_base}/geo/currencies`);
            if (!res.ok) return {};

            const result = await res.json();
            const currencies = result?.data?.currencies;

            if (!currencies) return {};

            let currenciesMap = {};

            if (Array.isArray(currencies)) {
                currencies.forEach(item => {
                    const code = item.currency?.trim();
                    const name = item.currency_name?.trim();
                    const symbol = item.currency_symbol?.trim();
                    if (code && name) {
                        currenciesMap[code] = { name, symbol: symbol || code };
                    }
                });
            } 
            else if (currencies.currency) {
                const code = currencies.currency?.trim();
                currenciesMap[code] = {
                    name: currencies.currency_name?.trim() || code,
                    symbol: currencies.currency_symbol?.trim() || code,
                };
            }

            return currenciesMap;
        } catch (err) {
            console.error('Error fetching currencies:', err);
            return {};
        }
    };

    const currencyFormatOptions = ({ symbol = '$', code = 'USD' } = {}) => ({
        us: symbol + '12,345.67',
        us_after: '12,345.67' + symbol,
        eu: '12.345,67 ' + symbol,
        eu_before: symbol + '12.345,67',
        ch: code + `12'345.67`,
        iso: code + ' 12,345.67',
        iso_after: '12,345.67 ' + code,
        plain: '12,345.67',
    });
    
    useEffect(() => {
        const loadCurrencies = async () => {
            const currencies = await fetchAllCurrencies();
            if (currencies && Object.keys(currencies).length > 0) {
                setAllCurrencies(currencies);
            }
        };

        loadCurrencies();
    }, []);

    useEffect(() => {
        if (!formValues.currency_symbol && !formValues.currency) return;

        const formats = currencyFormatOptions({
            symbol: formValues.currency_symbol,
            code: formValues.currency,
        });

        setCurrenciesList(formats);

        if (!formValues.format) {
            setFormValues(prev => ({
                ...prev,
                format: Object.keys(formats)[0],
            }));
        }
    }, [formValues.currency, formValues.currency_symbol]);
    
    useEffect(() => {
        if (!formValues.currency && Object.keys(allCurrencies).length > 0) {
            const firstCurrency = Object.keys(allCurrencies)[0];
            setFormValues(prev => ({
                ...prev,
                currency: firstCurrency,
                currency_symbol: allCurrencies[firstCurrency]?.symbol || "$",
            }));
        }
    }, [allCurrencies]);


    useEffect(() => {
        if (!formValues.payment_methods?.length) {
            setFormValues((prev) => ({
                ...prev,
                payment_methods: active_payment_methods || [],
            }));
        }
    }, [active_payment_methods]);

    const handleChange = (key) => (e) => {
        const value = e.target.value;

        if (key === "currency") {
            const currencyData = allCurrencies[value];

            setFormValues((prev) => ({
                ...prev,
                currency: value,
                currency_symbol: currencyData?.symbol || "$",
            }));
            return;
        }

        setFormValues((prev) => ({ ...prev, [key]: value }));
    };

    const handlePaymentToggle = (methodKey, isChecked) => {
        setFormValues((prev) => {
            const current = Array.isArray(prev.payment_methods) ? prev.payment_methods : [];
            const updated = isChecked
                ? [...new Set([...current, methodKey])]
                : current.filter((m) => m !== methodKey);
            return { ...prev, payment_methods: updated };
        });
    };

    const PaymentMethods = Object.keys(EASYCOMMERCE.all_payment_methods || {}).map(key => ({
        name: key,
        label: EASYCOMMERCE.all_payment_methods[key].label,
        icon: EASYCOMMERCE.all_payment_methods[key].icon
    }));

    return (
        <div className="bg-cover bg-center w-full flex flex-col items-center">
            <div className="mb-8 font-inter">
                <h2 className='text-2xl text-center  text-ec-title font-medium'>
                    {__('Payment Setup', 'easycommerce')}
                </h2>

                <p className="text-[#606060] text-[16px] text-center mt-2 max-w-xl">
                    {__('Fill in your payment information so your store is ready to accept payments safely and efficiently. You can change this anytime.', 'easycommerce')}
                </p>
            </div>
            <div className="w-full">
                <div className="flex flex-col w-full space-y-6">
                    <div className="flex flex-col space-y-2">
                        <label className="text-ec-body text-[16px] font-medium">
                            Currency
                        </label>
                        <select
                            className="easycommerce-wizard-input w-full"
                            name="payment-pricing-currency"
                            value={formValues.currency || ""}
                            onChange={handleChange("currency")}
                        >
                            <option value="">Select Your Currency</option>
                            {Object.entries(allCurrencies)
                                .sort(([, a], [, b]) => a.name.localeCompare(b.name))
                                .map(([code, data]) => (
                                    <option key={code} value={code}>
                                        {data.name} 
                                    </option>
                                ))
                            }
                        </select>
                        <p className="text-[#606060] text-[14px] mt-1">
                            {__('Select the currency your store will use for all prices and transactions.', 'easycommerce')}
                        </p>
                    </div>
                    <div className="flex flex-col space-y-2">
                        <label className="text-ec-body text-[16px] font-medium">
                            Currency Format
                        </label>
                        <select
                            className="easycommerce-wizard-input w-full"
                            name="payment-pricing-format"
                            value={formValues.format || ""}
                            onChange={handleChange("format")}
                        >
                            <option value="">Select Your Currency Format</option>
                            {Object.entries(currenciesList).map(([key, val]) => (
                                <option key={key} value={key}>
                                    {val}
                                </option>
                            ))}
                        </select>
                        <p className="text-[#606060] text-[14px] mt-1">
                            {__('Select the format you want for displaying prices in your store.', 'easycommerce')}
                        </p>
                    </div>
                    <div className="flex flex-col">
                        <label className="text-ec-body text-[16px] font-medium">
                            Payment Methods
                        </label>
                        <div>
                            {PaymentMethods.map(({ name, label, icon }) => {
                                const checked = Array.isArray(formValues.payment_methods)
                                    ? formValues.payment_methods.includes(name)
                                    : false;
                                return (
                                    <div
                                        key={name}
                                        className="group flex items-center py-5 gap-3 border-b border-ec-table-stock"
                                    >
                                        <input
                                            name="payment-methods-active_methods"
                                            type="checkbox"
                                            id={name}
                                            value={name}
                                            checked={checked}
                                            onChange={(e) =>
                                                handlePaymentToggle(name, e.target.checked)
                                            }
                                            className="easycommerce-checkbox-input"
                                        />
                                        <label
                                            htmlFor={name}
                                            className="flex gap-2 items-center cursor-pointer text-ec-body text-[16px]"
                                        >
                                        <div className="border border-ec-table-stock rounded-md w-[80px] h-[37px] flex items-center justify-center">
                                            <img src={icon} alt={name} />
                                        </div>
                                            {label}
                                        </label>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Payment;
