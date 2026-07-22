import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';

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
		{ header: __( 'Product Name', 'easycommerce' ), width: '25%', render: (item) => <span className="truncate">{item.name}</span> },
		{ header: __( 'In Stock (Unit)', 'easycommerce' ), width: '15%', accessor: 'in_stock' },
		{ header: __( 'Units Sold', 'easycommerce' ), width: '15%', accessor: 'unit_sold' },
		{ header: __( 'Total Sale', 'easycommerce' ), width: '15%', accessor: 'total_sale' },
		{ header: __( 'Refunds', 'easycommerce' ), width: '15%', accessor: 'refunds' },
		{
			header: __( 'See Report', 'easycommerce' ),
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
