import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

import TrendIndicator from '../common/TrendIndicator';
import TableSkeleton from '../common/TableSkeleton';

const CustomerOverview = ({ endpoint, params = {} }) => {
	const [customerData, setCustomerData] = useState({
		overview: [],
		customers: [],
	});
	const [isLoading, setIsLoading] = useState(false);

	const fetchCustomerOverview = async () => {
		setIsLoading(true);
		apiFetch({
			path: addQueryArgs(endpoint, params),
		})
			.then((data) => {
				setCustomerData(data.data);
				setIsLoading(false);
			})
			.catch((error) => {
				console.error('Error fetching customer overview data:', error);
				setIsLoading(false);
			});
	};

	useEffect(() => {
		if (endpoint) {
			fetchCustomerOverview();
		}
	}, [endpoint, JSON.stringify(params)]);

	if (isLoading) {
		return <TableSkeleton columns={6} rows={4} />;
	}

	return (
		<div className="flex gap-4">
			<div className="flex flex-col ec-db-lg:w-[260px] w-[195px] border border-[#F0EDFB] rounded-lg">
				{customerData.overview.map((item, index) => (
					<div
						className={`p-4 ${index !== customerData.overview.length - 1 ? 'border-b border-[#F0EDFB]' : ''}`}
						key={index}
					>
						<div className="flex items-center gap-3">
							<div className="rounded-[10px] bg-[#F3F3FF] ec-db-lg:flex items-center justify-center w-11 h-11 hidden">
								<img
									src={
										EASYCOMMERCE.assets +
										'admin/img/reports/' +
										item.icon +
										'.svg'
									}
									alt=""
								/>
							</div>
							<h3 className="text-base text-ec-title mb-2">{item.title}</h3>
						</div>

						<div className="flex items-end gap-2 ec-db-lg:ml-[56px] ml-0 mt-3">
							<h4 className="text-2xl font-medium text-[#3C3C42]">
								{item.value}
							</h4>

							<TrendIndicator
								type={item.comparison.type}
								value={item.comparison.value}
								size="sm"
							/>
						</div>
					</div>
				))}
			</div>

			<div className="bg-white rounded-lg flex-1 overflow-hidden w-full">
				<div className="flex h-full">
					<div className="flex items-center justify-center px-3 bg-[#F7F7F7]">
						<span
							className="text-sm font-medium text-[#3C3C42] tracking-widest uppercase"
							style={{
								writingMode: 'vertical-rl',
								transform: 'rotate(180deg)',
							}}
						>
							Top Customers
						</span>
					</div>

					<div className="flex-1 flex flex-col">
						<div className="grid grid-cols-3 px-4 py-4 bg-[#F7F7F7]">
							<span className="text-sm font-medium text-[#1B2538]">Name</span>
							<span className="text-sm font-medium text-[#1B2538] text-center">
								Orders
							</span>
							<span className="text-sm font-medium text-[#1B2538] text-right">
								Purchased
							</span>
						</div>

						<div className={`flex flex-col flex-1 ${customerData.customers.length === 5 ? 'justify-between' : 'justify-start'}`}>
							<div className="h-[1px] w-full"></div>
							{customerData.customers.map((customer, index) => (
								<>
									<div
										key={index}
										className="grid grid-cols-3 px-4 py-3"
									>
										<span className="text-sm text-[#1B2538] truncate">
											{customer.name}
										</span>
										<span className="text-sm text-[#1B2538] text-center">
											{customer.orders}
										</span>
										<span className="text-sm text-[#1B2538] text-right">
											{customer.spent}
										</span>
									</div>

									<div className="bg-[#EEF0FF] h-[1px] w-full"></div>
								</>
							))}
						</div>
					</div>
				</div>
			</div>
		</div>
	);
};

export default CustomerOverview;
