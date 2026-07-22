import React, { useState } from "react";
import { __ } from '@wordpress/i18n';

// components
import Container from '../components/common/Container';
import LineChart from '../components/common/LineChart';
import Header from '../components/common/Header';
import ReportStats from '../components/common/ReportStats';

import TopSold from '../components/products/TopSold';
import LeastSold from '../components/products/LeastSold';

const Products = () => {
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
				title={__( 'Products', 'easycommerce' )}
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
				<Container title={__( 'Products Sold Over Time', 'easycommerce' )} >
					<LineChart 
						endpoint="/easycommerce/v1/reports/products/sold-over-time" 
						params={{ range: range.value, comparison: comparison.value }} 
					/>
				</Container>
			</div>
			
			<div className="my-6">
				<Container title={__( ' Most sold products', 'easycommerce' )} >
					<TopSold range={range.value} />
				</Container>
			</div>
			
			<div className="my-6">
				<Container title={__( ' Least sold products', 'easycommerce' )} >
					<LeastSold range={range.value} />
				</Container>
			</div>
		</>
	);
};

export default Products;
