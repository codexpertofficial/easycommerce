import React, { useEffect, useState } from "react";
import { applyFilters } from "@wordpress/hooks";

// Components
import ItemsTable from "./elements/ItemsTable";
import OrderInfo from "./elements/OrderInfo";
import AddressTabs from "./elements/AddressTabs";
import SingleOrderSkeleton from "./elements/SingleOrderSkeleton";
import OrderNotes from "./elements/OrderNotes";

const SingleOrder = ({ orderId }) => {
	const [order, setOrder] = useState(null);
	const [isLoading, setIsLoading] = useState(true);

	useEffect(() => {
		if (!orderId) return;

		fetch(`${EASYCOMMERCE.rest_base}/orders/${orderId}`,{
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			}
		})
			.then((res) => res.json())
			.then((data) => {
				setIsLoading(false);

				if (data.success && data.data?.id) {
					setOrder(data.data);
				}
			});
	}, [orderId]);

	applyFilters("easycommerce.dashboard.order_data", order);

	return (
		<>
			{!isLoading ? (
				<>
					{order ? (
						<div className="easycommerce-dashboard-section pb-[55px] flex flex-col gap-5">
							<div className="flex flex-col gap-3">
								<a
									href="#orders"
									className="inline-flex items-center gap-1.5 w-fit font-inter text-sm font-medium text-ec-placeholder no-underline hover:text-ec-primary transition-colors"
								>
									<svg className="w-4 h-4" data-slot="icon" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
										<path strokeLinecap="round" strokeLinejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
									</svg>
									Back to orders
								</a>
								<h3 className="easycommerce-dashboard-section-title !text-lg sm:!text-2xl">
									Order #{order.id}
								</h3>
							</div>
							<div className="easycommerce-dashboard-section grid grid-cols-1 gap-6">
								<div className="easycommerce-dashboard-order-info-iteam col-span-1 grid grid-cols-1 lg:grid-cols-5 gap-4 items-stretch">
									<div className="lg:col-span-2 h-full">
										<OrderInfo order={order} />
									</div>
									<div className="lg:col-span-3 h-full">
										<AddressTabs
											billing={order.meta.billing}
											shipping={order.meta.shipping}
										/>
									</div>
								</div>

								<div className="easycommerce-dashboard-order-iteam p-4 md:p-7 col-span-1 bg-white border border-ec-border rounded-2xl shadow-[0_2px_16px_-8px_rgba(18,3,80,0.10)]">
									<ItemsTable order={order} />
								</div>

								<div className="bg-white border border-ec-border rounded-2xl shadow-[0_2px_16px_-8px_rgba(18,3,80,0.10)] overflow-hidden">
									<OrderNotes orderId={order.id} />
								</div>
							</div>
						</div>
					) : (
						<div>No order data found</div>
					)}
				</>
			) : (
				<SingleOrderSkeleton />
			)}
		</>
	);
};

export default SingleOrder;
