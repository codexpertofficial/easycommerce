import React from "react";

const TableHeader = () => {
    return (
        <>
            <thead>
                <tr className="border-b border-ec-border">
                    <th className="font-inter font-medium text-base text-ec-placeholder cursor-pointer text-left py-6 lg:w-[16%] md:w-[16%]">
                        Name
                    </th>
                    <th className="font-inter font-medium text-base text-ec-placeholder cursor-pointer text-left py-6 lg:w-[16%] md:w-[16%]">
                        Email
                    </th>
                    <th className="font-inter font-medium text-base text-ec-placeholder cursor-pointer text-left py-6 lg:w-[16%] md:w-[16%]">
                        Customer Since
                    </th>
                    <th className="font-inter font-medium text-base text-ec-placeholder cursor-pointer text-left py-6 lg:w-[12%] md:w-[14%]">
                        Total Orders
                    </th>
                    <th className=" font-inter font-medium text-base text-ec-placeholder cursor-pointer text-left py-6 lg:w-[12%] md:w-[12%]">
                        Lifetime Value
                    </th>
                    <th className="font-inter font-medium text-base text-ec-placeholder cursor-pointer text-left py-6 lg:w-[12%] md:w-[12%]">
                        Avg. Order Value
                    </th>
                    <th className="font-inter font-medium text-base text-ec-placeholder cursor-pointer text-left py-6 lg:w-[12%] md:w-[12%]">
                        Last Order
                    </th>
                    <th
                        className="font-inter font-medium text-base text-left py-6 lg:w-[14%] md:w-[14%]"
                        colSpan="2"
                    ></th>
                </tr>
            </thead>
        </>
    );
};

export default TableHeader;
