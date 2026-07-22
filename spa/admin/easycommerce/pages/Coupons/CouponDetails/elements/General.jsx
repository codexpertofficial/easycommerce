import React from "react";

import { __ } from "@wordpress/i18n";

// Components
import TextField from "../../../../../common/components/inputs/TextField";

const General = ({ couponData, handleCouponData }) => {
    return (
        <div className="col-span-1 border border-ec-border rounded-xl bg-white">
            <h3 className="font-inter font-medium text-ec-title text-lg leading-8 capitalize border-b border-ec-border p-6">
                { __( 'General', 'easycommerce' ) }
            </h3>

            <div className="flex flex-col gap-6 p-6 pb-8">
                <div className="flex flex-col gap-[6px]">
                    <label
                        id={"name"}
                        className="font-inter font-normal text-ec-body text-base leading-[26px]"
                    >
                        { __( 'Name', 'easycommerce' ) }
                    </label>
                    <TextField
                        id={"name"}
                        value={couponData.name}
                        onChange={(e) => handleCouponData("name", e.target.value)}
                        placeholder={ __( 'Coupon Name', 'easycommerce' ) }
                    />
                </div>
                <div className="flex flex-col gap-[6px]">
                    <label
                        id={"code"}
                        className="font-inter font-normal text-ec-body text-base leading-[26px]"
                    >
                        { __( 'Code', 'easycommerce' ) }
                    </label>
                    <TextField
                        id={"code"}
                        value={couponData.code}
                        onChange={(e) => handleCouponData("code", e.target.value)}
                        placeholder={ __( 'Coupon Code', 'easycommerce' ) }
                    />
                </div>
            </div>
        </div>
    );
};

export default General;
