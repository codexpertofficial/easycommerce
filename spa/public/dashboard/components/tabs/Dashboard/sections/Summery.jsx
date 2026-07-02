import React from "react";
import { applyFilters } from '@wordpress/hooks';

// icons
const totalOrdersIcon = `${EASYCOMMERCE.assets}public/img/icons/total-orders.png`;
const totalSpentIcon = `${EASYCOMMERCE.assets}public/img/icons/total-spent.png`;
const avgOrderValueIcon = `${EASYCOMMERCE.assets}public/img/icons/avg-order-value.png`;
const joiningDateIcon = `${EASYCOMMERCE.assets}public/img/icons/joining-date.png`;

const Summery = ({ data }) => {
    /**
     * Filters the dashboard summary items.
     *
     * @since 1.0.0
     * @param {Array} summaryItems Array of summary item objects.
     * @param {Object} data The data object passed to the component.
     */
    const defaultSummaryItems = [
        {
            title: "Total Orders",
            value: data?.count || "N/A",
            icon: totalOrdersIcon,
        },
        {
            title: "Total Spent",
            value: data?.ltv || "N/A",
            icon: totalSpentIcon,
        },
        {
            title: "Average Orders",
            value: data?.aov || "N/A",
            icon: avgOrderValueIcon,
        },
        {
            title: "Customer since",
            value: data?.since || "N/A",
            icon: joiningDateIcon,
        },
    ];
    const summaryItems = applyFilters('easycommerce.dashboard.summary.items', defaultSummaryItems, data);

    return (
        <div
            className="easycommerce-dashboard-section col-span-1 rounded-[10px] flex flex-col gap-[18px]"
        >
            <h3 className="easycommerce-dashboard-section-title !text-lg sm:!text-2xl">
                Summary
            </h3>

            <div className="easycommerce-dashboard-summery-list grid grid-cols-1 sm:grid-cols-2 gap-3">
                {summaryItems.map((item, index) => (
                    <div
                        key={index}
                        className="easycommerce-summary-item col-span-1 flex justify-start items-center gap-4 px-4 py-[14px] border 
                        border-ec-border rounded-[10px]"
                    >
                        <img
                            src={item.icon}
                            alt="icon"
                            className="w-10 h-[37px] pointer-events-none"
                        />

                        <div>
                            <h4 className="font-inter">{item.value}</h4>
                            <p className="font-inter">{item.title}</p>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default Summery;
