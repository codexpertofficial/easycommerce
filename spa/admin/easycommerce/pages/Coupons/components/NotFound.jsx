import React from "react";

import { __ } from "@wordpress/i18n";

const noCouponsIcon = `${EASYCOMMERCE.assets}admin/img/coupon/no-coupon.png`;

const NotFound = () => {
    return (
        <div className="w-full h-full pt-[130px]  bg-white flex justify-center items-center">
            <div className="min-w-[320px] flex flex-col justify-between items-center gap-[30px]">
                <img
                    src={noCouponsIcon}
                    className="w-[150px] h-[150px] pointer-events-none"
                />

                <div className="flex flex-col items-center gap-6">
                    <p className="font-inter font-medium text-2xl leading-8 text-ec-title">
                        { __( "No coupons found", "easycommerce" ) }
                    </p>
                    <p className="font-inter text-base leading-[26px] text-ec-light-black w-[300px] text-center">
                        { __( "All type of Coupons activities will appear here once they occur.", "easycommerce" ) }
                    </p>
                    <a
                        href="#/coupons/new"
                        className="w-[124px]  h-11 font-inter text-base leading-[26px] text-ec-primary 
                        border rounded-lg border-ec-primary flex justify-center items-center hover:bg-ec-primary 
                        hover:text-white focus:shadow-none focus:bg-ec-primary focus:text-white active:bg-ec-primary 
                        active:text-white active:shadow-none ease-in-out duration-500"
                    >
                        { __( "Add Coupon", "easycommerce" ) }
                    </a>
                </div>
            </div>
        </div>
    );
};

export default NotFound;
