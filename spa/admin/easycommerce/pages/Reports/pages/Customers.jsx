import React, { useState } from "react";

// components
import Container from '../components/common/Container';
import ReportStats from '../components/common/ReportStats';
import LineChart from '../components/common/LineChart';
import Header from '../components/common/Header';
import Heatmap from '../components/common/Heatmap';
import GeoMap from '../components/common/GeoMap';
import TopCustomers from '../components/customers/TopCustomers';

const Customers = () => {
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
				title="Customers"
				range={range}
				setRange={setRange}
				comparison={comparison}
				setComparison={setComparison}
			/>

			<ReportStats 
				endpoint="/easycommerce/v1/reports/customers/stats"
				params={{ range: range.value, comparison: comparison.value }}
				skeletonCount={6}
			/>

			<div className="my-6">
				<Container title="Customers Over Time" >
					<LineChart 
						endpoint="/easycommerce/v1/reports/customers/over-time" 
						params={{ range: range.value, comparison: comparison.value }} 
					/>
				</Container>
			</div>
			
			<div className="my-6">
				<Container title="Top Customers" >
					<TopCustomers range={range.value} />
				</Container>
			</div>
			
			<div className="my-6">
				<Container title="Customers location" >
					<GeoMap
						endpoint="/easycommerce/v1/reports/customers/locations"
						params={{ range: range.value }}
					/>
				</Container>
			</div>

			<div className="my-6">
				<Container title="Customer By Day Heatmap" >
					<Heatmap
						endpoint="/easycommerce/v1/reports/customers/heatmap"
						params={{ range: range.value }}
						showValues={true}
					/>
				</Container>
			</div>
		</>
	);
};

export default Customers;
