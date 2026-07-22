import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';

import Table from '../common/Table';
import TableSkeleton from '../common/TableSkeleton';

const TopSellingProducts = ({ range, comparison }) => {
	const [productsData, setProductsData] = useState([]);
	const [isLoading, setIsLoading] = useState(false);

	const fetchProductsData = async (range, comparison) => {
		setIsLoading(true);

		apiFetch({
			path: addQueryArgs('/easycommerce/v1/reports/overview/top-selling', {
				range,
				comparison,
			}),
		})
			.then((data) => {
				setProductsData(data.data.products);
				setIsLoading(false);
			})
			.catch((error) => {
				console.error('Error fetching top selling products data:', error);
				setIsLoading(false);
			});
	};

	useEffect(() => {
		fetchProductsData(range, comparison);
	}, [range, comparison]);

	if (isLoading) {
		return <TableSkeleton columns={6} rows={4} />;
	}

	if (productsData.length === 0) {
		return (
			<span className="text-base text-center block pt-6">
				{__( 'No data available for the selected range.', 'easycommerce' )}
			</span>
		);
	}

	const columns = [
		{ header: __( 'Rank', 'easycommerce' ), width: '15%', render: (item) => `#${item.rank}` },
		{
			header: __( 'Image', 'easycommerce' ),
			width: '20%',
			render: (item) =>
				item.thumbnail ? (
					<img
						src={item.thumbnail}
						alt={item.name}
						style={{
							width: '50px',
							height: '50px',
							objectFit: 'contain',
						}}
					/>
				) : (
					<svg
						className="opacity-50"
						width="35"
						height="35"
						viewBox="0 0 35 35"
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
					>
						<path
							d="M35.0014 22.2295V24.6506C35.0014 25.0524 34.9839 25.4577 34.9488 25.8594C34.4579 31.3879 30.6177 35.0003 25.0766 35.0003H9.92625C7.1207 35.0003 4.75345 34.0897 3.05253 32.4388C2.37855 31.8201 1.81518 31.0908 1.38672 30.2825C1.96537 29.579 2.61415 28.8088 3.24538 28.0368C4.31507 26.756 5.34965 25.491 5.99843 24.6682C6.96288 23.4751 9.50543 20.3346 13.03 21.8084C13.7489 22.1066 14.3802 22.5277 14.9589 22.8961C16.3792 23.8436 16.9754 24.1243 17.9749 23.5804C19.0795 22.9838 19.7986 21.8084 20.5525 20.5802C20.9558 19.9293 21.3591 19.2995 21.7975 18.7205C23.7088 16.2291 26.6547 15.5624 29.1097 17.0362C30.3371 17.773 31.3892 18.7029 32.3712 19.6486C32.5816 19.8591 32.792 20.0539 32.9849 20.2469C33.2479 20.51 34.1247 21.3873 35.0014 22.2295Z"
							fill="#A4A4A4"
						/>
						<path
							d="M25.0927 0H9.92488C3.98049 0 0 4.15817 0 10.3497V24.6505C0 26.8067 0.490957 28.7209 1.38526 30.2824C1.96391 29.5788 2.6127 28.8086 3.24401 28.0349C4.3137 26.7559 5.34819 25.4909 5.99698 24.668C6.96142 23.475 9.50398 20.3345 13.0285 21.8083C13.7475 22.1065 14.3788 22.5276 14.9574 22.896C16.3778 23.8435 16.974 24.1242 17.9735 23.5785C19.0782 22.9838 19.7971 21.8083 20.5511 20.5784C20.9544 19.9292 21.3577 19.2993 21.7961 18.7204C23.7074 16.229 26.6534 15.5622 29.1082 17.036C30.3357 17.7729 31.3878 18.7028 32.3698 19.6485C32.5802 19.859 32.7906 20.0538 32.9835 20.2467C33.2465 20.5081 34.1233 21.3854 35 22.2293V10.3497C35 4.15808 31.0195 0 25.0927 0Z"
							fill="#DBDBDB"
						/>
						<path
							d="M16.544 11.8947C16.544 14.3598 14.4907 16.4125 12.027 16.4125C9.56504 16.4125 7.51172 14.3598 7.51172 11.8947C7.51172 9.43143 9.56504 7.37695 12.027 7.37695C14.4907 7.37695 16.544 9.43143 16.544 11.8947Z"
							fill="#A4A4A4"
						/>
					</svg>
				),
		},
		{
			header: __( 'Name', 'easycommerce' ),
			width: '20%',
			render: (item) => <span className="truncate">{item.name}</span>,
		},
		{ header: __( 'Units Sold', 'easycommerce' ), width: '25%', accessor: 'unit_sold' },
		{
			header: __( 'Revenue', 'easycommerce' ),
			width: '20%',
			render: (item) =>
				EASYCOMMERCE.currency_symbol + Number(item.revenue).toFixed(2),
		},
	];

	return (
		<div className="flex gap-4 h-[338px] ec-db-lg:flex-row flex-col">
			<div className="ec-db-lg:w-[35%] p-4 border border-[#EEF0FF] rounded-lg relative">
				<span className="absolute top-4 left-4 text-2xl font-medium text-[#282828]">
					#{productsData[0].rank}
				</span>

				<div className="flex ec-db-lg:flex-col gap-4">
					{productsData[0].thumbnail ? (
						<img
							src={productsData[0].thumbnail}
							alt={productsData[0].name}
							style={{
								width: '165px',
								height: '165px',
								objectFit: 'contain',
							}}
							className="mx-auto"
						/>
					) : (
						<svg
							className="ec-db-lg:mx-auto ml-8 ec-db-lg:w-[172px] ec-db-lg:h-[172px] w-[90px] h-[90px]"
							width="172"
							height="172"
							viewBox="0 0 172 172"
							fill="none"
							xmlns="http://www.w3.org/2000/svg"
						>
							<g opacity="0.58" clipPath="url(#clip0_9586_5626)">
								<mask
									id="mask0_9586_5626"
									style={{ maskType: 'luminance' }}
									maskUnits="userSpaceOnUse"
									x="0"
									y="0"
									width="172"
									height="172"
								>
									<path d="M0 0H172V172H0V0Z" fill="white" />
								</mask>

								<g mask="url(#mask0_9586_5626)">
									<path
										d="M157.664 105.367V115.282C157.664 116.928 157.592 118.588 157.449 120.233C155.438 142.873 139.712 157.667 117.02 157.667H54.9753C43.4859 157.667 33.7915 153.938 26.8258 147.177C24.0657 144.643 21.7586 141.656 20.0039 138.346C22.3736 135.465 25.0305 132.311 27.6156 129.149C31.9962 123.904 36.233 118.724 38.89 115.354C42.8396 110.468 53.252 97.6073 67.6859 103.643C70.63 104.864 73.2154 106.589 75.5851 108.097C81.4015 111.977 83.8431 113.127 87.9365 110.899C92.4602 108.456 95.4047 103.643 98.4923 98.6131C100.144 95.9474 101.796 93.3681 103.591 90.997C111.418 80.7943 123.482 78.0638 133.536 84.0992C138.563 87.117 142.871 90.9252 146.893 94.7978C147.754 95.6599 148.616 96.4577 149.406 97.2478C150.483 98.3255 154.074 101.918 157.664 105.367Z"
										fill="#A4A4A4"
									/>
									<path
										d="M117.093 14.334H54.9768C30.6331 14.334 14.332 31.3627 14.332 56.7185V115.284C14.332 124.114 16.3426 131.953 20.005 138.348C22.3747 135.466 25.0316 132.312 27.617 129.144C31.9976 123.906 36.2341 118.725 38.8911 115.355C42.8407 110.47 53.2531 97.6085 67.687 103.644C70.6315 104.865 73.2165 106.59 75.5862 108.099C81.403 111.979 83.8446 113.128 87.9376 110.894C92.4617 108.458 95.4058 103.644 98.4938 98.6073C100.145 95.9487 101.797 93.3693 103.592 90.9983C111.42 80.7955 123.484 78.065 133.537 84.1005C138.564 87.1182 142.872 90.9264 146.894 94.7991C147.756 95.6614 148.617 96.459 149.407 97.2491C150.484 98.3197 154.075 101.912 157.665 105.368V56.7185C157.665 31.3623 141.364 14.334 117.093 14.334Z"
										fill="#DBDBDB"
									/>
									<path
										d="M82.0871 63.0444C82.0871 73.1393 73.6782 81.5458 63.5887 81.5458C53.5065 81.5458 45.0977 73.1393 45.0977 63.0444C45.0977 52.9565 53.5065 44.543 63.5887 44.543C73.6782 44.543 82.0871 52.9565 82.0871 63.0444Z"
										fill="#A4A4A4"
									/>
								</g>
							</g>

							<defs>
								<clipPath id="clip0_9586_5626">
									<rect width="172" height="172" fill="white" />
								</clipPath>
							</defs>
						</svg>
					)}
					<div className='flex-grow'>
						<h4 className="text-2xl font-medium text-[#3C3C42] mb-8 text-center truncate">
							{productsData[0].name}
						</h4>
					
						<div className="flex items-center justify-between">
							<div className="flex flex-col items-center justify-center">
								<span className="text-2xl font-medium text-[#3C3C42] text-center block">
									{productsData[0].unit_sold}
								</span>
								<span className="text-base text-[#3C3C42] text-center block">
									{__( 'Units Sold', 'easycommerce' )}
								</span>
							</div>

							<div className="flex flex-col items-center justify-center">
								<span className="text-2xl font-medium text-[#02BA02] text-center block">
									{EASYCOMMERCE.currency_symbol +
										Number(productsData[0].revenue).toFixed(2)}
								</span>
								<span className="text-base text-[#3C3C42] text-center block">
									{__( 'Revenue', 'easycommerce' )}
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div className="ec-db-lg:w-[65%] text-base text-[#1B2538]">
				<Table columns={columns} data={productsData.slice(1)} loading={false} />
			</div>
		</div>
	);
};

export default TopSellingProducts;
