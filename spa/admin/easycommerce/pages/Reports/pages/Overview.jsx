import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';

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
		label: __( 'Last 30 days', 'easycommerce' ),
		value: 'last-30',
	});

	const [comparison, setComparison] = useState({
		label: __( 'Prev. 30 days', 'easycommerce' ),
		value: 'prev-30',
	});

	return (
		<>
			<Header
				title={__( 'Overview', 'easycommerce' )}
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
					title={__( 'Sales vs. Refunds vs. Net Revenue', 'easycommerce' )}
					tooltip={__( 'Comparison of sales, refunds, and net revenue over time', 'easycommerce' )}
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
						title={__( 'Order vs. Refund Count', 'easycommerce' )}
						link="#/reports/orders"
					>
						<LineChart
							endpoint="/easycommerce/v1/reports/overview/order-vs-refund"
							params={{ range: range.value }}
							showShadow={false}
						/>
					</Container>

					<Container title={__( 'Order Statuses', 'easycommerce' )}>
						<OrderChart range={range.value} />
					</Container>

					<Container title={__( 'Subscriptions', 'easycommerce' )}>
						<Subscriptions />
					</Container>

					<Container title={__( 'Order Type', 'easycommerce' )}>
						<LineChart
							endpoint="/easycommerce/v1/reports/overview/order-type"
							params={{ range: range.value, comparison: comparison.value }}
							showShadow={false}
						/>
					</Container>

					<Container title={__( 'Top Selling Items', 'easycommerce' )} link="#/reports/products">
						<TopSellingProducts
							range={range.value}
							comparison={comparison.value}
						/>
					</Container>

					<Container title={__( 'Product Stats', 'easycommerce' )}>
						<ProductStats
							endpoint="/easycommerce/v1/reports/overview/catalog-stats"
							params={{ range: range.value }}
						/>
					</Container>

					<Container title={__( 'New vs. Returning Customers', 'easycommerce' )} link="#/reports/customers">
						<LineChart
							endpoint="/easycommerce/v1/reports/overview/customer-type"
							params={{ range: range.value }}
							showShadow={false}
						/>
					</Container>

					<Container title={__( 'Customer Overview', 'easycommerce' )}>
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
