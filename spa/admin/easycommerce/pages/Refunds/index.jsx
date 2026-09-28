import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { toast } from 'react-toastify';

// Components
import RefundsTable from './components/RefundsTable';
import TableFilter from './components/TableFilter';
import Pagination from '../../../common/components/Pagination';
import NotFound from '../../../common/NotFound';
import TableSkeleton from '../../../common/TableSkeleton';

const noRefund = `${EASYCOMMERCE.assets}admin/img/nofound/no-refund.png`;

const Refunds = ({ page }) => {
	const [totalPage, setTotalPage] = useState(1);
	const [refunds, setRefunds] = useState([]);
	const [isLoading, setIsLoading] = useState(false);
	const [refundsFiltered, setRefundsFiltered] = useState(false);
	const [searchTrigger, setSearchTrigger] = useState(0);
	const [formState, setFormState] = useState({
		search: '',
		dateFrom: null,
		dateTo: null,
	});

	const handleSubmit = () => {
		setRefundsFiltered(!!(formState.search.trim() || formState.dateFrom || formState.dateTo));
		setSearchTrigger((prev) => prev + 1);
		window.location.hash = '#/refunds';
	};

	const resetFilter = () => {
		setFormState({
			search: '',
			dateFrom: null,
			dateTo: null,
		});
		setRefundsFiltered(false);
		setSearchTrigger((prev) => prev + 1);
		window.location.hash = '#/refunds';
	};

	const formatDate = (date) => {
		if (!date) return '';

		const month = String(date.getMonth() + 1).padStart(2, '0');
		const day = String(date.getDate()).padStart(2, '0');

		return `${date.getFullYear()}-${month}-${day}`;
	};

	const fetchRefunds = async (pageNumber) => {
		setIsLoading(true);

		let url = `${EASYCOMMERCE.rest_base}/refunds?page=${pageNumber}&per_page=10`;
		const searchValue = formState.search.trim();

		if (refundsFiltered && searchValue) {
			url += `&search=${encodeURIComponent(searchValue)}`;
		}
		if (refundsFiltered && formState.dateFrom) {
			url += `&from_date=${formatDate(formState.dateFrom)}`;
		}
		if (refundsFiltered && formState.dateTo) {
			url += `&to_date=${formatDate(formState.dateTo)}`;
		}

		try {
			const response = await fetch(
				url,
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
			toast.error(__('Unable to load refunds. Please refresh and try again.', 'easycommerce'));
		} finally {
			setIsLoading(false);
		}
	};

	useEffect(() => {
		fetchRefunds(page);
	}, [page, searchTrigger]);

	return (
		<>
			<div className="flex items-start justify-start gap-4 mb-4">
				<div className="product-panel-title">
					<h3>{__('Refunds', 'easycommerce')}</h3>
				</div>
			</div>

			<div className="w-full bg-white border border-solid border-ec-table-stock rounded-xl p-6 min-h-screen flex flex-col h-[94%]">
				<div className="flex justify-end gap-5 mb-4">
					<TableFilter
						formState={formState}
						setFormState={setFormState}
						handleSubmit={handleSubmit}
						refundsFiltered={refundsFiltered}
						resetFilter={resetFilter}
					/>
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
								title={__('No Refunds Found.', 'easycommerce')}
								description={__('Refunds will appear here once processed.', 'easycommerce')}
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
