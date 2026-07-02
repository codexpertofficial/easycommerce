import React from "react";

const applyFilters = (hookName, data, ...args) => {
    if (window.easycommerceFilters && typeof window.easycommerceFilters[hookName] === 'function') {
        return window.easycommerceFilters[hookName](data, ...args);
    }
    return data;
};

const formatStatus = (status, type = 'order') => {
    if (!status) return 'N/A';
    const statuses = type === 'fulfill'
        ? EASYCOMMERCE.fulfill_statuses
        : EASYCOMMERCE.order_statuses;
    return statuses?.[status] ?? status;
};

const OrderInfo = ({ order }) => {
    const orderData = [
        { label: 'Order ID', value: order?.id || 'N/A' },
        { label: 'Order Date', value: order?.created_at || 'N/A' },
        { label: 'Order Status', value: formatStatus(order?.status), className: 'text-[#00BF7F]' },
        { label: 'Fulfilment Status', value: formatStatus(order?.fulfill_status, 'fulfill'), className: 'text-[#FFBB00]' },
        { label: 'Total', value: order?.total || 'N/A' },
        { label: 'Payment Method', value: order?.payment_method || 'N/A' },
    ];
    const filteredOrderData = applyFilters('easycommerce_order_info', orderData, order);

    return (
        <div className="flex flex-col">
            <div className="w-full p-4 pb-3 border border-b-0 border-ec-border rounded-tl-lg rounded-tr-lg">
                <h3 className="font-inter easycommerce-dashboard-order-section-title">
                    Order info
                </h3>
            </div>

            <div className="w-full h-full flex flex-col gap-1 p-4 pt-10 border border-ec-border rounded-bl-lg rounded-br-lg">
                {filteredOrderData.map((item, index) => (
                    <div key={index} className="w-full h-[42px] flex justify-between items-center border-b border-dashed border-b-ec-border">
                        <p className="font-inter font-normal text-sm leading-4 text-ec-placeholder">
                            {item.label}
                        </p>
                        <p className={`font-inter font-normal text-sm leading-4 text-ec-body ${item.className || ''}`}>
                            {item.value}
                        </p>
                    </div>
                ))}
                {order.status === 'pending' && EASYCOMMERCE.payment_page_url && order.transactions === null && (
                    <a
                        href={`${EASYCOMMERCE.payment_page_url}?order_id=${order.id}`}
                        className="mt-4 w-full inline-block text-center no-underline bg-ec-primary text-white font-inter font-medium text-base leading-[26px] py-2 px-8 rounded-lg hover:bg-ec-secondary transition-all ease-in-out duration-500"
                    >
                        Pay Now
                    </a>
                )}
            </div>
        </div>
    );
};

export default OrderInfo;
