import React from "react";
import { __ } from "@wordpress/i18n";

const statusStyles = {
	pending: "bg-ec-pendingBg text-ec-pendingText",
	notified: "bg-ec-completedBg text-ec-completedText",
	cancelled: "bg-ec-cancelledBg text-ec-cancelledText",
};

const statusLabels = {
	pending: __("Waiting", "easycommerce"),
	notified: __("Notified", "easycommerce"),
	cancelled: __("Cancelled", "easycommerce"),
};

const formatDate = (value) => {
	if (!value) return "-";
	const date = new Date(value.replace(" ", "T"));
	return isNaN(date)
		? value
		: date.toLocaleDateString(undefined, {
				day: "2-digit",
				month: "short",
				year: "numeric",
		  });
};

const TBody = ({ requests, columnList, selectedRequests, toggleRequest, removeRequest }) => {
	return (
		<tbody>
			{requests.map((request) => (
				<tr
					key={request.id}
					className="border-b ec-db-lg:text-sm text-xs border-ec-table-stock h-[70px] transition-shadow hover:shadow-[0px_4px_40px_0px_#00000014] group/request"
				>
					{columnList.map((column) => {
						if (column.key === "email") {
							return (
								<td key="email" className="p-5 w-[28%]">
									<div className="flex items-center">
										<input
											type="checkbox"
											checked={selectedRequests.includes(request.id)}
											onChange={() => toggleRequest(request.id)}
											className="min-w-5 h-5 accent-ec-primary cursor-pointer mr-2 easycommerce-input-checkoutbox"
										/>
										<div className="relative w-full min-w-0 h-10 ml-4 rtl:mr-4">
											<span className="text-ec-body font-inter font-normal absolute top-1/2 -translate-y-1/2 group-hover/request:top-0 group-hover/request:translate-y-0 duration-300 w-full truncate block">
												{request.email}
											</span>
											<div className="invisible group-hover/request:visible opacity-0 group-hover/request:opacity-100 duration-300 absolute bottom-0">
												<button
													onClick={() => removeRequest(request.id, request.email)}
													className="font-inter font-normal text-xs text-ec-red"
												>
													{__("Delete", "easycommerce")}
												</button>
											</div>
										</div>
									</div>
								</td>
							);
						}

						if (column.key === "product") {
							return (
								<td key="product" className="p-5 w-[28%]">
									<a
										href={`admin.php?page=easycommerce#/products/edit/${request.product_id}`}
										className="font-inter text-ec-title hover:text-ec-primary truncate block"
									>
										{request.product_name || __("(Deleted product)", "easycommerce")}
									</a>
								</td>
							);
						}

						if (column.key === "status") {
							return (
								<td key="status" className="p-5 w-[14%]">
									<span
										className={`inline-block font-inter text-xs font-medium px-3 py-1 rounded-full ${
											statusStyles[request.status] || ""
										}`}
									>
										{statusLabels[request.status] || request.status}
									</span>
								</td>
							);
						}

						if (column.key === "stock") {
							return (
								<td key="stock" className="p-5 w-[14%] font-inter text-ec-body">
									{null === request.stock || undefined === request.stock ? "-" : request.stock}
								</td>
							);
						}

						return (
							<td key="created_at" className="p-5 w-[14%] font-inter text-ec-body">
								{formatDate(request.created_at)}
							</td>
						);
					})}
				</tr>
			))}
		</tbody>
	);
};

export default TBody;
