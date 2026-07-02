import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import StatusDropdown from '../../components/StatusDropdown';
import FullfillmentDropdown from '../../components/FullfillmentDropdown';
import RefundModal from './Modal/RefundModal';


const orderDateIcon = `${EASYCOMMERCE.assets}admin/img/icons/order/date.png`;
const orderTotalIcon = `${EASYCOMMERCE.assets}admin/img/icons/order/total.png`;
const orderStatusIcon = `${EASYCOMMERCE.assets}admin/img/icons/order/status.png`;
const paymentMethodIcon = `${EASYCOMMERCE.assets}admin/img/icons/order/payment.png`;
const statusEditIcon = `${EASYCOMMERCE.assets}admin/img/icons/order/edit.png`;
const fulfillmentIcon = `${EASYCOMMERCE.assets}admin/img/icons/order/fullfillment.png`;
const transectionId = `${EASYCOMMERCE.assets}admin/img/icons/order/transaction.png`;

// JS filter hook for extending order data
const applyFilters = (hookName, ...params) => {
    if (
        window.easycommerceFilters &&
        typeof window.easycommerceFilters[hookName] === 'function'
    ) {
        const result = window.easycommerceFilters[hookName](...params);
        return Array.isArray(result) ? result : params[0];
    }
    return params[0];
};

const fulfillStatusColors = {
    delivered: {
        color: '#00CD55',
        background: '#00CD550D',
    },
    fulfilled: {
        color: '#00CD55',
        background: '#00CD550D',
    },
    partially_fulfilled: {
        color: '#00CD9D',
        background: '#00CD9D0D',
    },
    returned: {
        color: '#FF2D88',
        background: '#FF2D880D',
    },
    shipped: {
        color: '#4277FF',
        background: '#4277FF0D',
    },
    unfulfilled: {
        color: '#FF4B4E',
        background: '#FF4B4E0D',
    },
};

const removeUnderscore = (status) => {
    if (status.match(/_/g)) {
        return status.replace('_', ' ');
    } else {
        return status;
    }
};

const statusOptions = [
	{ label: "Completed", value: "completed" },
	{ label: "Cancelled", value: "cancelled" },
	{ label: "Partially Refunded", value: "partially_refunded" },
    { label: "Refunded", value: "refunded" },
	{ label: "Pending", value: "pending" },
	{ label: "On hold", value: "on_hold" },
	{ label: "Processing", value: "processing" },
    // { label: "Failed", value: "failed" },
];

