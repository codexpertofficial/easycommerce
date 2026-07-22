import React, { useState, useEffect, useRef } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { useDispatch } from 'react-redux';

// css
import './style.css';
import 'react-datepicker/dist/react-datepicker.css';
import { toast } from 'react-toastify';
// Components
import OrderTable from './components/OrderTable';
import OrderTableFilter from './components/OrderTableFilter';
import Pagination from '../../../common/components/Pagination';
import NotFound from '../../../common/NotFound';
import TableSkeleton from '../../../common/TableSkeleton';
import DeletePopup from '../../../common/components/DeletePopup';
import ActionBar from './components/ActionBar';

const noOrder = `${EASYCOMMERCE.assets}admin/img/nofound/no-orders.png`;


const Orders = ({ page, productId }) => {
	const [selectedOrders, setSelectedOrders] = useState([]);
	const [isBulkSelection, setIsBulkSelection] = useState(false);
	const [totalPage, setTotalPage] = useState();
	const [showModal, setShowModal] = useState(false);
	const [allShowModal, setAllShowModal] = useState(false);
	const [orderIdToDelete, setOrderIdToDelete] = useState(null);
	const [orders, setOrders] = useState([]);
	const [isLoading, setIsLoading] = useState(true);
	const [ordersFiltered, setOrdersFiltered] = useState(false);
	const [searchTrigger, setSearchTrigger] = useState(0);
	const [isBulkDelete, setIsBulkDelete] = useState(false);
	const [statusCounts, setStatusCounts] = useState({});
	const [orderToDeleteStatus, setOrderToDeleteStatus] = useState(null);
	const tabWrapperRef = useRef(null);
	const tabContainerRef = useRef(null);
	const [tabContainerNarrow, setTabContainerNarrow] = useState(false);
	const [activeTab, setActiveTab] = useState('');
	const [isStatusLoaded, setIsStatusLoaded] = useState(false);
	const [productTitle, setProductTitle] = useState(''); // State to store product title
	const orderStatuses = EASYCOMMERCE.order_statuses;

	const postPerPage = 10;

	const handleSelectAll = (checked) => {
		setIsBulkSelection(checked);
		if (checked) {
			const allIds = orders.map((order) => order.id);
			setSelectedOrders(allIds);
		} else {
			setSelectedOrders([]);
		}
	};

	const handleSelectOne = (id) => {
		setIsBulkSelection(false);
		setSelectedOrders((prev) =>
			prev.includes(id)
				? prev.filter((orderId) => orderId !== id)
				: [...prev, id],
		);
	};

	const [tableColumns, setTableColumns] = useState([
		'id',
		'customer',
		'status',
		'fulfillment',
		'items',
		'total',
		'transactions',
		'created_at',
	]);

	const [formState, setFormState] = useState({
		searchquery: '',
		dateForm: '',
		dateTo: '',
	});

	const statusStyles = {
		completed: 'bg-ec-completedBg text-ec-completedText',
		cancelled: 'bg-ec-cancelledBg text-ec-cancelledText',
		refunded: 'bg-ec-refundedBg text-ec-refundedText',
		pending: 'bg-ec-pendingBg text-ec-pendingText',
		on_hold: 'bg-ec-onHoldBg text-ec-onHoldText',
		processing: 'bg-ec-processingBg text-ec-processingText',
		partially_refunded:
			'bg-ec-partiallyRefundedBg text-ec-partiallyRefundedText',
		failed: "bg-ec-failedBg text-ec-failedText"
	};

	const handleTableFilterSubmit = () => {
		setOrdersFiltered(true);
		setSearchTrigger((prev) => prev + 1);
		window.location.hash = '#/orders';
	};

	const resetFilter = () => {
		setFormState({
			searchquery: '',
			dateForm: '',
			dateTo: '',
		});

		setOrdersFiltered(false);
		setSearchTrigger((prev) => prev + 1);

		// Remove product_id from URL
		const url = new URL(window.location);
		url.searchParams.delete('product_id');
		window.history.replaceState({}, '', url);

		window.location.hash = '#/orders';
	};

	const convertDateFormat = (dateString) => {
		if (!dateString) return '';

		const date = new Date(dateString);
		const day = String(date.getDate()).padStart(2, '0');
		const month = String(date.getMonth() + 1).padStart(2, '0');
		const year = date.getFullYear();

		return `${day}-${month}-${year}`;
	};

	const fetchStatusCounts = () => {
		let url = `${EASYCOMMERCE.rest_base}/orders?page=${page}&per_page=${postPerPage}`;
 
		fetch(url, {
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': EASYCOMMERCE.nonce,
			},
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success) {
					setStatusCounts(data.data.statuses_counts ?? {});
				}
				setIsStatusLoaded(true);
			})
			.catch(() => {
				setIsStatusLoaded(true);
				toast.error(__('Unable to load order status counts. Please refresh and try again.', 'easycommerce'));
			});
	};

	// All order statuses
	const tabOptions = [
		{
			label: __('All', 'easycommerce'),
			key: 'all',
			bg: 'bg-ec-allBg text-ec-allText',
		},
		...Object.entries(orderStatuses).map(([key, label]) => ({
			label,
			key,
			bg: statusStyles[key],
		})),
	];

	// Tab counts for "All"
	const tabCounts = {
		all: statusCounts
			? Object.values(statusCounts).reduce((a, b) => a + b, 0)
			: 0,
		...statusCounts,
	};
	// Keep the raw key for comparisons; the label is translated and must not drive logic.
	const tabKey = tabOptions.find((t) => t.key === activeTab)?.key || 'all';
	const tabLabel = tabOptions.find((t) => t.key === tabKey)?.label || '';

	const fetchOrders = () => {
		setIsLoading(true);

		const searchValue = formState.searchquery.trim();
		const fromDate = convertDateFormat(formState.dateForm);
		const toDate = convertDateFormat(formState.dateTo);

		// Use productId prop if available
		const effectiveProductId = productId;

		let url = `${EASYCOMMERCE.rest_base}/orders?page=${page}&per_page=${postPerPage}`;
		if (activeTab !== 'all') {
			url += `&status=${activeTab}`;
		}
		if (ordersFiltered) {
			if (searchValue) {
				url += `&search_query=${searchValue}`;
			}
			if (fromDate) {
				url += `&from=${fromDate}`;
			}
			if (toDate) {
				url += `&to=${toDate}`;
			}
		}

		// Add product_id filter if present
		if (effectiveProductId) {
			url += `&product_id=${effectiveProductId}`;
		}

		fetch(url, {
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': EASYCOMMERCE.nonce,
			},
		})
			.then((res) => res.json())
			.then((data) => {
				setIsLoading(false);
 
				if (data.success && Array.isArray(data.data?.orders)) {
					setTotalPage(data.data.total_pages);
					setOrders(data.data.orders);
				} else {
					setTotalPage(1);
					setOrders([]);
				}
			})
			.catch(() => {
				setIsLoading(false);
				toast.error(__('Unable to load orders. Please refresh and try again.', 'easycommerce'));
			});
	};

	const singleDeleteOrder = () => {
		if (!orderIdToDelete) return;

		easycommerce_modal(true);

		fetch(`${EASYCOMMERCE.rest_base}/orders/${orderIdToDelete}`, {
			method: 'DELETE',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': EASYCOMMERCE.nonce,
			},
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success) {
					setOrders((prev) =>
						prev.filter((order) => order.id !== orderIdToDelete),
					);
					setStatusCounts((prev) => {
						const updated = { ...prev };
						if (orderToDeleteStatus && updated[orderToDeleteStatus] > 0) {
							updated[orderToDeleteStatus] -= 1;
						}
						return updated;
					});
					toast.success(data.data.message);
				} else {
					toast.error(data.data?.message || 'Failed to delete the order.');
				}
			})
			.catch(() => {
				toast.error('Something went wrong. Please try again.');
			})
			.finally(() => {
				easycommerce_modal(false);
				setShowModal(false);
				setOrderIdToDelete(null);
				setOrderToDeleteStatus(null);
			});
	};

	useEffect(() => {
		const el = tabContainerRef.current;
		if (!el) return;
		const check = () => {
			setTabContainerNarrow(el.scrollWidth > el.clientWidth);
		};
		check();
		const observer = new ResizeObserver(check);
		observer.observe(el);
		return () => observer.disconnect();
	}, [isStatusLoaded]);

	useEffect(() => {
		fetchStatusCounts();
	}, []);

	// Fetch product title when productId is present
	useEffect(() => {
		const urlParams = new URLSearchParams(window.location.search);
		const productId = urlParams.get('product_id');

		if (productId) {
			fetch(`${EASYCOMMERCE.rest_base}/products/${productId}`, {
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': EASYCOMMERCE.nonce,
				},
			})
				.then((res) => res.json())
				.then((data) => {
					if (data.success && data.data?.title) {
						setProductTitle(data.data.title);
					}
				})
				.catch(() => {
					// If we can't fetch the product title, we'll just show the ID
					setProductTitle('');
				});
		} else {
			setProductTitle('');
		}
	}, []);

	useEffect(() => {
		const storedTab =
			localStorage.getItem('easycommerce_orders_activeTab') ?? 'all';
		setActiveTab(storedTab);
		if (isStatusLoaded) {
			fetchOrders();
		}
	}, [searchTrigger, activeTab, isStatusLoaded]);

	return (
		<>
			<div className="product-panel-title mb-4">
				<h3>{__('Orders', 'easycommerce')}</h3>
			</div>
			<div className="w-full bg-white border border-solid border-ec-table-stock rounded-xl p-6 min-h-screen flex flex-col">
				{isStatusLoaded && (
					<div className="flex justify-between gap-5 xl:mb-4 lg:mb-6">
						{selectedOrders.length > 0 ? (
							<ActionBar
								selectedOrders={selectedOrders}
								setSelectedOrders={setSelectedOrders}
								setOrders={setOrders}
								fetchStatusCounts={fetchStatusCounts}
							/>
						) : (
							<div
								ref={tabWrapperRef}
								className="flex items-center max-w-[calc(100%-546px)]"
							>
								<div
									ref={tabContainerRef}
									className="flex overflow-x-auto overflow-y-hidden min-w-0"
								>
									{tabOptions.map((tab) => {
										const isActive = activeTab === tab.key;
										const count = tabCounts[tab.key] ?? 0;
										return (
											<button
												key={tab.key}
												onClick={() => {
													setActiveTab(tab.key);
													localStorage.setItem(
														'easycommerce_orders_activeTab',
														tab.key,
													);
													window.location.hash = '#/orders';
												}}
												className={`relative flex items-center px-2 gap-[3px] border-b-[2px] pb-1.5 font-inter text-sm text-ec-body transition-colors duration-300 
                                                ${isActive ? 'border-ec-primary' : 'border-[#F0EDFB]'}`}
											>
												<span className="min-w-max">{tab.label}</span>
												<span
													className={`text-xs font-medium px-2 py-0.5 rounded-full ${tab.bg}`}
												>
													{count}
												</span>
											</button>
										);
									})}
								</div>
								{tabContainerNarrow && (
									<button
										onClick={() => {
											if (tabContainerRef.current) {
												tabContainerRef.current.scrollBy({
													left: 100,
													behavior: 'smooth',
												});
											}
										}}
										className="ml-1 p-1 text-ec-body hover:text-ec-primary transition-colors duration-300 w-6 h-6 flex items-center justify-center rounded-full bg-[#f7f7f7] hover:bg-ec-table-stock"
									>
										<svg
											xmlns="http://www.w3.org/2000/svg"
											fill="currentColor"
											width="18px"
											height="18px"
											viewBox="0 0 24 24"
										>
											<path d="m11.293 17.293 1.414 1.414L19.414 12l-6.707-6.707-1.414 1.414L15.586 11H6v2h9.586z" />
										</svg>
									</button>
								)}
							</div>
						)}
						<OrderTableFilter
							formState={formState}
							setFormState={setFormState}
							handleSubmit={handleTableFilterSubmit}
							ordersFiltered={ordersFiltered}
							resetFilter={resetFilter}
						/>
					</div>
				)}
				{isLoading ? (
					<>
						<div className="flex flex-col rounded-2xl">
							<div className="flex flex-col gap-8 m-[15px] mb-10 rounded-2xl">
								<TableSkeleton numberOfRows={15} SkeletonHeight={30} />
							</div>
						</div>
					</>
				) : (
					<>
						{orders.length > 0 ? (
							<>
								<OrderTable
									tableColumns={tableColumns}
									orders={orders}
									setOrderIdToDelete={setOrderIdToDelete}
									setShowModal={setShowModal}
									selectedOrders={selectedOrders}
									handleSelectOne={handleSelectOne}
									handleSelectAll={handleSelectAll}
									isBulkSelection={isBulkSelection}
									setAllShowModal={setAllShowModal}
									setIsBulkDelete={setIsBulkDelete}
									setOrders={setOrders}
									setStatusCounts={setStatusCounts}
									setOrderToDeleteStatus={setOrderToDeleteStatus}
								/>
								{totalPage > 1 && (
									<Pagination
										baseSlug="orders"
										current={page}
										total={totalPage}
									/>
								)}
								{showModal && (
									<DeletePopup
										onClose={() => {
											setShowModal(false);
											setOrderIdToDelete(null);
										}}
										onConfirm={singleDeleteOrder}
										itemName={sprintf(__('Order #%s', 'easycommerce'), orderIdToDelete)}
									/>
								)}
							</>
						) : (
							<NotFound
								ImageUrl={noOrder}
								title={
									tabKey !== 'all'
										? // translators: %s: order status label (e.g. Pending).
										  sprintf(__('No %s Orders Found', 'easycommerce'), tabLabel)
										: __('No Orders Found', 'easycommerce')
								}
								description={
									tabKey === 'all'
										? __("You're yet to receive any orders in your store. Keep promoting \nyour store to bring in your first sale.", 'easycommerce')
										: // translators: %s: order status label (e.g. Pending).
										  sprintf(__('No %s orders in your store. Keep promoting \nyour store to bring in your first sale.', 'easycommerce'), tabLabel)
								}
								btnCallBack={() => {
									window.location.hash = '#/transactions/';
								}}
							/>
						)}
					</>
				)}
			</div>
		</>
	);
};

export default Orders;
