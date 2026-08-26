import React from "react";
import { __ } from "@wordpress/i18n";

const RegionsHeader = () => {
    return (
        <thead>
            <tr>
                <th className="w-[19%] text-start text-ec-body pb-2 text-sm font-normal leading-4 font-inter pl-[7px]">
                    { __( "Country", "easycommerce" ) }
                </th>
                <th className="w-[19%] text-start text-ec-body pb-2 text-sm font-normal leading-4 font-inter pl-[7px]">
                    { __( "State", "easycommerce" ) }
                </th>
                <th className="w-[19%] text-start text-ec-body pb-2 text-sm font-normal leading-4 font-inter pl-[7px]">
                    { __( "City", "easycommerce" ) }
                </th>
                <th className="w-[19%] text-start text-ec-body pb-2 text-sm font-normal leading-4 font-inter pl-[7px]">
                    { __( "Postcode", "easycommerce" ) }
                </th>
                <th className="w-[19%] text-start text-ec-body pb-2 text-sm font-normal leading-4 font-inter pl-[7px]">
                    { __( "Rate (%)", "easycommerce" ) }
                </th>
                <th className="w-[5%]"></th>
            </tr>
        </thead>
    );
};

export default RegionsHeader;
