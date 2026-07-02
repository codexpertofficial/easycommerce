import React, { useState } from 'react';

// components
import Container from '../components/common/Container';
import ReportStats from '../components/common/ReportStats';
import LineChart from '../components/common/LineChart';
import Header from '../components/common/Header';
import GeoMap from '../components/common/GeoMap';

import TopCustomers from '../components/revenue/TopCustomers';

const Revenues = () => {
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
				title="Revenues"
				range={range}
				setRange={setRange}
				comparison={comparison}
				setComparison={setComparison}
			/>

			<ReportStats
				endpoint="/easycommerce/v1/reports/revenue/stats"
				params={{ range: range.value, comparison: comparison.value }}
				skeletonCount={5}
			/>

			<div className="my-6">
				<Container title="Revenue Over Time" >
					<LineChart
						endpoint="/easycommerce/v1/reports/revenue/over-time"
						params={{ range: range.value, comparison: comparison.value }}
					/>
				</Container>
			</div>
			
			<div className="my-6">
				<Container title="Orders by Location" >
					<GeoMap
						endpoint="/easycommerce/v1/reports/revenue/locations"
						params={{ range: range.value }}
					/>
				</Container>
			</div>

			<div className="my-6">
				<Container title="Top Customers by Revenue" >
					<TopCustomers range={range.value} />
				</Container>
			</div>
		</>
	);
};

export default Revenues;
