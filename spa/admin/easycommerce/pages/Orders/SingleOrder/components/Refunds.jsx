import React, { useState, useEffect } from 'react';

const Refunds = ({ orderId }) => {
	const [refunds, setRefunds] = useState([]);

	useEffect(() => {
		try {
			fetch(`${EASYCOMMERCE.rest_base}/refunds?order_id=${orderId}`, {
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': EASYCOMMERCE.nonce,
				},
				method: 'GET',
			})
				.then((res) => res.json())
				.then((data) => {
					setRefunds(data.data.refunds || []);
				});
		} catch (error) {
			console.error('Error fetching refunds:', error);
		}
	}, [orderId]);

	const tableColumns = [
		{
			name: 'Reason',
			key: 'reason',
			widthClass: 'w-[27%]',
		},
		{
			name: 'Amount',
			key: 'amount',
			widthClass: 'w-[14%]',
		},
		{
			name: 'Transaction ID',
			key: 'transaction_id',
			widthClass: 'w-[22%]',
		},
		{
			name: 'Refunded By',
			key: 'refunded_by',
			widthClass: 'w-[18%]',
		},
		{
			name: 'Date',
			key: 'date',
			widthClass: 'w-[19%]',
		},
	];

	const formattedTxnId = (txnId) => {
		return txnId
			? txnId.length > 10
				? `...${txnId.slice(txnId.length - 10)}`
				: txnId
			: 'N/A';
	};

	return (
		<div className="mt-6 bg-white rounded-2xl">
			<div className="flex flex-col border-b border--ec-table-stock pt-4 px-6">
				<h3 className="text-ec-title text-xl font-medium font-inter leading-8 pb-4">
					Refunds
				</h3>
			</div>
			<div className="p-5">
				<table className="overflow-scroll border-collapse border-spacing-0 w-full m-0">
					<thead className="bg-[#00000008]">
						<tr>
							{tableColumns.map((column) => (
								<th
									key={column.key}
									className={`${column.widthClass} text-left font-inter font-normal text-ec-title text-base leading-6 py-3 px-4`}
								>
									{column.name}
								</th>
							))}
						</tr>
					</thead>
					{refunds.length > 0 && (
						<tbody>
							{refunds.map((refund) => (
								<tr
									key={refund.transaction_id}
									className="border-b border-ec-table-stock"
								>
									<td className="w-[27%] px-4 font-inter text-sm text-ec-body">
										{EASYCOMMERCE.refund_reasons[refund.reason] || 'N/A'}
									</td>
									<td className="w-[14%] px-4 font-inter text-sm text-ec-body">
										${refund.amount}
									</td>
									<td className="w-[22%] px-4 font-inter text-sm text-ec-body">
										{(() => {
											const paymentIcon =
												EASYCOMMERCE.payment_methods[refund.payment_gateway]
													?.icon;

											const supportsRefund =
												EASYCOMMERCE.payment_methods[refund.payment_gateway]
													?.support_refund;

											return (
												<td
													className={`py-4 pr-3 lg:pr-0 ${tableColumns.find((col) => col.key === 'transaction_id')?.widthClass}`}
												>
													<div className="flex items-center gap-1">
														{supportsRefund && paymentIcon && (
															<img
																src={paymentIcon}
																className="pointer-events-none object-contain rounded h-[30px] w-[54px] p-[3px]"
																alt="payment-icon"
																style={{ border: '1px solid #f0edfb' }}
															/>
														)}

														<span className="font-inter text-[14px] text-ec-body font-normal">
															{formattedTxnId(refund.transaction_id)}
														</span>
													</div>
												</td>
											);
										})()}
									</td>
									<td className="w-[18%] px-4 font-inter text-sm text-ec-body">
										{refund.refunded_by}
									</td>
									<td className="w-[19%] px-4 font-inter text-sm text-ec-body">
										{(() => {
											const [date, time] = refund.created_at.split(' ');

											return (
												<div className="flex flex-col">
													<p className="font-inter font-normal mb-1 lg:text-sm text-ec-body md:text-[14px]">
														{date ? date : 'N/A'}
													</p>
													<span className="text-ec-placeholder text-sm font-inter leading-4">
														{time ? time : 'N/A'}
													</span>
												</div>
											);
										})()}
									</td>
								</tr>
							))}
						</tbody>
					)}
				</table>
			</div>
		</div>
	);
};

export default Refunds;
