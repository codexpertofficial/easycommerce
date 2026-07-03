import React, { useEffect, useState } from "react";
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import TableSkeleton from "../../../../common/TableSkeleton";

const LatestOrders = ({ range }) => {

    const statusColors = {
        completed: {
            color: "var(--color-ec-completedText)",
            background: "var(--color-ec-completedBg)",
            border: "1px solid var(--color-ec-completedBorder)",
        },
        cancelled: {
            color: "var(--color-ec-cancelledText)",
            background: "var(--color-ec-cancelledBg)",
            border: "1px solid var(--color-ec-cancelledBorder)",
        },
        refunded: {
            color: "var(--color-ec-refundedText)",
            background: "var(--color-ec-refundedBg)",
            border: "1px solid var(--color-ec-refundedBorder)",
        },
        partially_refunded: {
            color: "var(--color-ec-partiallyRefundedText)",
            background: "var(--color-ec-partiallyRefundedBg)",
            border: "1px solid var(--color-ec-partiallyRefundedBorder)",
        },
        pending: {
            color: "var(--color-ec-pendingText)",
            background: "var(--color-ec-pendingBg)",
            border: "1px solid var(--color-ec-pendingBorder)",
        },
        on_hold: {
            color: "var(--color-ec-onHoldText)",
            background: "var(--color-ec-onHoldBg)",
            border: "1px solid var(--color-ec-onHoldBorder)",
        },
        processing: {
            color: "var(--color-ec-processingText)",
            background: "var(--color-ec-processingBg)",
            border: "1px solid var(--color-ec-processingBorder)",
        },
        failed: {
            color: "var(--color-ec-failedText)",
            background: "var(--color-ec-failedBg)",
            border: "1px solid var(--color-ec-failedBorder)",
        },
    };


    const [orders, setOrders] = useState([]);
    const [isLoading, setIsLoading] = useState(false);

    useEffect(() => {
        setIsLoading(true);
        apiFetch({
            path: addQueryArgs('/easycommerce/v1/dashboard/recent_orders', { range }),
        }).then((data) => {
            setIsLoading(false);
            if (data.success) {
                setOrders(data.data.orders);
            }
        });
    }, [range]);

    return (
        <>
            {isLoading ? (
                <TableSkeleton numberOfRows={5} SkeletonHeight={35} />
            ) : orders.length === 0 ? (
                <div className=" text-sm text-ec-body px-[30px] pb-[30px]">
                    No orders found.
                </div>
            ) : (
                <>
                    <div>
                        <div className="flex items-center bg-ec-table-bg py-3 px-3 rounded-lg">
                            <div className="w-[25%] text-sm font-medium text-ec-body capitalize">Order ID</div>
                            <div className="w-[20%] text-sm font-medium text-ec-body capitalize text-center">Amount</div>
                            <div className="w-[40%] text-sm font-medium text-ec-body capitalize text-center">Status</div>
                            <div className="w-[15%] text-sm font-medium text-ec-body capitalize text-end">Action</div>
                        </div>
                        {orders.map((item, index) => (
                            <div key={index} className="flex items-center py-3 px-3 border-b border-[#F8F8F8] last:border-0">
                                <div className="w-[25%] flex flex-col gap-0.5">
                                    <a href={`#/orders/${item.id}`} className="font-inter text-sm text-ec-body focus:shadow-none focus:outline-none">
                                        #{item.id}
                                    </a>
                                    <span className="text-[10px] text-ec-placeholder">
                                        {item.created_at ?? "N/A"}
                                    </span>
                                </div>
                                <div className="w-[20%] text-sm font-inter text-ec-body text-center">
                                    {item.total}
                                </div>
                                <div className="w-[40%] text-center">
                                    <span
                                        className="px-4 py-1 font-inter rounded-[100px] text-[12px]"
                                        style={{
                                            color: statusColors[item.status]?.color,
                                            background: statusColors[item.status]?.background,
                                        }}
                                    >
                                        {item.status.replace(/_/g, " ").toLowerCase().replace(/\b\w/g, char => char.toUpperCase())}
                                    </span>
                                </div>
                                <div className="w-[15%] flex justify-end">
                                    <a href={`#/orders/${item.id}`} className="font-inter text-ec-primary hover:underline text-sm focus:text-ec-primary">
                                        View
                                    </a>
                                </div>
                            </div>
                        ))}
                    </div>
                </>
            )}
        </>
    );
};

export default LatestOrders;
