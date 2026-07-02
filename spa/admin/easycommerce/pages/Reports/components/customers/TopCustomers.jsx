import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

import Table from '../common/Table';
import SeeReportLink from '../common/SeeReportLink';

const TopCustomers = ({ range }) => {
	const [customers, setCustomers] = useState([]);
	const [loading, setLoading] = useState(true);

	const fetchTopCustomers = async (range) => {
		setLoading(true);
		apiFetch({
			path: addQueryArgs('/easycommerce/v1/reports/customers/top-customers', {
				range,
			}),
		})
			.then((response) => {
				setCustomers(response.data.customers);
			})
			.catch((error) => {
				console.error('Error fetching top customers:', error);
			})
			.finally(() => {
				setLoading(false);
			});
	};

	useEffect(() => {
		fetchTopCustomers(range);
	}, [range]);

	const columns = [
		{ header: '#', width: '10%', accessor: 'rank' },
		{ header: 'Customer Name', width: '30%', accessor: 'name' },
		{ header: 'Order Placed', width: '15%', accessor: 'total_orders' },
		{ header: 'Product Purchased', width: '15%', accessor: 'product_count' },
		{ header: 'Total Purchase', width: '20%', accessor: 'total_purchase' },
		{
			header: ' See Report',
			width: '10%',
			render: (customer) => <SeeReportLink href={`#/customers/${customer.customer_id}`} />,
		},
	];

	return (
		<div className="text-base text-[#1B2538]">
			<Table
				columns={columns}
				data={customers}
				loading={loading}
				skeletonRows={5}
				skeletonColumns={10}
			/>
		</div>
	);
};

export default TopCustomers;
