import React from "react";
import { applyFilters } from '@wordpress/hooks';
import { __ } from "@wordpress/i18n";

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
            title: __( "Total Orders", "easycommerce" ),
            value: data?.count || __( "N/A", "easycommerce" ),
            icon: totalOrdersIcon,
        },
        {
            title: __( "Total Spent", "easycommerce" ),
            value: data?.ltv || __( "N/A", "easycommerce" ),
            icon: totalSpentIcon,
        },
        {
            title: __( "Average Orders", "easycommerce" ),
            value: data?.aov || __( "N/A", "easycommerce" ),
            icon: avgOrderValueIcon,
        },
        {
            title: __( "Customer since", "easycommerce" ),
            value: data?.since || __( "N/A", "easycommerce" ),
            icon: joiningDateIcon,
        },
    ];
    const summaryItems = applyFilters('easycommerce.dashboard.summary.items', defaultSummaryItems, data);

    return (
        <div
            className="easycommerce-dashboard-section col-span-1 rounded-[10px] flex flex-col gap-[18px]"
        >
            <h3 className="easycommerce-dashboard-section-title !text-lg sm:!text-2xl">
                {__( "Summary", "easycommerce" )}
            </h3>

            <div className="easycommerce-dashboard-summery-list grid grid-cols-1 sm:grid-cols-2 gap-4">
                {summaryItems.map((item, index) => (
                    <div
                        key={index}
                        className="easycommerce-summary-item col-span-1 flex justify-start items-center gap-4 p-5 rounded-2xl border border-ec-border bg-white transition-shadow duration-200 hover:shadow-[0_4px_20px_-12px_rgba(18,3,80,0.16)]"
                    >
                        <img
                            src={item.icon}
                            alt={__( "icon", "easycommerce" )}
                            className="w-11 h-11 object-contain pointer-events-none shrink-0"
                        />

                        <div className="flex flex-col gap-0.5 min-w-0">
                            <h4 className="font-inter font-semibold text-lg leading-tight text-ec-title truncate">{item.value}</h4>
                            <p className="font-inter text-sm font-medium text-ec-light-black">{item.title}</p>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default Summery;
