import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';

const Customers = ({ range }) => {
	const [data, setData] = useState([]);
	const [isLoading, setIsLoading] = useState(true);

	const fetchData = async (range) => {
		setIsLoading(true);
		apiFetch({
			path: addQueryArgs('/easycommerce/v1/reports/orders/customers', { range }),
		})
			.then((result) => {
				setData(result.data || []);
				setIsLoading(false);
			})
			.catch((error) => {
				console.error('Error fetching customer data:', error);
				setIsLoading(false);
			});
	};

	useEffect(() => {
		fetchData(range);
	}, [range]);

	return (
		<>
			<div className="p-4 rounded-xl bg-[#F5F8FB]">
				<h4 className='text-[#0F172B] font-bold text-[42px] leading-[56px]'>
					{data?.avg_orders_per_customer}
				</h4>
				<span className='text-[#45556C] text-base'>{__( 'Average Order Per Customer', 'easycommerce' )}</span>
			</div>

			<div className="p-4 mt-3 text-[#3C3C42] font-medium text-base">
				<div className="flex items-center justify-between mb-6">
					{__( 'People Ordered once', 'easycommerce' )}
					<span>{data.ordered_once}</span>
				</div>

				<div className="flex items-center justify-between mb-6">
					{__( 'People Ordered twice', 'easycommerce' )}
					<span>{data.ordered_twice}</span>
				</div>

				<div className="flex items-center justify-between">
					{__( 'People Ordered three times', 'easycommerce' )}
					<span>{data.ordered_three_plus}</span>
				</div>
			</div>
		</>
	);
};

export default Customers;
