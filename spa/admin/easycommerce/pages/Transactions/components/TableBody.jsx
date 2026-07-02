import React from "react";
import CopyButton from "../../../../common/CopyButton";
const paymentMethods = EASYCOMMERCE.payment_methods;
const columnList = [
	{
		title: "Order ID",
		name: "order_id",
		width:"10",
	},
	{
		title: "Name",
		name: "name",
		width:"12",
	},
	{
		title: "Type",
		name: "type",
		width:"14",
	},
	{
		title: "Amount",
		name: "amount",
		width:"13",
	},
	{
		title: "Transaction ID",
		name: "transaction_id",
		width:"20",
	},
	
	{
		title: "Date",
		name: "date",
		width:"10",
	},
];

const typeColors = {
	payment: {
		color: "var(--color-ec-paymentText)",
		background: "var(--color-ec-paymentBg)",
		border: "1px solid var(--color-ec-paymentBorder)",
	},
	refund: {
		color: "var(--color-ec-refundText)",
		background: "var(--color-ec-refundBg)",
		border: "1px solid var(--color-ec-refundBorder)",
	},
	adjustment: {
		color: "var(--color-ec-adjustmentText)",
		background: "var(--color-ec-adjustmentBg)",
		border: "1px solid var(--color-ec-adjustmentBorder)",
	},
};

const TableBody = ({
	transactions,
	tableColumns,
}) => {
	return (
		<>
			<thead>
				<tr className="h-[44px]">
					{columnList.map(
						(column , index) =>
							tableColumns.includes(column.name) && (
								<th
									key={column.key}
									className="bg-ec-modal items-center justify-start gap-2 first:rounded-l-lg last:rounded-r-lg border-r-0 font-inter font-normal text-sm pl-5 text-ec-title text-left rtl:text-right rtl:pr-4">
									<span>
										{column.name === "type"
										? "Type"
										: column.title}
									</span>
									
								</th>
							)
					)}
				</tr>
			</thead>
			<tbody>
				{transactions.map((transaction, index) => {
					const orderId = transaction.order_id || "N/A";
					const type = transaction.type
						? transaction.type.toLowerCase()
						: "";
					const amount = transaction.amount || "$0.00";
					const transactionId =
						transaction.transaction_id || "**** ****";
					const createdAt = transaction.created_at
						? transaction.created_at
						: "N/A";
					const createdAtTime = transaction.created_time
						? transaction.created_time
						: "N/A";
					const { color, background, border } =
						typeColors[type] || typeColors["payment"];
					const paymentGateway = transaction.payment_gateway
						? transaction.payment_gateway
						: "unknown";
					const paymentMethod = paymentMethods[paymentGateway];

					const paymentIcon = paymentMethod?.icon;

					return (
						<>
							<tr
								key={index}
								className="border-b border-ec-table-stock h-[80px] transition-shadow hover:shadow-[0px_4px_40px_0px_#00000014] group/transaction">
								{tableColumns.includes("order_id") && (
									<td className="relative text-sm text-ec-body font-inter font-normal leading-[26px] pl-5 lg:w-[10%]">
										<div className="flex items-center w-full h-[80px] rtl:pr-4">
											<div className="relative w-full h-ec-input">
												<span
													className="text-ec-body hover:text-ec-body focus:shadow-none focus:outline-none 
													focus:border-0 focus:text-ec-body absolute left-0 rtl:left-auto rtl:right-0 top-1/2 -translate-y-1/2 group-hover/transaction:top-4 duration-300">
													{transaction.order_id}
												</span>

												<div className="absolute bottom-0 gap-1.5 font-inter font-normal text-xs text-ec-light-black opacity-0 group-hover/transaction:opacity-100 duration-300">
													<a
														href={`#/orders/${transaction.order_id}`}
														className="duration-300 hover:text-ec-primary"
													>
														View
													</a>
												</div>
											</div>
										</div>
									</td>
								)}
								{tableColumns.includes("name") && (
									<td className="text-sm text-ec-body hover:text-ec-body font-inter leading-[26px] 
                                        font-normal pl-5 lg:w-[12%] rtl:pl-0 rtl:pr-4">
										<div className="flex items-center w-full h-[80px]">
											<div className="flex items-start justify-center">
												<span
													className="text-ec-body hover:text-ec-body">
													{transaction.customer.id ? (
														transaction.customer.name
													) : (
														"Unknown"
													)}
												</span>

												{/* <div className="absolute bottom-0 gap-1.5 font-inter font-normal text-xs text-ec-light-black opacity-0 group-hover/transaction:opacity-100 duration-300">
													<a
														href={`#/customers/${transaction.customer.id}`}
														className="duration-300 hover:text-ec-primary"
													>
														View
													</a>
												</div> */}
											</div>
										</div>
									</td>
								)}
								{tableColumns.includes("type") && (
									<td className="lg:w-[14%] pl-5 rtl:pl-0 rtl:pr-4">
										<span
											className="inline-block w-[116px] text-center rounded-lg py-[6px] font-inter text-sm font-normal capitalize"
											style={{
												color: color,
												backgroundColor: background,
												border: border,
											}}>
											{transaction.type
												? transaction.type
														.charAt(0)
														.toUpperCase() +
												  transaction.type.slice(1)
												: "N/A"}
										</span>
									</td>

								)}
								{tableColumns.includes("amount") && (
									<td className="lg:w-[13%] pl-5 rtl:pl-0 rtl:pr-4">
										<span className="font-inter font-normal lg:text-sm text-ec-body md:text-[14px] ">
											{amount}
										</span>
									</td>
								)}
								{tableColumns.includes("transaction_id") && (
									<td className="pl-5 lg:w-[20%] rtl:pl-0 rtl:pr-4">
										<div className="flex h-10 w-10 items-center gap-2">
											{paymentIcon ? (
												<img
													src={paymentIcon}
													className="pointer-events-none object-contain rounded h-[30px] min-w-[54px] p-[3px]"
													alt="payment-icon"
													style={{ border: "1px solid #f0edfb" }}
												/>
											) : (
												<span className="text-ec-body font-inter text-sm font-normal border rounded border-ec-table-stock w-[70px] p-[7px]">
													{
														transaction.payment_gateway
													}
												</span>
											)}
											{transactionId && transactionId !== "-" && (
												<div className="flex items-center gap-2">
													<p className="text-ec-body font-inter text-sm font-normal leading-[26px]">
														{transactionId.length > 15
															? `${transactionId.substring(0, 15)}...`
															: transactionId}
													</p>
													<CopyButton copy={transactionId} />
													
												</div>
											)}
										</div>
									</td>
								)}
								
								{tableColumns.includes("date") && (
									<td className="pl-5 lg:w-[10%] rtl:pl-0 rtl:pr-4">
										<p className="font-inter font-normal mb-1 lg:text-sm text-ec-body md:text-[14px]">
											{createdAt}
										</p>
										<span className="text-ec-placeholder text-sm font-inter leading-4">
											{createdAtTime}
										</span>
									</td>
								)}
							</tr>
						</>
					);
				})}
			</tbody>
		</>
	);
};

export default TableBody;
