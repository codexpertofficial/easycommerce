import React from "react";
import { __ } from "@wordpress/i18n";

const THead = () => {
    const lables = [__("Products", "easycommerce"), __("Quantity", "easycommerce"), __("Total", "easycommerce")];

    return (
        <thead className="bg-[#00000008]">
            {/* <tr className="grid grid-cols-12 gap-0 ">
                {lables.map((label, index) => (
                    <th
                        key={index}
                        className={`${
                            index === 0
                            ? "col-span-8 text-left rounded-l-lg"
                            : index === lables.length - 1
                            ? "col-span-2 text-right rounded-r-lg"
                            : "col-span-2 text-right"
                        } font-inter text-base font-normal leading-8 text-ec-title px-6 py-4`}
                    >
                        {label}
                    </th>
                ))}
            </tr> */}
            <div role="row" className="grid grid-cols-12 bg-[#00000008] rounded-lg h-[44px] items-center ">
                {lables.map((label, index) => (
                    <div
                        key={index}
                        role="columnheader"
                        className={`${
                            index === 0
                            ? "col-span-8 text-left rounded-l-lg"
                            : index === lables.length - 1
                            ? "col-span-2 text-right rounded-r-lg"
                            : "col-span-2 text-right"
                        } font-inter text-base font-normal leading-8 text-ec-title px-6 `}
                        >
                        {label}
                    </div>
                ))}
            </div>
        </thead>
    );
};

export default THead;
