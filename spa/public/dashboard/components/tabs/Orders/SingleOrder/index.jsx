import React, { useEffect, useState } from "react";
import { applyFilters } from "@wordpress/hooks";

// Components
import ItemsTable from "./elements/ItemsTable";
import OrderInfo from "./elements/OrderInfo";
import AddressSection from "./elements/AddressSection";
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
						<div className="easycommerce-dashboard-section pb-[55px] flex flex-col gap-4">
							<h3 className="easycommerce-dashboard-section-title !text-lg sm:!text-2xl">
								Order #{order.id}
							</h3>
							<div className="easycommerce-dashboard-section grid grid-cols-1 gap-6">
								<div className="easycommerce-dashboard-order-info-iteam col-span-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
									<OrderInfo order={order} />
									<AddressSection
										address={order.meta.billing}
										title="Billing"
									/>
									<AddressSection
										address={order.meta.shipping}
										title="Shipping"
									/>
								</div>

								<div className="easycommerce-dashboard-order-iteam p-1 md:p-7 col-span-1 border border-ec-border rounded-lg">
									<ItemsTable order={order} />
								</div>
								
								<OrderNotes orderId={order.id} />
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
