import React from "react";
import { Slot } from '@wordpress/components';
import {Link} from "react-router-dom";

const viewRightIcon = `${EASYCOMMERCE.assets}public/img/icons/view-right-icon.png`;
const orderViewIcon = `${EASYCOMMERCE.assets}public/img/icons/dashboard-view-icon.png`;

const statusColorMapping = {
    processing: { textColor: "#F59E0B", bgColor: "#F59E0B0D" },
    completed: { textColor: "#009D68", bgColor: "#009D680D" },
    cancelled: { textColor: "#EF4444", bgColor: "#EF44440D" },
    pending: { textColor: "#F97316", bgColor: "#F973160D" },
    refunded: { textColor: "#FF001F", bgColor: "#FF001F1A" },
    partially_refunded: { textColor: "#F89102", bgColor: "#F891021A" },
    on_hold: { textColor: "#555DFF", bgColor: "#555DFF1A" },
};

const RecentOrders = ({ orders }) => {
    if (orders.length === 0) {
        return (
            <div className="easycommerce-dashboard-section mt-8">
                <p>You have no orders.</p>
            </div>
        );
    }

    return (
        <div className="easycommerce-dashboard-section mt-8 flex flex-col gap-4">
            <div className="flex justify-between items-center">
                <h3 className="easycommerce-dashboard-section-title !text-lg sm:!text-2xl">
                    Recent Orders
                </h3>

                <Link
                    to="orders"
                    className="view-more-btn font-inter font-medium text-base leading-[26px]
                    flex items-center justify-end gap-2 border-b border-b-ec-placeholder text-ec-placeholder"
                >
                    View More
                    <span>
                        <img
                            src={viewRightIcon}
                            alt="right-icon"
                            className="w-4 h-3"
                        />
                    </span>
                </Link>
            </div>

            <div className="easycommerce-recent-order-table w-full border border-ec-border rounded-lg overflow-x-auto">
                <table className="w-full min-w-[500px] border-none m-0">
                    <thead className="easycommerce-dash-roth">
                        <tr className="h-[42px]">
                            <th className="font-inter font-medium text-left rtl:text-right rtl:pr-4 text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0">
                                Order ID
                            </th>
                            <th className="font-inter font-medium text-left rtl:text-right text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0">
                                Date
                            </th>
                            <th className="font-inter font-medium text-left rtl:text-right text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0">
                                Amount
                            </th>
                            <th className="font-inter font-medium text-left rtl:text-right text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0">
                                Status
                            </th>
                            <th className="font-inter font-medium text-left rtl:text-right text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        {orders.map((order, index) => (
                            <tr
                                key={index}
                                className={`h-[76px] ${
                                    index === orders.length - 1
                                        ? "last-row"
                                        : ""
                                }`}
                            >
                                <td className="font-inter font-normal text-left rtl:text-right rtl:pr-4 text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0">
                                    #{order.id}
                                </td>
                                <td className="font-inter font-normal text-left rtl:text-right text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0">
                                    {/* {order.created_at} */}
                                    <p className="text-ec-body font-inter font-normal text-base leading-[26px] mb-1">
                                        {order.created_at ? order.created_at : "N/A"}
                                    </p>
                                    <span className="text-ec-placeholder text-sm font-inter leading-4">
                                        {order.created_time ? order.created_time : "N/A"}
                                    </span>
                                </td>
                                <td className="font-inter font-normal text-left rtl:text-right text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0">
                                    {EASYCOMMERCE.currency_symbol}{order.total}
                                </td>
                                <td className="font-inter font-normal text-left rtl:text-right text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0">
                                    <span
                                        className="p-[3px] rounded-[4px] text-sm leading-4 font-medium"
                                        style={{
                                            color:
                                                statusColorMapping[order.status]
                                                    ?.textColor || "#000",
                                            backgroundColor:
                                                statusColorMapping[order.status]
                                                    ?.bgColor || "#0000000D",
                                        }}
                                    >
                                        {EASYCOMMERCE.order_statuses?.[order.status] ?? order.status}
                                    </span>
                                </td>
                                <td className="font-inter easycommerce-dashboard-order-view font-normal text-left rtl:text-right text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0">
                                    <Link to={`/orders/${order.id}`}>
                                        <img
                                            src={orderViewIcon}
                                            alt="view-icon"
                                            className="w-[42px] h-6 pointer-events-none"
                                        />
                                    </Link>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>

                {/* Slot for additional order information */}
                <Slot name="easycommerce.dashboard.recent.orders.extra" props={{ orders }} />
            </div>
        </div>
    );
};

export default RecentOrders;
