import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

import DoughnutChart from '../common/DoughnutChart';

const OrderChart = ({ range }) => {
	const [orderData, setOrderData] = useState([]);
	const [isLoading, setIsLoading] = useState(false);

	const fetchOrderData = async (range) => {
		setIsLoading(true);
		apiFetch({
			path: addQueryArgs('/easycommerce/v1/reports/overview/order-status', {
				range,
			}),
		})
			.then((data) => {
				setOrderData(data.data);
				setIsLoading(false);
			})
			.catch((error) => {
				console.error('Error fetching order pie chart data:', error);
				setIsLoading(false);
			});
	};

	useEffect(() => {
		fetchOrderData(range);
	}, [range]);

	return <DoughnutChart data={orderData} isLoading={isLoading} />;
};

export default OrderChart;
