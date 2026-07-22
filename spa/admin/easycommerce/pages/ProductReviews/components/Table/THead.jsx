import React from "react";
import { __ } from "@wordpress/i18n";

const THead = ({ tableColumns, allChecked, toggleAll }) => {
    const columnHeaders = {
        customer: __("Customer", "easycommerce"),
        product: __("Product", "easycommerce"),
        content: __("Review Content", "easycommerce"),
        rating: __("Rating", "easycommerce"),
        date: __("Date", "easycommerce"),
        status: __("Status", "easycommerce"),
    };

    return (
        <thead>
            <tr className="h-[44px]">
                {tableColumns.map((column) => (
                    <th
                        key={column}
                        className="bg-ec-modal items-center justify-start gap-2 first:rounded-l-lg last:rounded-r-lg border-r-0 font-inter font-normal text-sm pl-5 text-ec-title text-left rtl:text-right rtl:pr-5"
                    >
                        {columnHeaders[column]}
                    </th>
                ))}
            </tr>
        </thead>
    );
};

export default THead;