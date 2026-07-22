import React from "react";
import { __ } from "@wordpress/i18n";

const TableHeader = () => {
    return (
        <>
            <thead>
                <tr className="border-b border-ec-border">
                    <th className="font-inter font-medium text-base text-ec-placeholder cursor-pointer text-left py-6 lg:w-[16%] md:w-[16%]">
                        {__("Name", "easycommerce")}
                    </th>
                    <th className="font-inter font-medium text-base text-ec-placeholder cursor-pointer text-left py-6 lg:w-[16%] md:w-[16%]">
                        {__("Email", "easycommerce")}
                    </th>
                    <th className="font-inter font-medium text-base text-ec-placeholder cursor-pointer text-left py-6 lg:w-[16%] md:w-[16%]">
                        {__("Customer Since", "easycommerce")}
                    </th>
                    <th className="font-inter font-medium text-base text-ec-placeholder cursor-pointer text-left py-6 lg:w-[12%] md:w-[14%]">
                        {__("Total Orders", "easycommerce")}
                    </th>
                    <th className=" font-inter font-medium text-base text-ec-placeholder cursor-pointer text-left py-6 lg:w-[12%] md:w-[12%]">
                        {__("Lifetime Value", "easycommerce")}
                    </th>
                    <th className="font-inter font-medium text-base text-ec-placeholder cursor-pointer text-left py-6 lg:w-[12%] md:w-[12%]">
                        {__("Avg. Order Value", "easycommerce")}
                    </th>
                    <th className="font-inter font-medium text-base text-ec-placeholder cursor-pointer text-left py-6 lg:w-[12%] md:w-[12%]">
                        {__("Last Order", "easycommerce")}
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
