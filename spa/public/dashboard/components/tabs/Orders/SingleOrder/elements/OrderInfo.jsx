import React from "react";
import { __ } from "@wordpress/i18n";
import StatusBadge from "../../../../common/StatusBadge";

const applyFilters = (hookName, data, ...args) => {
    if (window.easycommerceFilters && typeof window.easycommerceFilters[hookName] === 'function') {
        return window.easycommerceFilters[hookName](data, ...args);
    }
    return data;
};

const formatStatus = (status, type = 'order') => {
    if (!status) return __( 'N/A', 'easycommerce' );
    const statuses = type === 'fulfill'
        ? EASYCOMMERCE.fulfill_statuses
        : EASYCOMMERCE.order_statuses;
    return statuses?.[status] ?? status;
};

const OrderInfo = ({ order }) => {
    const orderData = [
        { label: __( 'Order ID', 'easycommerce' ), value: `#${order?.id || __( 'N/A', 'easycommerce' )}` },
        { label: __( 'Order Date', 'easycommerce' ), value: order?.created_at || __( 'N/A', 'easycommerce' ) },
        { label: __( 'Order Status', 'easycommerce' ), value: <StatusBadge status={order?.status} /> },
        {
            label: __( 'Fulfilment Status', 'easycommerce' ),
            value: (
                <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold capitalize bg-ec-table-bg text-ec-body">
                    {formatStatus(order?.fulfill_status, 'fulfill')}
                </span>
            ),
        },
        { label: __( 'Total', 'easycommerce' ), value: order?.total || __( 'N/A', 'easycommerce' ), className: 'font-bold text-ec-primary' },
        { label: __( 'Payment Method', 'easycommerce' ), value: order?.payment_method || __( 'N/A', 'easycommerce' ), className: 'capitalize' },
    ];
    const filteredOrderData = applyFilters('easycommerce_order_info', orderData, order);

    return (
        <div className="flex flex-col h-full bg-white border border-ec-border rounded-2xl overflow-hidden shadow-[0_2px_16px_-8px_rgba(18,3,80,0.10)]">
            <div className="w-full px-5 py-4 border-b border-ec-border bg-ec-table-bg">
                <h3 className="font-inter easycommerce-dashboard-order-section-title flex items-center gap-2">
                    <svg className="w-4 h-4 text-ec-primary" data-slot="icon" fill="none" stroke="currentColor" strokeWidth="1.7" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                    </svg>
                    {__( 'Order info', 'easycommerce' )}
                </h3>
            </div>

            <div className="w-full h-full flex flex-col gap-0 p-5">
                {filteredOrderData.map((item, index) => (
                    <div key={index} className="w-full py-3 flex justify-between items-center gap-3 border-b border-dashed border-b-ec-border last:border-0">
                        <p className="font-inter font-normal text-sm leading-5 text-ec-placeholder shrink-0">
                            {item.label}
                        </p>
                        <div className={`font-inter font-medium text-sm leading-5 text-ec-body text-right ${item.className || ''}`}>
                            {item.value}
                        </div>
                    </div>
                ))}
                {order.status === 'pending' && EASYCOMMERCE.payment_page_url && order.transactions === null && (
                    <a
                        href={`${EASYCOMMERCE.payment_page_url}?order_id=${order.id}`}
                        className="mt-5 w-full inline-flex justify-center items-center gap-2 text-center no-underline bg-ec-primary text-white font-inter font-medium text-sm leading-[26px] py-2.5 px-8 rounded-xl hover:bg-ec-secondary transition-all ease-in-out duration-300"
                    >
                        <svg className="w-4 h-4" data-slot="icon" fill="none" stroke="currentColor" strokeWidth="1.8" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                        {__( 'Pay Now', 'easycommerce' )}
                    </a>
                )}
            </div>
        </div>
    );
};

export default OrderInfo;
