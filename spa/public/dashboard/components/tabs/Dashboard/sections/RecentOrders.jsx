import React from "react";
import { Slot } from '@wordpress/components';
import {Link} from "react-router-dom";
import StatusBadge from "../../../common/StatusBadge";
import ViewButton from "../../../common/ViewButton";
import EmptyState from "../../../common/EmptyState";

const RecentOrders = ({ orders }) => {
    const thClass =
        "font-inter font-semibold text-xs uppercase tracking-wide text-left rtl:text-right text-ec-light-black py-3.5 px-5 border-0";
    const tdClass =
        "font-inter text-sm text-left rtl:text-right text-ec-body py-4 px-5 border-0 border-b border-b-ec-border/70 align-middle";

    return (
        <div className="easycommerce-dashboard-section mt-8 flex flex-col gap-4">
            <div className="flex justify-between items-center">
                <h3 className="easycommerce-dashboard-section-title !text-lg sm:!text-2xl">
                    Recent Orders
                </h3>

                <Link
                    to="orders"
                    className="view-more-btn font-inter font-medium text-sm leading-[26px] flex items-center justify-end gap-1.5 text-ec-primary hover:gap-2.5 transition-all"
                >
                    View More
                    <svg className="w-4 h-4" data-slot="icon" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </Link>
            </div>

            {orders.length === 0 ? (
                <div className="w-full border border-ec-border rounded-2xl bg-white">
                    <EmptyState
                        title="No orders yet"
                        message="When you place an order it will show up here."
                    />
                </div>
            ) : (
                <div className="easycommerce-recent-order-table w-full border border-ec-border rounded-2xl overflow-x-auto bg-white">
                    <table className="w-full min-w-[500px] border-none m-0">
                        <thead className="easycommerce-dash-roth bg-ec-table-bg">
                            <tr>
                                <th className={thClass}>Order ID</th>
                                <th className={thClass}>Date</th>
                                <th className={thClass}>Amount</th>
                                <th className={thClass}>Status</th>
                                <th className={`${thClass} text-right rtl:text-left`}>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            {orders.map((order, index) => (
                                <tr
                                    key={index}
                                    className={`transition-colors duration-150 hover:bg-ec-active ${
                                        index === orders.length - 1 ? "last-row" : ""
                                    }`}
                                >
                                    <td className={`${tdClass} font-semibold text-ec-title`}>
                                        #{order.id}
                                    </td>
                                    <td className={tdClass}>
                                        <p className="text-ec-body font-inter font-medium text-sm leading-[22px] mb-0.5">
                                            {order.created_at ? order.created_at : "N/A"}
                                        </p>
                                        <span className="text-ec-placeholder text-xs font-inter leading-4">
                                            {order.created_time ? order.created_time : "N/A"}
                                        </span>
                                    </td>
                                    <td className={`${tdClass} font-semibold text-ec-title`}>
                                        {EASYCOMMERCE.currency_symbol}{order.total}
                                    </td>
                                    <td className={tdClass}>
                                        <StatusBadge status={order.status} />
                                    </td>
                                    <td className={`${tdClass} easycommerce-dashboard-order-view text-right rtl:text-left`}>
                                        <ViewButton LinkComponent={Link} to={`/orders/${order.id}`} title="View order" />
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {/* Slot for additional order information */}
                    <Slot name="easycommerce.dashboard.recent.orders.extra" props={{ orders }} />
                </div>
            )}
        </div>
    );
};

export default RecentOrders;
