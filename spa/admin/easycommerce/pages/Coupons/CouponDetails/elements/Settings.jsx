import React from "react";

import { __ } from "@wordpress/i18n";

// Components
import MultiSelect from "../../components/MultiSelect";
import PriceInput from "../../components/PriceInput";

const Settings = ({
    couponData,
    handleCouponData,
    handleAddProduct,
    handleRemoveProduct
}) => {
    const calenderIcon = `${EASYCOMMERCE.assets}admin/img/coupon/calender.png`;
    return (
        <div className="col-span-1 border border-ec-border rounded-xl bg-white self-start">
            <h3 className="font-inter font-medium text-ec-title text-lg leading-8 capitalize border-b border-ec-border p-6">
                { __( "Conditions", "easycommerce" ) }
            </h3>

            <div className="flex flex-col gap-6 p-6 pb-8">
                <MultiSelect
                    id={"products"}
                    label={ __( "Discount applies to specific products?", "easycommerce" ) }
                    selectedValues={couponData.rules.products}
                    placeholder={ __( "Search for products", "easycommerce" ) }
                    handleSelect={handleAddProduct}
                    handleRemove={handleRemoveProduct}
                />

                <PriceInput
                    label={ __( "Minimum Spend", "easycommerce" ) }
                    Icon={EASYCOMMERCE.currency_symbol}
                    id={"min_spend"}
                    value={couponData.rules.min_spend}
                    onChange={(e) => handleCouponData("min_spend", e.target.value)}
                />

                <PriceInput
                    label={ __( "Maximum Spend", "easycommerce" ) }
                    Icon={EASYCOMMERCE.currency_symbol}
                    id={"max_spend"}
                    value={couponData.rules.max_spend}
                    onChange={(e) => handleCouponData("max_spend", e.target.value)}
                />
                <div
                    onClick={() => document.getElementById('start_date').showPicker()} className="cursor-pointer" >
                    <h5 className="text-base text-ec-title font-inter mb-[6px]">{ __( "Start Date", "easycommerce" ) }</h5>
                    <div className="h-ec-input rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus-within:border-ec-primary focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out overflow-hidden flex">
                        <div className="h-full w-[50px] flex items-center justify-center border-r border-ec-table-stock bg-[#F9F9F9]">
                            <img src={calenderIcon} alt={ __( "calendar", "easycommerce" ) } className="w-[18px] h-[21px]" />
                        </div>
                        <input
                            id="start_date"
                            label="Coupon Start Date"
                            value={couponData.rules.start_date}
                            min={new Date().toISOString().split("T")[0]}
                            placeholder="DD-MM-YYYY"
                            type="date"
                            onChange={(e) => handleCouponData("start_date", e.target.value)}
                            className="w-[calc(100%_-_50px)] h-full border-none outline-none shadow-none appearance-none pr-2 bg-white
                            [&::-webkit-calendar-picker-indicator]:opacity-0
                            [&::-webkit-calendar-picker-indicator]:cursor-pointer"
                        />
                    </div>
                </div>

                <div
                    onClick={() => document.getElementById('end_date').showPicker()} className="cursor-pointer" >
                    <h5 className="text-base text-ec-title font-inter mb-[6px]">{ __( "End Date", "easycommerce" ) }</h5>
                    <div className="h-ec-input rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus-within:border-ec-primary focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out overflow-hidden flex">
                        <div className="h-full w-[50px] flex items-center justify-center border-r border-ec-table-stock bg-[#F9F9F9]">
                            <img src={calenderIcon} alt={ __( "calendar", "easycommerce" ) } className="w-[18px] h-[21px]" />
                        </div>
                        <input
                            id="end_date"
                            label="Coupon End Date"
                            value={couponData.rules.end_date}
                            min={new Date().toISOString().split("T")[0]}
                            placeholder="DD-MM-YYYY"
                            type="date"
                            onChange={(e) => handleCouponData("end_date", e.target.value)}
                            className="w-[calc(100%_-_50px)] h-full border-none outline-none shadow-none appearance-none pr-2 bg-white
                            [&::-webkit-calendar-picker-indicator]:opacity-0
                            [&::-webkit-calendar-picker-indicator]:cursor-pointer"
                        />
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Settings;
