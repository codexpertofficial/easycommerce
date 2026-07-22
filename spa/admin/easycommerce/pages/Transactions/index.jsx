import React, { useState, useEffect } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { toast } from 'react-toastify';

//components
import TransactionTable from './components/TransactionTable';
import Pagination from '../../../common/components/Pagination';
import NotFound from '../../../common/NotFound';
import TableSkeleton from '../../../common/TableSkeleton';
import TableFilter from './components/TableFilter';

const noTransaction = `${EASYCOMMERCE.assets}admin/img/nofound/no-transaction.png`;

const Transactions = ({ page }) => {
	const [isLoading, setIsLoading] = useState(true);
	const [transactions, setTransactions] = useState([]);
	const [tableColumns, setTableColumns] = useState([
		'order_id',
		'name',
		'type',
		'amount',
		'transaction_id',
		'date',
	]);
	const [formState, setFormState] = useState({
		search: '',
		dateForm: null,
		dateTo: null,
	});

	const [totalPage, setTotalPage] = useState(1);
	const [postPerPage, setPostPerPage] = useState(20);
	const [transactionsFiltered, setTransactionsFiltered] = useState(false);
	const [searchTrigger, setSearchTrigger] = useState(0);
	const [filterLoader, setFilterLoader] = useState(false);
	const [typeCounts, setTypeCounts] = useState({});
	const [isStatusLoaded, setIsStatusLoaded] = useState(false);
	const [activeTab, setActiveTab] = useState(() => {
		return localStorage.getItem('easycommerce_transactionTab') || 'all';
	});

	const typeStyles = {
		payment: 'bg-ec-paymentBg text-ec-paymentText',
		refund: 'bg-ec-refundBg text-ec-refundText',
		adjustment: 'bg-ec-adjustmentBg text-ec-adjustmentText',
	};

	const transactionType = {
		payment: __( 'Payment', 'easycommerce' ),
		refund: __( 'Refund', 'easycommerce' ),
		adjustment: __( 'Adjustment', 'easycommerce' ),
	};

	// All order statuses
	const tabOptions = [
		{
			label: __( 'All', 'easycommerce' ),
			key: 'all',
			bg: 'bg-ec-allBg text-ec-allText',
		},
		...Object.entries(transactionType).map(([key, label]) => ({
			label,
			key,
			bg: typeStyles[key],
		})),
	];

	// Tab counts for "All"
	const tabCounts = {
		all: Object.values(typeCounts).reduce((a, b) => a + b, 0),
		...Object.keys(transactionType).reduce((acc, key) => {
			acc[key] = typeCounts[key] ?? 0;
			return acc;
		}, {}),
	};

	const tabLabel =
		tabOptions.find((t) => t.key === activeTab)?.label.toLowerCase() ||
		'transactions';

	const handleInputChange = (e) => {
		const { name, value } = e.target;
		setFormState((prevState) => ({
			...prevState,
			[name]: value,
		}));
	};

	const handleDropdownChange = (field, value) => {
		setFormState((prevState) => ({
			...prevState,
			[field]: value,
		}));
	};

	const handleDateForm = (dateForm) => {
		setFormState((prevState) => ({
			...prevState,
			dateForm: dateForm,
		}));
	};
	const handleDateTo = (dateTo) => {
		setFormState((prevState) => ({
			...prevState,
			dateTo: dateTo,
		}));
	};

	const handleSubmit = () => {
		setTransactionsFiltered(true);
		setSearchTrigger((prev) => prev + 1);
		window.location.hash = '#/transactions';
	};

	const resetFilter = () => {
		setFormState({
			// searchBy: "",
			search: '',
			dateForm: '',
			dateTo: '',
		});
		setTransactionsFiltered(false);
		setSearchTrigger((prev) => prev + 1);
		window.location.hash = '#/transactions';
	};

	const convertDateFormat = (dateString) => {
		if (!dateString) return '';

		const date = new Date(dateString);
		const day = String(date.getDate()).padStart(2, '0');
		const month = String(date.getMonth() + 1).padStart(2, '0');
		const year = date.getFullYear();

		return `${day}-${month}-${year}`;
	};

	const fetchTransactionsStatuses = () => {
		setIsLoading(true);

		let url = `${EASYCOMMERCE.rest_base}/transactions`;

		fetch(url, {
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': EASYCOMMERCE.nonce,
			},
		})
			.then((response) => response.json())
			.then((data) => {
				if (data.success && Array.isArray(data.data?.transactions)) {
					if (data.data.types_counts) {
						setTypeCounts(data.data.types_counts);
					}
				}
				setIsStatusLoaded(true);
			})
			.catch(() => {
				setIsStatusLoaded(true);
				toast.error(__('Unable to load transaction status counts. Please refresh and try again.', 'easycommerce'));
			});
	};

	const fetchTransactions = () => {
		setIsLoading(true);
		transactionsFiltered && setFilterLoader(true);
		const searchValue = formState.search.trim();
		const fromDate = convertDateFormat(formState.dateForm);
		const toDate = convertDateFormat(formState.dateTo);

		let url = `${EASYCOMMERCE.rest_base}/transactions?page=${page}&per_page=${postPerPage}`;

		if (activeTab !== 'all') {
			url += `&type=${activeTab}`;
		}
		if (transactionsFiltered) {
			if (searchValue) {
				url += `&order_id=${searchValue}`;
			}
			if (fromDate) {
				url += `&from=${fromDate}`;
			}
			if (toDate) {
				url += `&to=${toDate}`;
			}
		}

		fetch(url, {
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': EASYCOMMERCE.nonce,
			},
		})
			.then((response) => response.json())
			.then((data) => {
				setIsLoading(false);

				if (data.success && Array.isArray(data.data?.transactions)) {
					setTotalPage(data.data.total_pages);
					setTransactions(data.data.transactions);
					if (data.data.types_counts) {
						setTypeCounts(data.data.types_counts);
					}
				} else {
					setTotalPage(1);
					setTransactions([]);
				}
			})
			.catch(() => {
				setIsLoading(false);
				setFilterLoader(false);
				toast.error(__('Unable to load transactions. Please refresh and try again.', 'easycommerce'));
			});
	};

	useEffect(() => {
		fetchTransactionsStatuses();
	}, []);

	useEffect(() => {
		fetchTransactions();
	}, [page, postPerPage, activeTab, searchTrigger, transactionsFiltered]);

	return (
		<>
			<div className="product-panel-title mb-4">
				<h3>{__( 'Transactions', 'easycommerce' )}</h3>
			</div>
			<div className="w-full bg-white border border-solid border-ec-table-stock rounded-xl p-6 min-h-screen">
				{isStatusLoaded && (
					<div className="flex xl:flex-wrap justify-between gap-5 mb-4">
						<div className="flex gap-4 flex-wrap border-b-2 border-[#F0EDFB]">
							{tabOptions.map((tab) => {
								const isActive = activeTab === tab.key;
								const count = tabCounts[tab.key] ?? 0;
								return (
									<button
										key={tab.key}
										onClick={() => {
											setActiveTab(tab.key);
											localStorage.setItem(
												'easycommerce_transactions_activeTab',
												tab.key,
											);
											window.location.hash = '#/transactions';
										}}
										className={`relative flex items-center gap-[3px] font-inter text-sm text-ec-body transition-colors duration-300 
                                            ${isActive ? 'after:absolute after:bottom-[-2px] after:left-0 after:right-0 after:h-[2px] after:bg-ec-primary' : ''}`}
									>
										<span>{tab.label}</span>
										<span
											className={`text-xs font-medium px-2 py-0.5 rounded-full ${tab.bg}`}
										>
											{count}
										</span>
									</button>
								);
							})}
						</div>

						<TableFilter
							formState={formState}
							handleSubmit={handleSubmit}
							handleInputChange={handleInputChange}
							handleDateTo={handleDateTo}
							handleDateForm={handleDateForm}
							transactionsFiltered={transactionsFiltered}
							resetFilter={resetFilter}
							handleDropdownChange={handleDropdownChange}
							setFormState={setFormState}
						/>
					</div>
				)}
				{!isLoading ? (
					<>
						{transactions && transactions.length > 0 ? (
							<>
								<TransactionTable
									transactions={transactions}
									tableColumns={tableColumns}
								/>
								{totalPage > 1 && (
									<Pagination
										baseSlug="transactions"
										current={page}
										total={totalPage}
									/>
								)}
							</>
						) : (
							<NotFound
								ImageUrl={noTransaction}
								title={
									activeTab !== 'all'
										? (
											// translators: %s: transaction status label.
											sprintf( __( 'No %s Transactions Found', 'easycommerce' ), tabOptions.find((tab) => tab.key === activeTab)?.label || activeTab )
										)
										: __( 'No Transactions Found.', 'easycommerce' )
								}
								description={__( 'All type of payment activities will appear here once they occur', 'easycommerce' )}
							/>
						)}
					</>
				) : (
					<TableSkeleton numberOfRows={15} SkeletonHeight={30} />
				)}
			</div>
		</>
	);
};
export default Transactions;
