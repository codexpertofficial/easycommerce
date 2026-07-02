import React, { useState } from "react";

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
		label: 'Last 30 days',
		value: 'last-30',
	});

	const [comparison, setComparison] = useState({
		label: 'Prev. 30 days',
		value: 'prev-30'
	});

	return (
		<>
			<Header
				title="Orders"
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
				<Container title="Orders Over Time" >
					<LineChart
						endpoint="/easycommerce/v1/reports/orders/over-time"
						params={{ range: range.value, comparison: comparison.value }}
					/>
				</Container>
			</div>

			<div className="my-6">
				<div className="grid grid-cols-3 gap-6">
					<Container title="Order vs. Customers" >
						<Customers range={range.value} />
					</Container>
					<Container title="Order Status Breakdown" >
						<OrderPieChart 
							endpoint="/easycommerce/v1/reports/orders/statuses"
							params={{ range: range.value }}
						/>
					</Container>
					<Container title="Fulfillment Status Breakdown" >
						<OrderPieChart 
							endpoint="/easycommerce/v1/reports/orders/fulfillment"
							params={{ range: range.value }}
						/>
					</Container>
			    </div>
			</div>

			<div className="my-6">
				<Container title="Orders by Location" >
					<GeoMap
						endpoint="/easycommerce/v1/reports/orders/locations"
						params={{ range: range.value }}
					/>
				</Container>
			</div>

			<div className="my-6">
				<Container title="Daily Orders Heatmap" >
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