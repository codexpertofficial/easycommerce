import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';

import Table from '../common/Table';
import SeeReportLink from '../common/SeeReportLink';

const TopSold = ({ range }) => {
	const [productsData, setProductsData] = useState([]);
    const [loading, setLoading] = useState(false);

	const fetchTopSoldProducts = (range) => {
		setLoading(true);
		apiFetch({
			path: addQueryArgs('/easycommerce/v1/reports/products/most-sold', {
				range,
			}),
		})
			.then((data) => {
				setProductsData(data.data.products);
                setLoading(false);
			})
			.catch((error) => {
				console.error('Error fetching top sold products:', error);
			});
	};

	useEffect(() => {
		fetchTopSoldProducts(range);
	}, [range]);

	const columns = [
		{ header: '#', width: '5%', accessor: 'rank', render: (item) => `#${item.rank}` },
		{ header: __( 'Product Name', 'easycommerce' ), width: '25%', accessor: 'name', render: (item) => <span className="truncate">{item.name}</span> },
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
		<div className="text-base text-[#1B2538]">
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

export default TopSold;
