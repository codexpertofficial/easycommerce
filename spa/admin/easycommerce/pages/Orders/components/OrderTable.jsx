import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import StatusDropdown from './StatusDropdown';
import FullfillmentDropdown from './FullfillmentDropdown';
import RefundModal from '../SingleOrder/components/Modal/RefundModal';

const columnList = [
	{
		title: __('ID', 'easycommerce'),
		name: 'id',
		width: '10',
	},
	{
		title: __('Customer', 'easycommerce'),
		name: 'customer',
		width: '14',
	},
	{
		title: __('Status', 'easycommerce'),
		name: 'status',
		width: '15',
	},
	{
		title: __('Fulfillment', 'easycommerce'),
		name: 'fulfillment',
		width: '15',
	},
	{
		title: __('Products', 'easycommerce'),
		name: 'items',
		width: '8',
	},
	{
		title: __('Amount', 'easycommerce'),
		name: 'total',
		width: '9',
	},
	{
		title: __('Transactions ID', 'easycommerce'),
		name: 'transactions',
		width: '15',
	},
	{
		title: __('Date & Time', 'easycommerce'),
		name: 'created_at',
		width: '14',
	},
];

const statusOptions = [
	{ label: __('Completed', 'easycommerce'), value: 'completed' },
	{ label: __('Cancelled', 'easycommerce'), value: 'cancelled' },
	{ label: __('Partially Refunded', 'easycommerce'), value: 'partially_refunded' },
	{ label: __('Refunded', 'easycommerce'), value: 'refunded' },
	{ label: __('Pending', 'easycommerce'), value: 'pending' },
	{ label: __('On hold', 'easycommerce'), value: 'on_hold' },
	{ label: __('Processing', 'easycommerce'), value: 'processing' },
];

