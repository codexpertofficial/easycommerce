import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

import PieChart from '../common/PieChart';

const OrderPieChart = ({ endpoint, params = {} }) => {
	const [data, setData] = useState([]);

	useEffect(() => {
		apiFetch({
			path: addQueryArgs(endpoint, params),
		})
			.then((res) => {
				if (res.data?.breakdown) {
					setData(res.data.breakdown);
				} else if (res.data?.datasets) {
					setData(res.data.datasets);
				}
			})
			.catch((err) => console.error('Error:', err));
	}, [endpoint, JSON.stringify(params)]);

	return <PieChart data={data} />;
};

export default OrderPieChart;
