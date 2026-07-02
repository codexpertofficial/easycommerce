import React from "react";

const RegionsHeader = () => {
    return (
        <thead>
            <tr>
                <th className="w-[22%] text-start text-ec-body pb-2 text-sm font-normal leading-4 font-inter pl-[7px]">
                    Country
                </th>
                <th className="w-[22%] text-start text-ec-body pb-2 text-sm font-normal leading-4 font-inter pl-[7px]">
                    State
                </th>
                <th className="w-[22%] text-start text-ec-body pb-2 text-sm font-normal leading-4 font-inter pl-[7px]">
                    City
                </th>
                <th className="w-[22%] text-start text-ec-body pb-2 text-sm font-normal leading-4 font-inter pl-[7px]">
                    Rate (%)
                </th>
                <th className="w-[22%]"></th>
            </tr>
        </thead>
    );
};

export default RegionsHeader;
