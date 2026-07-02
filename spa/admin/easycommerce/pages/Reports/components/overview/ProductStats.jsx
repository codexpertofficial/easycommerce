import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

import StatRow from '../common/StatRow';
import TableSkeleton from '../common/TableSkeleton';

const ProductStats = ({ endpoint, params = {} }) => {
	const [productStats, setProductStats] = useState([]);
	const [loading, setLoading] = useState(false);

	const fetchProductStats = async () => {
		setLoading(true);
		apiFetch({
			path: addQueryArgs(endpoint, params),
		}).then((data) => {
			setProductStats(data.data.stats);
			setLoading(false);
		});
	};

	useEffect(() => {
		if (endpoint) {
			fetchProductStats();
		}
	}, [endpoint, JSON.stringify(params)]);

	if (loading) {
		return <TableSkeleton columns={1} rows={4} />;
	}

	return (
		<div className="flex flex-col justify-between gap-6">
			{productStats.map((data, index) => (
				<StatRow
					key={index}
					icon={data.icon}
					title={data.title}
					value={data.value}
				/>
			))}
		</div>
	);
};

export default ProductStats;
