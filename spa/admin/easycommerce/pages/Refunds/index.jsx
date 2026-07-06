import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';

// Components
import RefundsTable from './components/RefundsTable';
import Pagination from '../../../common/components/Pagination';
import NotFound from '../../../common/NotFound';
import TableSkeleton from '../../../common/TableSkeleton';

const noRefund = `${EASYCOMMERCE.assets}admin/img/nofound/no-refund.png`;

const Refunds = ({ page }) => {
	const [totalPage, setTotalPage] = useState(1);
	const [refunds, setRefunds] = useState([]);
	const [isLoading, setIsLoading] = useState(false);

	const fetchRefunds = async (pageNumber) => {
		setIsLoading(true);
		try {
			const response = await fetch(
				`${EASYCOMMERCE.rest_base}/refunds?page=${pageNumber}&per_page=10`,
				{
					method: 'GET',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': EASYCOMMERCE.nonce,
					},
				},
			);
			const data = await response.json();

			if (data.success) {
				setRefunds(data.data.refunds || []);
				setTotalPage(data.data.total_pages || 1);
			}
		} catch (error) {
			console.error('Error fetching refunds:', error);
		} finally {
			setIsLoading(false);
		}
	};

	useEffect(() => {
		fetchRefunds(page);
	}, [page]);

	return (
		<>
			<div className="flex items-start justify-start gap-4 mb-4">
				<div className="product-panel-title">
					<h3>{__('Refunds', 'easycommerce')}</h3>
				</div>
			</div>

			<div className="w-full bg-white border border-solid border-ec-table-stock rounded-xl p-6 min-h-screen flex flex-col h-[94%]">
				<div className="flex justify-between gap-5 mb-4">
					{/* Status */}
					{/* Filters */}
				</div>

				{isLoading ? (
					<div className="flex flex-col gap-8 m-[15px] mb-10 rounded-2xl">
						<TableSkeleton numberOfRows={10} SkeletonHeight={30} />
					</div>
				) : (
					<>
						{refunds.length > 0 ? (
							<div className="flex flex-col rounded-2xl h-full">
								<RefundsTable refunds={refunds} />

								{totalPage > 1 && (
									<Pagination
										baseSlug="refunds"
										current={page}
										total={totalPage}
									/>
								)}
							</div>
						) : (
							<NotFound
								ImageUrl={noRefund}
								title={`No Refunds Found.`}
								description={`Refunds will appear here once processed.`}
								isBtn={false}
							/>
						)}
					</>
				)}
			</div>
		</>
	);
};

export default Refunds;
