import React, { useState, useEffect, useCallback } from 'react';

// css
import './style.css';

// components
import AddNewCustomer from './AddNew';
import CustomerTable from './components/CustomerTable';
import CustomerTableFilter from './components/CustomerTableFilter';
import Pagination from '../../../common/components/Pagination';
import NotFound from '../../../common/NotFound';
import TableSkeleton from '../../../common/TableSkeleton';
import { __, sprintf } from '@wordpress/i18n';
import { use } from 'react';

const noCustomers = `${EASYCOMMERCE.assets}admin/img/nofound/no-customer.png`;

const Customers = ({ page }) => {
	const [isAddNew, setIsAddNew] = useState(false);
	const [customers, setCustomers] = useState([]);
	const [isLoading, setIsLoading] = useState(true);
	const [isStatusLoaded, setisStatusLoaded] = useState(false);
	const [tableColumns] = useState([
		'title',
		'email',
		'customer_since',
		'total_orders',
		'lifetime_value',
		'avg_order_value',
		'last_order',
	]);
	const [totalPage, setTotalPage] = useState(1);
	const [postPerPage] = useState(20);
	const [searchCustomer, setSearchCustomer] = useState('');
	const [isFiltering, setIsFiltering] = useState(false);
	const [searchTrigger, setSearchTrigger] = useState(0);
	const [activeTab, setActiveTab] = useState('all');
	const [tabCounts, setTabCounts] = useState({
		all: 0,
		recurring: 0,
		one_time: 0,
	});

	const handleCustomerSearch = useCallback((value) => {
		setSearchCustomer(value);
		setIsFiltering(true);
		setSearchTrigger((prev) => prev + 1);
		window.location.hash = '#/customers';
	}, []);

	const tabOptions = [
		{
			label: __( 'All', 'easycommerce' ),
			key: 'all',
			bg: 'bg-ec-allBg text-ec-allText',
		},
		{
			label: __( 'Recurring', 'easycommerce' ),
			key: 'recurring',
			bg: 'bg-ec-recurringBg text-ec-recurringText',
		},
		{
			label: __( 'One-time', 'easycommerce' ),
			key: 'one_time',
			bg: 'bg-ec-oneTimeBg text-ec-oneTimeText',
		},
	];

	const handleResetFilter = useCallback(() => {
		setSearchCustomer('');
		setSearchTrigger((prev) => prev + 1);
		setIsFiltering(false);
		window.location.hash = '#/customers';
	}, []);
	// Keep the raw key for comparisons; the label is translated and must not drive logic.
	const tabKey = tabOptions.find((t) => t.key === activeTab)?.key || 'all';
	const tabLabel = tabOptions.find((t) => t.key === tabKey)?.label || '';
	const fetchCustomersStatuses = async () => {
		try {
			const url = `${EASYCOMMERCE.rest_base}/customers`;

			const res = await fetch(url, {
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': EASYCOMMERCE.nonce,
				},
			});
			const data = await res.json();

			if (data.data.statuses) {
				setTabCounts({
					all: data.data.statuses.all || 0,
					recurring: data.data.statuses.recurring || 0,
					one_time: data.data.statuses.one_time || 0,
				});
			}
			setisStatusLoaded(true);
		} catch (error) {
			console.error('Error fetching customers:', error);
		}
	};

	useEffect(() => {
		fetchCustomersStatuses();
	}, []);

	const fetchCustomers = async () => {
		setIsLoading(true);
		const querySep = EASYCOMMERCE.permalink ? '?' : '&';

		try {
			const url =
				`${EASYCOMMERCE.rest_base}/customers${querySep}` +
				`${searchCustomer ? `s=${encodeURIComponent(searchCustomer)}&` : ''}` +
				`page=${page}&per_page=${postPerPage}&type=${activeTab}`;

			const res = await fetch(url, {
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': EASYCOMMERCE.nonce,
				},
			});
			const data = await res.json();

			setIsLoading(false);

			if (data.success && Array.isArray(data.data?.customers)) {
				setCustomers(data.data.customers);
				setTotalPage(data.data.total_pages);
				if (data.data.statuses) {
					setTabCounts({
						all: data.data.statuses.all || 0,
						recurring: data.data.statuses.recurring || 0,
						one_time: data.data.statuses.one_time || 0,
					});
				}
			} else {
				setCustomers([]);
				setTotalPage(1);
			}
		} catch (error) {
			console.error('Error fetching customers:', error);
			setCustomers([]);
			setTotalPage(1);
		}
	};

	useEffect(() => {
		const storedTab = localStorage.getItem('easycommerce_customers_tab_active');
		if (storedTab) {
			setActiveTab(storedTab);
		}
		if (isStatusLoaded) {
			fetchCustomers();
		}
	}, [searchTrigger, activeTab, isStatusLoaded]);

	return (
		<>
			<div className="flex items-center justify-start gap-4 mb-4">
				<div className="product-panel-title">
					<h3>{ __( 'Customers', 'easycommerce' ) }</h3>
				</div>

				{/* <button
                    onClick={() => setIsAddNew(true)}
                    className="flex h-[41px] justify-center items-center gap-1 font-inter bg-white group border border-ec-primary px-3 py-2 rounded-lg text-ec-primary hover:text-white hover:bg-ec-primary focus:shadow-none focus:text-white focus:bg-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out duration-500"
                >
                    <svg
                        className="w-5 h-5 font-medium"
                        fill="none"
                        strokeWidth="1.5"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Customer
                </button> */}
			</div>

			<div className="w-full bg-white border border-solid border-ec-table-stock rounded-xl p-6 min-h-screen flex flex-col h-[94%]">
				<div className="flex justify-between gap-5 h-ec-input mb-4">
					{isStatusLoaded && (
						<>
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
													'easycommerce_customers_tab_active',
													tab.key,
												);
												window.location.hash = '#/customers';
											}}
											className={`relative flex items-center gap-[3px] font-inter text-sm text-ec-body transition-colors duration-300 
                                                ${
																									isActive
																										? 'after:absolute after:bottom-[-2px] after:left-0 after:right-0 after:h-[2px] after:bg-ec-primary'
																										: ''
																								}`}
										>
											<span>{tab.label}</span>
											<span
												className={`text-xs font-medium px-2 py-0.5 rounded-full bg-[#1212161A] text-ec-titlev ${tab.bg}`}
											>
												{count}
											</span>
										</button>
									);
								})}
							</div>
							<CustomerTableFilter
								customersFilterted={isFiltering}
								handleResetFilter={handleResetFilter}
								setIsAddNew={setIsAddNew}
								handleSearch={handleCustomerSearch}
							/>
						</>
					)}
				</div>

				{!isLoading ? (
					<>
						{customers.length > 0 ? (
							<>
								<CustomerTable
									tableColumns={tableColumns}
									customers={customers}
									isLoading={isLoading}
								/>
								{totalPage > 1 && (
									<Pagination
										baseSlug="customers"
										current={page}
										total={totalPage}
									/>
								)}
							</>
						) : (
							<NotFound
								ImageUrl={noCustomers}
								title={
									tabKey !== 'all'
										? sprintf(
												// translators: %s: customer type label (e.g. Recurring).
												__( 'No %s Customers Found', 'easycommerce' ),
												tabLabel,
										  )
										: __( 'No Customers Found', 'easycommerce' )
								}
								description={`${
									tabKey === 'all'
										? __( "You're yet to receive any customers in your store.", 'easycommerce' )
										: sprintf(
												// translators: %s: customer type label (e.g. Recurring).
												__( 'No %s customers in your store.', 'easycommerce' ),
												tabLabel,
										  )
								} ${ __( 'Keep promoting \nyour store to bring in your first customer.', 'easycommerce' ) }`}
								isBtn={false}
								btnText={ __( 'Add Customer', 'easycommerce' ) }
								btnCallBack={() => setIsAddNew(false)}
							/>
						)}
					</>
				) : (
					<div className="flex flex-col gap-8 m-[15px] mb-10 rounded-2xl">
						<TableSkeleton numberOfRows={15} SkeletonHeight={30} />
					</div>
				)}

				{isAddNew && <AddNewCustomer setIsAddNew={setIsAddNew} />}
			</div>
		</>
	);
};

export default Customers;
