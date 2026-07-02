import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

import Table from '../common/Table';
import SeeReportLink from '../common/SeeReportLink';

const LeastSold = ({ range }) => {
	const [productsData, setProductsData] = useState([]);
	const [loading, setLoading] = useState(false);

	const fetchProducts = (range) => {
		setLoading(true);
		apiFetch({
			path: addQueryArgs('/easycommerce/v1/reports/products/least-sold', {
				range,
			}),
		})
			.then((data) => {
				setProductsData(data.data.products);
				setLoading(false);
			})
			.catch((error) => {
				console.error('Error fetching least sold products:', error);
			});
	};

	useEffect(() => {
		fetchProducts(range);
	}, [range]);

	const columns = [
		{ header: '#', width: '5%', render: (item) => `#${item.rank}` },
		{ header: 'Product Name', width: '25%', render: (item) => <span className="truncate">{item.name}</span> },
		{ header: 'In Stock (Unit)', width: '15%', accessor: 'in_stock' },
		{ header: 'Units Sold', width: '15%', accessor: 'unit_sold' },
		{ header: 'Total Sale', width: '15%', accessor: 'total_sale' },
		{ header: 'Refunds', width: '15%', accessor: 'refunds' },
		{ 
			header: 'See Report', 
			width: '10%', 
			render: (item) => <SeeReportLink href={`#/reports/products/${item.product_id}`} />
		},
	];

	return (
		<div className="rounded-lg bg-white text-base text-[#1B2538]">
			<Table
				columns={columns}
				data={productsData}
				loading={loading}
				skeletonRows={5}
				skeletonColumns={7}
			/>
		</div>
	);
};

export default LeastSold;
