import React, { useState } from "react";

// components
import Container from '../components/common/Container';
import LineChart from '../components/common/LineChart';
import Header from '../components/common/Header';
import ReportStats from '../components/common/ReportStats';

import TopSold from '../components/products/TopSold';
import LeastSold from '../components/products/LeastSold';

const Products = () => {
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
				title="Products"
				range={range}
				setRange={setRange}
				comparison={comparison}
				setComparison={setComparison}
			/>

			<ReportStats 
				endpoint="/easycommerce/v1/reports/products/stats"
				params={{ range: range.value, comparison: comparison.value }}
				skeletonCount={6}
			/>

			<div className="my-6">
				<Container title="Products Sold Over Time" >
					<LineChart 
						endpoint="/easycommerce/v1/reports/products/sold-over-time" 
						params={{ range: range.value, comparison: comparison.value }} 
					/>
				</Container>
			</div>
			
			<div className="my-6">
				<Container title=" Most sold products" >
					<TopSold range={range.value} />
				</Container>
			</div>
			
			<div className="my-6">
				<Container title=" Least sold products" >
					<LeastSold range={range.value} />
				</Container>
			</div>
		</>
	);
};

export default Products;
