import React, { useState } from 'react';

// components
import Header from '../components/common/Header';
import Container from '../components/common/Container';
import ReportStats from '../components/common/ReportStats';
import LineChart from '../components/common/LineChart';

import Subscriptions from '../components/overview/Subscriptions';
import TopSellingProducts from '../components/overview/TopSellingProducts';
import CustomerOverview from '../components/overview/CustomerOverview';
import OrderChart from '../components/overview/OrderChart';
import ProductStats from '../components/overview/ProductStats';

const Overview = () => {
	const [range, setRange] = useState({
		label: 'Last 30 days',
		value: 'last-30',
	});

	const [comparison, setComparison] = useState({
		label: 'Prev. 30 days',
		value: 'prev-30',
	});

	return (
		<>
			<Header
				title="Overview"
				range={range}
				setRange={setRange}
				comparison={comparison}
				setComparison={setComparison}
			/>

			<ReportStats
				endpoint="/easycommerce/v1/reports/overview/stats"
				params={{ range: range.value, comparison: comparison.value }}
				skeletonCount={6}
			/>

			<div className="my-6">
				<Container 
					title="Sales vs. Refunds vs. Net Revenue" 
					tooltip="Comparison of sales, refunds, and net revenue over time" 
					link="#/reports/revenues"
				>
					<LineChart
						endpoint="/easycommerce/v1/reports/overview/sales-refund-revenue"
						params={{ range: range.value }}
					/>
				</Container>
			</div>

			<div className="my-6">
				<div className="grid grid-cols-2 gap-6">
					<Container 
						title="Order vs. Refund Count" 
						link="#/reports/orders"
					>
						<LineChart
							endpoint="/easycommerce/v1/reports/overview/order-vs-refund"
							params={{ range: range.value }}
							showShadow={false}
						/>
					</Container>

					<Container title="Order Statuses">
						<OrderChart range={range.value} />
					</Container>

					<Container title="Subscriptions">
						<Subscriptions />
					</Container>

					<Container title="Order Type">
						<LineChart
							endpoint="/easycommerce/v1/reports/overview/order-type"
							params={{ range: range.value, comparison: comparison.value }}
							showShadow={false}
						/>
					</Container>

					<Container title="Top Selling Items" link="#/reports/products">
						<TopSellingProducts
							range={range.value}
							comparison={comparison.value}
						/>
					</Container>

					<Container title="Product Stats">
						<ProductStats
							endpoint="/easycommerce/v1/reports/overview/catalog-stats"
							params={{ range: range.value }}
						/>
					</Container>

					<Container title="New vs. Returning Customers" link="#/reports/customers">
						<LineChart
							endpoint="/easycommerce/v1/reports/overview/customer-type"
							params={{ range: range.value }}
							showShadow={false}
						/>
					</Container>

					<Container title="Customer Overview">
						<CustomerOverview
							endpoint="/easycommerce/v1/reports/overview/customer-overview"
							params={{ range: range.value }}
						/>
					</Container>
				</div>
			</div>
		</>
	);
};

export default Overview;
