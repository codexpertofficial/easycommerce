import React from "react";

import { __ } from "@wordpress/i18n";

// Components
import RadioInput from "../../components/RadioInput";
import PriceInput from "../../components/PriceInput";
import MultiSelect from "../../components/MultiSelect";

const Offers = ({ couponData, handleCouponData, handleFreeProduct, handleRemoveFreeProduct }) => {
    return (
        <div className="col-span-1 border border-ec-border rounded-xl bg-white">
            <h3 className="font-inter font-medium text-ec-title text-lg leading-8 capitalize border-b border-ec-border p-6">
                { __( "Offers", "easycommerce" ) }
            </h3>

            <div className="flex flex-col gap-6 p-6 pb-8">
                <div className="flex flex-col justify-start items-start gap-2">
                    <label
                        id="type"
                        className="font-inter font-normal text-ec-body text-base leading-[26px]"
                    >
                        { __( "Discount Type", "easycommerce" ) }
                    </label>

                    <div className="flex flex-col justify-start items-start gap-4">
                        <RadioInput
                            id="percentage"
                            label={ __( "Percentage Discount", "easycommerce" ) }
                            name="type"
                            value="percentage"
                            checked={ couponData.type === "percentage" }
                            handleChange={ ( e ) => {
                                handleCouponData( "type", e.target.value );
                                handleCouponData( "offer", couponData.offer > 100 ? "" : couponData.offer );
                            } }
                        />

                        <RadioInput
                            id="fixed"
                            label={ __( "Fixed Cart Discount", "easycommerce" ) }
                            name="type"
                            value="fixed"
                            checked={ couponData.type === "fixed" }
                            handleChange={ ( e ) =>
                                handleCouponData( "type", e.target.value )
                            }
                        />

                        <RadioInput
                            id={ "products" }
                            label={ __( "Free Products", "easycommerce" ) }
                            name={ "type" }
                            value={ "products" }
                            checked={ couponData.type === "products" }
                            handleChange={ ( e ) => {
                                handleCouponData( "type", e.target.value );
                                handleCouponData( "offer", [] );
                            } }
                        />

                        <RadioInput
                            id={ "free_shipping" }
                            label={ __( "Free Shipping", "easycommerce" ) }
                            name={ "type" }
                            value={ "free_shipping" }
                            checked={ couponData.type === "free_shipping" }
                            handleChange={ ( e ) =>
                                handleCouponData( "type", e.target.value )
                            }
                        />
                    </div>
                </div>

                { [ "percentage", "fixed" ].includes( couponData.type ) && <PriceInput
                    label={ __( "Offer Amount", "easycommerce" ) }
                    Icon={couponData.type === "percentage" ? "%" : EASYCOMMERCE.currency_symbol}
                    id={"offer"}
                    value={couponData.offer ?? 0}
                    onChange={(e) => {
                        const value = e.target.value;
                        if (couponData.type === "percentage" && value > 100) return;
                        handleCouponData("offer", value);
                    }}
                /> }

                { couponData.type === "products" && <MultiSelect
                    id={ "products_offer" }
                    label={ __( "Select products to give for free", "easycommerce" ) }
                    selectedValues={couponData.offer ?? []}
                    placeholder={ __( "Search for products", "easycommerce" ) }
                    handleSelect={handleFreeProduct}
                    handleRemove={handleRemoveFreeProduct}
                /> }
            </div>
        </div>
    );
};

export default Offers;
