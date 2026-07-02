import React from 'react';

const RefundsTable = ({ refunds }) => {
	const tableColumns = [
		{
			name: 'ID',
			key: 'id',
			widthClass: 'w-[8%]',
		},
		{
			name: 'Order ID',
			key: 'order_id',
			widthClass: 'w-[8%]',
		},
		{
			name: 'Amount',
			key: 'amount',
			widthClass: 'w-[8%]',
		},
		{
			name: 'Customer',
			key: 'customer',
			widthClass: 'w-[15%]',
		},
		{
			name: 'Reason',
			key: 'reason',
			widthClass: 'w-[15%]',
		},
		{
			name: 'Status',
			key: 'status',
			widthClass: 'w-[8%]',
		},
		{
			name: 'Transaction ID',
			key: 'transaction_id',
			widthClass: 'w-[15%]',
		},
		{
			name: 'Date',
			key: 'date',
			widthClass: 'w-[10%]',
		},
	];

	const statusStyles = {
		approved: 'bg-green-100 text-green-800',
		pending: 'bg-yellow-100 text-yellow-800',
		failed: 'bg-red-100 text-red-800',
	};

	const formattedTxnId = (txnId) => {
		return txnId
			? txnId.length > 10
				? `...${txnId.slice(txnId.length - 10)}`
				: txnId
			: 'N/A';
	};

	return (
		<div className="w-full overflow-x-auto">
			<table className="min-w-full xl:min-w-[1300px] w-full border-collapse border-spacing-0">
				<thead>
					<tr className="h-auto">
						{tableColumns.map((col, index) => (
							<th
								key={col.key}
								className={`font-inter font-normal bg-ec-modal text-sm text-ec-title text-left rtl:text-right ${col.widthClass} ${
									index === 0
										? 'p-3 pl-5 rounded-l-md border-r-0'
										: index === tableColumns.length - 1
											? 'rounded-r-md'
											: ''
								}`}
							>
								{col.name}
							</th>
						))}
					</tr>
				</thead>

				<tbody>
					{refunds.map((refund) => (
						<tr
							key={refund.id}
							className="border-b border-ec-table-stock h-[80px] transition-shadow hover:shadow-[0px_4px_40px_0px_#00000014] group"
						>
							{tableColumns.some((col) => col.key === 'id') && (
								<td
									className={`leading-[26px] pl-5 py-4 pr-3 lg:pr-0 ${tableColumns.find((col) => col.key === 'id')?.widthClass}`}
								>
									<div className="flex items-center gap-4 lg:gap-3 justify-start mt-1 w-full">
										<div className="flex items-center gap-4 lg:gap-3 focus:shadow-none grow">
											<span className="text-sm text-ec-body font-inter font-normal">
												#{refund.id}
											</span>
										</div>
									</div>
								</td>
							)}
							{tableColumns.some((col) => col.key === 'order_id') && (
								<td
									className={`py-4 pr-3 lg:pr-0 ${tableColumns.find((col) => col.key === 'order_id')?.widthClass}`}
								>
									<a href={`#/orders/${refund.order_id}`} className="font-inter text-sm text-ec-body hover:text-ec-primary duration-300">
										Order #{refund.order_id}
									</a>
								</td>
							)}
							{tableColumns.some((col) => col.key === 'amount') && (
								<td
									className={`font-inter font-normal text-sm text-ec-body pr-3 lg:pr-0 py-4 ${tableColumns.find((col) => col.key === 'amount')?.widthClass}`}
								>
									${refund.amount}
								</td>
							)}
							{tableColumns.some((col) => col.key === 'customer') && (
								<td
									className={`py-4 pr-3 lg:pr-0 ${tableColumns.find((col) => col.key === 'customer')?.widthClass}`}
								>
									<span className="font-inter text-[14px] text-ec-body font-normal">
										{refund.customer}
									</span>
								</td>
							)}
							{tableColumns.some((col) => col.key === 'reason') && (
								<td
									className={`py-4 pr-3 lg:pr-0 ${tableColumns.find((col) => col.key === 'reason')?.widthClass}`}
								>
									<span className="font-inter text-[14px] text-ec-body font-normal">
										{EASYCOMMERCE.refund_reasons[refund.reason] || 'N/A'}
									</span>
								</td>
							)}
							{tableColumns.some((col) => col.key === 'status') && (
								<td
									className={`font-inter font-normal text-sm text-ec-body pr-3 lg:pr-0 py-4 ${tableColumns.find((col) => col.key === 'status')?.widthClass}`}
								>
									<span
										className={`px-2 py-1 rounded-full text-xs ${
											statusStyles[refund.status] || 'bg-gray-100 text-gray-800'
										}`}
									>
										{refund.status}
									</span>
								</td>
							)}
							
							{tableColumns.some((col) => col.key === 'transaction_id') && (
								(() => {
									const paymentIcon = EASYCOMMERCE.payment_methods[refund.payment_gateway]?.icon;
									const supportsRefund =
												EASYCOMMERCE.payment_methods[refund.payment_gateway]
													?.support_refund;

									return (
										<td
											className={`py-4 pr-3 lg:pr-0 ${tableColumns.find((col) => col.key === 'transaction_id')?.widthClass}`}
										>
											<div className='flex items-center gap-1'>
												{supportsRefund && paymentIcon && (
													<img
														src={paymentIcon}
														className="pointer-events-none object-contain rounded h-[30px] w-[54px] p-[3px]"
														alt="payment-icon"
														style={{ border: "1px solid #f0edfb" }}
													/>
												)}

												<span className="font-inter text-[14px] text-ec-body font-normal">
													{formattedTxnId(refund.transaction_id)}
												</span>
											</div>
										</td>
									)
								})()
							)}
							{tableColumns.some((col) => col.key === 'date') && (
								<td
									className={`font-inter font-normal text-sm text-ec-body pr-3 lg:pr-0 py-4 ${tableColumns.find((col) => col.key === 'date')?.widthClass}`}
								>
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
							)}
						</tr>
					))}
				</tbody>
			</table>
		</div>
	);
};

export default RefundsTable;
