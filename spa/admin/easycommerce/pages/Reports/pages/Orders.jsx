import React, { useState } from "react";
import { __ } from '@wordpress/i18n';

// components
import Container from '../components/common/Container';
import Header from '../components/common/Header';
import ReportStats from '../components/common/ReportStats';
import OrderPieChart from '../components/orders/OrderPieChart';
import LineChart from '../components/common/LineChart';
import Heatmap from "../components/common/Heatmap";
import GeoMap from "../components/common/GeoMap";
import Customers from '../components/orders/Customers';

const Orders = () => {
	const [range, setRange] = useState({
		label: __( 'Last 30 days', 'easycommerce' ),
		value: 'last-30',
	});

	const [comparison, setComparison] = useState({
		label: __( 'Prev. 30 days', 'easycommerce' ),
		value: 'prev-30'
	});

	return (
		<>
			<Header
				title={__( 'Orders', 'easycommerce' )}
				range={range}
				setRange={setRange}
				comparison={comparison}
				setComparison={setComparison}
			/>

			<ReportStats 
				endpoint="/easycommerce/v1/reports/orders/stats"
				params={{ range: range.value, comparison: comparison.value }}
				skeletonCount={5}
			/>

			<div className="my-6">
				<Container title={__( 'Orders Over Time', 'easycommerce' )} >
					<LineChart
						endpoint="/easycommerce/v1/reports/orders/over-time"
						params={{ range: range.value, comparison: comparison.value }}
					/>
				</Container>
			</div>

			<div className="my-6">
				<div className="grid grid-cols-3 gap-6">
					<Container title={__( 'Order vs. Customers', 'easycommerce' )} >
						<Customers range={range.value} />
					</Container>
					<Container title={__( 'Order Status Breakdown', 'easycommerce' )} >
						<OrderPieChart 
							endpoint="/easycommerce/v1/reports/orders/statuses"
							params={{ range: range.value }}
						/>
					</Container>
					<Container title={__( 'Fulfillment Status Breakdown', 'easycommerce' )} >
						<OrderPieChart 
							endpoint="/easycommerce/v1/reports/orders/fulfillment"
							params={{ range: range.value }}
						/>
					</Container>
			    </div>
			</div>

			<div className="my-6">
				<Container title={__( 'Orders by Location', 'easycommerce' )} >
					<GeoMap
						endpoint="/easycommerce/v1/reports/orders/locations"
						params={{ range: range.value }}
					/>
				</Container>
			</div>

			<div className="my-6">
				<Container title={__( 'Daily Orders Heatmap', 'easycommerce' )} >
					<Heatmap
						endpoint="/easycommerce/v1/reports/orders/heatmap"
						params={{ range: range.value }}
						showValues={true}
					/>
				</Container>
			</div>
		</>
	);
};

export default Orders;