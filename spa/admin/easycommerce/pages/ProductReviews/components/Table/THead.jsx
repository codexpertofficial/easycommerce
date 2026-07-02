import React from "react";

const THead = ({ tableColumns, allChecked, toggleAll }) => {
    const columnHeaders = {
        customer: "Customer",
        product: "Product",
        content: "Review Content",
        rating: "Rating",
        date: "Date",
        status: "Status",
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