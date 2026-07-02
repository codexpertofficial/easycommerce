import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

// components
import Container from '../components/common/Container';
import LineChart from '../components/common/LineChart';
import GeoMap from '../components/common/GeoMap';
import Header from '../components/common/Header';
import ReportStats from '../components/common/ReportStats';

import Reviews from '../components/singleProduct/Reviews';
import ProductInfo from '../components/singleProduct/ProductInfo';

const SingleProduct = ({ productId, setBreadcrumbTitle }) => {
	const [range, setRange] = useState({
		label: 'Last 30 days',
		value: 'last-30',
	});

	const [comparison, setComparison] = useState({
		label: 'Prev. 30 days',
		value: 'prev-30'
	});

	const [productData, setProductData] = useState(null);
	const [isLoading, setIsLoading] = useState(false);

	useEffect(() => {
		if (productId) {
			setIsLoading(true);
			apiFetch({
				path: addQueryArgs('/easycommerce/v1/reports/products/single-info', {
					product_id: productId,
					range: range.value,
					comparison: comparison.value,
				}),
			})
				.then(data => {
					if (data.success && data.data) {
						setProductData(data.data);
					}
				})
				.catch(err => console.error('Failed to fetch product name:', err))
				.finally(() => setIsLoading(false));
		}
	}, [productId, range.value, comparison.value]);

	useEffect(() => {
		if (productData && setBreadcrumbTitle) {
			setBreadcrumbTitle(productData.name);
		}
	}, [productData]);

	return (
		<>
			<Header
				title={isLoading ? 'Loading...' : productData?.name}
				range={range}
				setRange={setRange}
				comparison={comparison}
				setComparison={setComparison}
			/>

			<ReportStats 
				endpoint="/easycommerce/v1/reports/products/single-stats"
				params={{ product_id: productId, range: range.value, comparison: comparison.value }}
				skeletonCount={4}
			/>

			<div className="my-6 flex gap-6">
				<div className="w-[40%]">
					<Container title="Basic Info">
						<ProductInfo
							product={productData}
					    />
					</Container>
				</div>
				<div className="w-[60%]">
					<Container title="Sales Over Time">
						<LineChart 
							endpoint="/easycommerce/v1/reports/products/single-sales-over-time" 
							params={{ product_id: productId, range: range.value }} 
						/>
					</Container>
				</div>
			</div>

			<div className="my-6">
				<Container title="Product Sale by Location">
					<GeoMap
						endpoint="/easycommerce/v1/reports/products/locations"
						params={{ range: range.value, product_id: productId }}
					/>
				</Container>
			</div>

			<div className="my-6">
				<Container title="Reviews">
					<Reviews
						endpoint="/easycommerce/v1/reports/products/single-reviews"
						params={{ product_id: productId, range: range.value }}
					/>
				</Container>
			</div>
		</>
	);
};

export default SingleProduct;