const OrderTable = ({
	tableColumns,
	orders,
	setOrderIdToDelete,
	setShowModal,
	selectedOrders,
	handleSelectOne,
	handleSelectAll,
	setIsBulkDelete,
	setAllShowModal,
	isBulkSelection,
	setOrders,
	setStatusCounts,
	setOrderToDeleteStatus,
}) => {
	const paymentMethods = EASYCOMMERCE.payment_methods;
	const allSelected =
		orders.length > 0 && selectedOrders.length === orders.length;
	const [refundOrderId, setRefundOrderId] = useState(null);

	const getRefundOrder = () => orders.find((o) => o.id === refundOrderId);

	return (
		<>
			<div className="w-full h-full">
				<table className="w-full border-collapse border-spacing-0">
					<thead>
						<tr className="h-auto">
							{columnList.map((item, index) => {
								if (!tableColumns.includes(item.name)) return null;

								if (item.name === 'id') {
									return (
										<th
											key={index}
											className="p-3 pl-5 flex bg-ec-modal items-center justify-start gap-2 rounded-l-md border-r-0"
										>
											<input
												type="checkbox"
												checked={allSelected}
												onChange={(e) => handleSelectAll(e.target.checked)}
												className="min-w-5 h-5 accent-ec-primary cursor-pointer mr-2 easycommerce-input-checkoutbox pl-2"
											/>

											<span className="ml-2 font-inter font-normal text-sm text-ec-title">
												{__('ID', 'easycommerce')}
											</span>
										</th>
									);
								}
								return (
									<th
										key={index}
										className={`font-inter font-normal pl-5 bg-ec-modal text-sm text-ec-title cursor-pointer text-left rtl:text-right
									lg:w-[${item.width}%] ${item.name === 'created_at' ? 'rounded-r-md' : ''}`}
									>
										{item.title}
									</th>
								);
							})}
						</tr>
					</thead>
					<tbody>
						{orders.map((order) => (
							<tr
								key={order.id}
								className="group border-b border-ec-table-stock h-[80px] transition-shadow hover:shadow-[0px_4px_40px_0px_#00000014]"
							>
								{tableColumns.includes('id') && (
									<td className="lg:w-[10%] leading-[26px] pl-5 h-[80px] rtl:text-right rtl:pr-4">
										<div className="flex items-center w-full h-full">
											<input
												type="checkbox"
												checked={selectedOrders.includes(order.id)}
												onChange={() => handleSelectOne(order.id)}
												className="min-w-5 h-5 accent-ec-primary cursor-pointer mr-2 easycommerce-input-checkoutbox pl-2"
											/>
											<div className="relative w-full h-ec-input ml-[10px] rtl:mr-[10px]">
												<div className="flex absolute left-0 rtl:left-auto rtl:right-0 top-1/2 -translate-y-1/2 group-hover:top-4 duration-300">
													<span className="font-inter font-normal text-sm lg:text-[14px] md:text-sm text-ec-body">
														{`#${order.id}`}
													</span>
												</div>
												<div className="absolute bottom-0 flex items-center gap-1.5 font-inter font-normal text-xs text-ec-light-black opacity-0 group-hover:opacity-100 duration-300">
													<button
														className="hover:text-ec-primary duration-300"
														onClick={() =>
															(window.location.hash = `#/orders/${order.id}`)
														}
													>
														{__('View', 'easycommerce')}
													</button>
													<span className="text-[#bdbdbd]">|</span>
													<button
														className="text-ec-red"
														onClick={() => {
															setOrderIdToDelete(order.id);
															setOrderToDeleteStatus(order.status);
															setShowModal(true);
														}}
													>
														{__('Delete', 'easycommerce')}
													</button>
												</div>
											</div>
										</div>
									</td>
								)}
								{tableColumns.includes('customer') && (
									<td className="w-[14%] pl-5 font-inter font-normal lg:text-[14px] md:text-sm">
										<a
											href={`#/customers/${order.customer?.id}`}
											className="text-ec-body hover:text-ec-body focus:shadow-none focus:outline-none"
										>
											<span className="pb-1">{order.customer?.name}</span>
										</a>
									</td>
								)}
								{tableColumns.includes('status') && (
									<td className="w-[15%] pl-5">
										<div className="flex items-center gap-2">
											<StatusDropdown
												options={statusOptions}
												orderId={order.id}
												value={order?.status}
												placeholder={__('Select status', 'easycommerce')}
												width="auto"
												menuWidth="150px"
												prevStatus={order?.status}
												onBeforeStatusChange={(nextStatus) => {
													if (nextStatus === 'partially_refunded' || nextStatus === 'refunded') {
														setRefundOrderId(order.id);
														return true;
													}
													return false;
												}}
												onStatusChange={(prev, next) => {
													setOrders((orders) =>
														orders.map((ord) =>
															ord.id === order.id
																? { ...ord, status: next }
																: ord,
														),
													);
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
									</td>
								)}
								{tableColumns.includes('fulfillment') && (
									<td className="font-inter font-normal pl-5 lg:text-sm text-ec-body md:text-[14px] w-[15%] relative overflow-visible">
										<div className="flex items-center gap-2">
											<FullfillmentDropdown
												orderId={order.id}
												value={order?.fulfill_status}
												placeholder={__('Select status', 'easycommerce')}
												width="116px"
												menuWidth="150px"
												prevStatus={order?.fulfill_status}
												onStatusChange={(prev, next) => {
													setOrders((orders) =>
														orders.map((ord) =>
															ord.id === order.id
																? { ...ord, fulfill_status: next }
																: ord,
														),
													);
												}}
											/>
										</div>
									</td>
								)}
								{tableColumns.includes('items') && (
									<td className="font-inter font-normal text-center pl-5 lg:text-sm text-ec-body md:text-[14px] lg:w-[8%]">
										{order?.items}
									</td>
								)}
								{tableColumns.includes('total') && (
									<td className="font-inter font-normal pl-5 lg:text-sm text-ec-body md:text-[14px] lg:w-[9%]">
										{EASYCOMMERCE.currency_symbol}
										{order?.total}
									</td>
								)}
								{tableColumns.includes('transactions') &&
									(order.transactions ? (
										<td
											key={order.transactions.id}
											className="flex items-center gap-2 mr-2 pl-5 py-[20px] text-sm w-[15%]"
										>
											{order.transactions.payment_gateway ? (
												<img
													src={
														EASYCOMMERCE.payment_methods[
															order.transactions.payment_gateway
														]?.icon
													}
													className="pointer-events-none object-contain rounded h-[30px] min-w-[54px] p-[3px]"
													alt={__('payment-icon', 'easycommerce')}
													style={{ border: '1px solid #f0edfb' }}
												/>
											) : (
												<span className="text-ec-body font-inter text-sm font-normal text-center border rounded border-ec-table-stock h-[30px] min-w-[54px] p-[3px]">
													{order.transactions.payment_gateway}
												</span>
											)}

											<p className="text-ec-body font-inter text-sm font-normal leading-[26px]">
												{order.transactions.transaction_id &&
												order.transactions.transaction_id !== '-'
													? order.transactions.transaction_id.length > 7 ? 
														'...' +
														order.transactions.transaction_id.slice(
															order.transactions.transaction_id.length - 7,
															order.transactions.transaction_id.length,
														)
														: order.transactions.transaction_id
													: ''}
											</p>
										</td>
									) : (
										<td
											className="text-left py-4 font-inter pl-5 font-normal lg:w-[13%] lg:text-sm 
										text-ec-body md:text-[14px]"
										>
											{__('N/A', 'easycommerce')}
										</td>
									))}

								{tableColumns.includes('created_at') && (
									<td className="w-[14%] pl-5">
										<div className="flex flex-col">
											<p className="font-inter font-normal mb-1 lg:text-sm text-ec-body md:text-[14px]">
												{order.created_at_formatted ? order.created_at_formatted : __('N/A', 'easycommerce')}
											</p>
											<span className="text-ec-placeholder text-sm font-inter leading-4">
												{order.created_time ? order.created_time : __('N/A', 'easycommerce')}
											</span>
										</div>
									</td>
								)}
							</tr>
						))}
					</tbody>
				</table>
			</div>
			{refundOrderId && getRefundOrder() && (
				<RefundModal
					hideModal={() => setRefundOrderId(null)}
					order={getRefundOrder()}
					updateOrder={(updatedOrder) => {
						setOrders((orders) =>
							orders.map((ord) =>
								ord.id === updatedOrder.id ? updatedOrder : ord,
							),
						);
						setStatusCounts((counts) => {
							const updated = { ...counts };
							const currentOrder = getRefundOrder();
							if (currentOrder) {
								if (updated[currentOrder.status] > 0) {
									updated[currentOrder.status] -= 1;
								}
								updated[updatedOrder.status] =
									(updated[updatedOrder.status] || 0) + 1;
							}
							return updated;
						});
						setRefundOrderId(null);
					}}
				/>
			)}
		</>
	);
};

export default OrderTable;