const OrderInfo = ({ order, selectModal, setStatusCounts, updateOrder }) => {
    const [isRefundOpen, setIsRefundOpen] = useState(false);
    const paymentGateway = order.payment_method;
    const paymentMethod = EASYCOMMERCE.payment_methods[paymentGateway] || null;

    const paymentIcon = paymentMethod?.icon;

    let OrderInfos = applyFilters(
        'easycommerce_admin_side_order_infos',
        [
            {
                icon: orderDateIcon,
                key: 'Order Date',
                value: (
                    <p className="text-ec-body font-inter font-normal text-sm leading-[26px]">
                        <span className="flex flex-col justify-start items-start">
                            <span>
                                {order.created_at ? order.created_at : 'N/A'}
                            </span>
                        </span>
                    </p>
                ),
            },
            ...(order?.delivery_date
            ? [
                  {
                      icon: orderDateIcon,
                      key: 'Delivery Date',
                      value: (
                          <p className="text-ec-body font-inter font-normal text-sm leading-[26px]">
                              <span>{order.delivery_date}</span>
                          </p>
                      ),
                  },
              ]
            : []),
            {
                icon: orderTotalIcon,
                key: 'Order Total',
                value: (
                    <p className="text-ec-body font-inter font-normal text-sm leading-[26px]">
                        {order.total}
                    </p>
                ),
            },
            ...(
                order.refunded_total && parseFloat(order.refunded_total.replace(/[^0-9.]/g, "")) > 0
                ? [
                    {
                        icon: orderTotalIcon,
                        key: 'Order Refund',
                        value: (
                            <p className="text-ec-body font-inter font-normal text-sm leading-[26px]">
                                {order.refunded_total}
                            </p>
                        ),
                    }
                ] : []
            ),
            {
                icon: orderStatusIcon,
                key: 'Order Status',
                value: (
                    <div className="flex justify-end items-center gap-4">
                        <StatusDropdown
							options={statusOptions}
							orderId={order.id}
                            value={order.status} 
							placeholder="Select status"
							width="116px"
							menuWidth="150px"
							prevStatus={order?.status}
							onBeforeStatusChange={(nextStatus) => {
								if (nextStatus === 'partially_refunded' || nextStatus === 'refunded') {
									setIsRefundOpen(true);
									return true;
								}
								return false;
							}}
							onStatusChange={(prev, next) => {
							setStatusCounts((counts) => {
								const updated = { ...counts };

									if (prev && updated[prev] > 0) {
										updated[prev] -= 1;
									}

									if (next) {
										updated[next] = (updated[next] || 0) + 1;
									}

									return updated;
								});
							}}
						/>
                    </div>
                ),
            },
            {
                icon: fulfillmentIcon,
                key: 'Fulfillment Status',
                value: (
                    <div className="flex justify-end items-center gap-4">
                        
                        <FullfillmentDropdown
							orderId={order.id}
							value={order.fulfill_status}
							placeholder="Select status"
							width="116px"
							menuWidth="150px"
							prevStatus={order?.fulfill_status}
							onStatusChange={(prev, next) => {
								setStatusCounts((counts) => {
									const updated = { ...counts };

										if (prev && updated[prev] > 0) {
											updated[prev] -= 1;
										}

										if (next) {
											updated[next] = (updated[next] || 0) + 1;
										}

									    return updated;
								});
							}}
						/>
                    </div>
                ),
            },
            {
                icon: paymentMethodIcon,
                key: 'Payment Method',
                value: (
                    <p className="text-ec-body font-inter font-normal text-sm leading-[26px] flex items-center">
                        <div className="border border-ec-table-stock mr-2 rounded flex items-center gap-2">
                            {paymentIcon ? (
                                <img
                                    src={paymentIcon}
                                    alt={paymentMethod?.label || 'Payment Icon'}
                                    className="w-full h-10 object-contain p-1"
                                />
                            ) : (
                                <span className="text-ec-body font-inter font-normal text-sm leading-[26px] p-2">
                                    {order.payment_method}
                                </span>
                            )}
                        </div>
                    </p>
                ),
            },
            {
                icon: transectionId,
                key: order.transactions ? 'Payment URL' : 'Transaction ID',
                value: (
                    <p className="text-ec-body font-inter font-normal text-sm leading-[26px] flex items-center">
                        <div className="mr-2 p-2 flex items-center gap-2">
                            {order.transactions ? (
                                order.transactions.transaction_id
                            ) : order.status === 'pending' && EASYCOMMERCE.payment_page_url ? (
                                <a
                                    href={`${EASYCOMMERCE.payment_page_url}?order_id=${order.id}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-ec-primary underline hover:text-ec-secondary transition-colors duration-200"
                                >
                                    {`${EASYCOMMERCE.payment_page_url}?order_id=${order.id}`}
                                </a>
                            ) : (
                                "N/A"
                            )}
                        </div>
                    </p>
                ),
            },
        ],
        order.id
    );
    
    return (
        <>
        <div className="bg-white rounded-2xl">
            <div className="flex flex-col border-b border--ec-table-stock pt-4 px-6">
                <h3 className="text-ec-title text-xl font-medium font-inter leading-8 pb-4">
                   Overview
                </h3>
            </div>
            <div className="p-6">
                {OrderInfos.map((item, index) => (
                    <div
                        key={index}
                        className="flex items-center justify-between border-b border-dashed border-ec-table-stock h-[60px] last:border-b-0"
                    >
                        <div className="flex items-center">
                            <img
                                className="w-5 h-5 mr-2"
                                src={item.icon}
                                alt=""
                            />
                            <p className="text-ec-title font-inter font-normal leading-[26px] text-base">
                                {item.key}
                            </p>
                        </div>
                        {item.value}
                    </div>
                ))}
            </div>
        </div>
        {isRefundOpen && (
            <RefundModal
                hideModal={() => setIsRefundOpen(false)}
                order={order}
                updateOrder={updateOrder}
            />
        )}
        </>
    );
};

export default OrderInfo;
