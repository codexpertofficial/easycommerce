import React, { useState } from "react";
import { Slot } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

//Images and Icons
const paymentMethodImage = `${EASYCOMMERCE.assets}public/img/checkout/payment-method.png`;

const debitCredit = `${EASYCOMMERCE.assets}public/icons/debit-credit-40-40.png`;
const internetBanking = `${EASYCOMMERCE.assets}public/icons/internet-banking-40-40.png`;
const googleAppleWallet = `${EASYCOMMERCE.assets}public/icons/google-apple-wallet-40-40.png`;

const PaymentMethod = () => {
    const [paymentDetails, setPaymentDetails] = useState("debit-credit-card");
    const checked = paymentDetails === "debit-credit-card";

    return (
        <>
            <div className="easycommerce-payment-wrapper border border-ec-border bg-white rounded-lg p-[30px] mb-5">
                <div className="flex items-center mb-[30px]">
                    <img
                        src={paymentMethodImage}
                        className="w-[53px] h-[53px] mr-4"
                    />
                    <h3 className="easycommerce-billing-title font-inter leading-8 font-semibold text-xl mb-0">
                        {__( "Payment Method", "easycommerce" )}
                    </h3>
                </div>
                <div>
                    <label
                        htmlFor="easycommerce-credit-card"
                        className="flex items-center gap-5 mb-6"
                    >
                        <input
                            type="radio"
                            id="easycommerce-credit-card"
                            name="easycommerce-payment"
                            checked={checked && true}
                            onClick={() =>
                                setPaymentDetails("debit-credit-card")
                            }
                        />
                        <img src={debitCredit} alt="" />
                        <span className="block text-ec-body font-inter font-medium text-base leading-[26px]">
                            {__( "Debit / Credit card", "easycommerce" )}
                        </span>
                    </label>
                    <label
                        htmlFor="easycommerce-internet-banking"
                        className="flex items-center gap-5 mb-6"
                    >
                        <input
                            type="radio"
                            id="easycommerce-internet-banking"
                            name="easycommerce-payment"
                            onClick={() => setPaymentDetails("")}
                        />
                        <img src={internetBanking} alt="" />
                        <span className="block text-ec-body font-inter font-medium text-base leading-[26px]">
                            {__( "Internet banking", "easycommerce" )}
                        </span>
                    </label>
                    <label
                        htmlFor="easycommerce-google-apple-wallet"
                        className="flex items-center gap-5 mb-6"
                    >
                        <input
                            type="radio"
                            id="easycommerce-google-apple-wallet"
                            name="easycommerce-payment"
                            onClick={() => setPaymentDetails("")}
                        />
                        <img src={googleAppleWallet} alt="" />
                        <span className="block text-ec-body font-inter font-medium text-base leading-[26px]">
                            {__( "Google / Apple Wallet", "easycommerce" )}
                        </span>
                    </label>

                    {/* Slot for additional payment methods */}
                    <Slot name="easycommerce.checkout.payment.methods" props={{ paymentDetails }} />
                 </div>
                {paymentDetails === "debit-credit-card" ? (
                    <div className="ml-10">
                        <label htmlFor="" className="block mb-6">
                            <span
                                className="font-inter text-black text-base font-normal block leading-[26px]
                             mb-2"
                            >
                                {__( "Card Number", "easycommerce" )}
                            </span>
                            <input
                                className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                                type="text"
                                placeholder="XXX XXXX XXXX XXXX"
                            />
                        </label>
                        <label htmlFor="" className="block mb-6">
                            <span className="font-inter text-black text-base font-normal block leading-[26px] mb-2">
                                {__( "Name on Card", "easycommerce" )}
                            </span>
                            <input
                                className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                                type="text"
                                placeholder={__( "Enter card holder name", "easycommerce" )}
                            />
                        </label>
                        <div className="flex items-center gap-3 mb-6">
                            <label htmlFor="">
                                <span className="font-inter text-black text-base font-normal block leading-[26px] mb-2">
                                    {__( "Expiration date (MM/YY)", "easycommerce" )}
                                </span>
                                <input
                                    className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                                    type="text"
                                    placeholder="MM/YY"
                                />
                            </label>
                            <label htmlFor="">
                                <span className="font-inter text-black text-base font-normal block leading-[26px] mb-2">
                                    {__( "CVC", "easycommerce" )}
                                </span>
                                <input
                                    className="easycommerce-checkout_input w-full text-ec-body 
                            placeholder:text-ec-placeholder font-inter text-base leading-[26px] py-[7px] px-[10px]"
                                    type="text"
                                    placeholder={__( "CVC", "easycommerce" )}
                                />
                            </label>
                        </div>
                    </div>
                ) : null}
            </div>
        </>
    );
};

export default PaymentMethod;
