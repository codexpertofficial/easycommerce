import React from "react";
import { __ } from "@wordpress/i18n";

const PopulateTaxButton = ({ onClick }) => {
    return (
        <div className="w-full flex justify-end items-center gap-[30px] mt-10">
            <button
                type="button"
                className="flex justify-center items-center font-inter border-b border-ec-body text-ec-body focus:shadow-none focus:ec-body text-base"
                onClick={onClick}
            >
                { __( "Populate Tax Rates", "easycommerce" ) }
            </button>
        </div>
    );
};

export default PopulateTaxButton;