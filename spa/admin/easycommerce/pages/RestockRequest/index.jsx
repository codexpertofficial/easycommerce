import React, { useEffect, useState } from "react";
import { __ } from "@wordpress/i18n";
import { toast } from "react-toastify";

import THead from "./components/Table/THead";
import TBody from "./components/Table/TBody";
import ActionBar from "./components/ActionBar";
import TableFilter from "../Transactions/components/TableFilter";
import Pagination from "../../../common/components/Pagination";
import TableSkeleton from "../../../common/TableSkeleton";
import NotFound from "../../../common/NotFound";
import DeletePopup from "../../../common/components/DeletePopup";

const noData = `${EASYCOMMERCE.assets}admin/img/nofound/no-data.png`;
const TAB_STORAGE_KEY = "easycommerce_restock_request_activeTab";
const PER_PAGE = 20;

const columnList = [
	{ key: "email", label: __("Email", "easycommerce") },
	{ key: "product", label: __("Product", "easycommerce") },
	{ key: "status", label: __("Status", "easycommerce") },
	{ key: "stock", label: __("Stock", "easycommerce") },
	{ key: "created_at", label: __("Requested", "easycommerce") },
];

const tabOptions = [
	{ key: "all", label: __("All", "easycommerce"), bg: "bg-ec-allBg text-ec-allText" },
	{ key: "pending", label: __("Waiting", "easycommerce"), bg: "bg-ec-pendingBg text-ec-pendingText" },
	{ key: "notified", label: __("Notified", "easycommerce"), bg: "bg-ec-completedBg text-ec-completedText" },
];

const toDateParam = (value) => {
	if (!value) return "";
	const date = new Date(value);
	return isNaN(date) ? "" : date.toISOString().slice(0, 10);
};

const RestockRequest = ({ page = 1 }) => {
	const [requests, setRequests] = useState([]);
	const [statusCounts, setStatusCounts] = useState({ all: 0, pending: 0, notified: 0 });
	const [isLoading, setIsLoading] = useState(true);
	const [totalPage, setTotalPage] = useState(1);
	const [selectedRequests, setSelectedRequests] = useState([]);
	const [requestToDelete, setRequestToDelete] = useState(null);
	const [refreshList, setRefreshList] = useState(false);
	const [activeTab, setActiveTab] = useState(() => {
		const saved = localStorage.getItem(TAB_STORAGE_KEY);
		return saved && tabOptions.some((tab) => tab.key === saved) ? saved : "all";
	});

	const [formState, setFormState] = useState({ search: "", dateForm: null, dateTo: null });
	const [isFiltered, setIsFiltered] = useState(false);
	const [searchTrigger, setSearchTrigger] = useState(0);

	const headers = {
		"Content-Type": "application/json",
		"X-WP-Nonce": EASYCOMMERCE.nonce,
	};

	useEffect(() => {
		setIsLoading(true);

		let url = `${EASYCOMMERCE.rest_base}/restock-requests?page=${page}&per_page=${PER_PAGE}&status=${activeTab}`;

		if (isFiltered) {
			const search = formState.search.trim();
			const from = toDateParam(formState.dateForm);
			const to = toDateParam(formState.dateTo);

			if (search) url += `&search=${encodeURIComponent(search)}`;
			if (from) url += `&from=${from}`;
			if (to) url += `&to=${to}`;
		}

		fetch(url, { headers })
			.then((res) => res.json())
			.then((data) => {
				setRequests(data.data?.requests || []);
				setStatusCounts(data.data?.statuses || { all: 0, pending: 0, notified: 0 });
				setTotalPage(Math.max(1, Math.ceil((data.data?.total || 0) / PER_PAGE)));
			})
			.catch(() => toast.error(__("Unable to load back in stock requests.", "easycommerce")))
			.finally(() => setIsLoading(false));
	}, [page, activeTab, searchTrigger, refreshList]);

	const toggleAll = (checked) => {
		setSelectedRequests(checked ? requests.map((request) => request.id) : []);
	};

	const toggleRequest = (id) => {
		setSelectedRequests((prev) =>
			prev.includes(id) ? prev.filter((requestId) => requestId !== id) : [...prev, id]
		);
	};

	const deleteRequest = (id) => {
		setRequestToDelete(null);
		easycommerce_modal(true);

		fetch(`${EASYCOMMERCE.rest_base}/restock-requests/${id}`, { method: "DELETE", headers })
			.then((res) => res.json())
			.then((data) => {
				if (!data.success) throw new Error();

				toast.success(data.data?.message || __("Request removed", "easycommerce"));
				setRefreshList((prev) => !prev);
			})
			.catch(() => toast.error(__("Unable to remove the request.", "easycommerce")))
			.finally(() => easycommerce_modal(false));
	};

	const handleSubmit = () => {
		setIsFiltered(true);
		setSearchTrigger((prev) => prev + 1);
		window.location.hash = "#/restock-request";
	};

	const resetFilter = () => {
		setFormState({ search: "", dateForm: null, dateTo: null });
		setIsFiltered(false);
		setSearchTrigger((prev) => prev + 1);
		window.location.hash = "#/restock-request";
	};

	return (
		<>
			<div className="product-panel-title mb-4">
				<h3>{__("Restock Request", "easycommerce")}</h3>
			</div>
			<div className="w-full bg-white border border-solid border-ec-table-stock rounded-xl p-6 min-h-screen flex flex-col">
				<div className="flex justify-between gap-5 xl:mb-4 lg:mb-6">
					{selectedRequests.length > 0 ? (
						<ActionBar
							selectedRequests={selectedRequests}
							setSelectedRequests={setSelectedRequests}
							refreshList={() => setRefreshList((prev) => !prev)}
						/>
					) : (
						<div className="flex items-center">
							<div className="flex overflow-x-auto overflow-y-hidden min-w-0">
								{tabOptions.map((tab) => {
									const isActive = activeTab === tab.key;

									return (
										<button
											key={tab.key}
											onClick={() => {
												setActiveTab(tab.key);
												setSelectedRequests([]);
												localStorage.setItem(TAB_STORAGE_KEY, tab.key);
												window.location.hash = "#/restock-request";
											}}
											className={`relative flex items-center px-2 gap-[3px] border-b-[2px] pb-1.5 font-inter text-sm text-ec-body transition-colors duration-300 
											${isActive ? "border-ec-primary" : "border-[#F0EDFB]"}`}
										>
											<span className="min-w-max">{tab.label}</span>
											<span className={`text-xs font-medium px-2 py-0.5 rounded-full ${tab.bg}`}>
												{statusCounts[tab.key] ?? 0}
											</span>
										</button>
									);
								})}
							</div>
						</div>
					)}

					<TableFilter
						formState={formState}
						setFormState={setFormState}
						handleInputChange={(e) =>
							setFormState((prev) => ({ ...prev, [e.target.name]: e.target.value }))
						}
						handleSubmit={handleSubmit}
						transactionsFiltered={isFiltered}
						resetFilter={resetFilter}
					/>
				</div>

				{isLoading ? (
					<div className="flex flex-col gap-8 m-[15px] mb-10 rounded-2xl">
						<TableSkeleton numberOfRows={15} SkeletonHeight={30} />
					</div>
				) : requests.length > 0 ? (
					<>
						<table className="w-full border-separate border-spacing-0">
							<THead
								columnList={columnList}
								allChecked={selectedRequests.length === requests.length}
								toggleAll={toggleAll}
							/>
							<TBody
								requests={requests}
								columnList={columnList}
								selectedRequests={selectedRequests}
								toggleRequest={toggleRequest}
								removeRequest={(id, email) => setRequestToDelete({ id, email })}
							/>
						</table>

						{totalPage > 1 && (
							<Pagination baseSlug="restock-request" current={page} total={totalPage} />
						)}
					</>
				) : (
					<NotFound
						ImageUrl={noData}
						title={__("No one is waiting on a restock", "easycommerce")}
						description={__(
							"When a shopper asks to be told a sold out product is back,\ntheir request lands here.",
							"easycommerce"
						)}
					/>
				)}

				{requestToDelete && (
					<DeletePopup
						onClose={() => setRequestToDelete(null)}
						onConfirm={() => deleteRequest(requestToDelete.id)}
						itemName={requestToDelete.email}
					/>
				)}
			</div>
		</>
	);
};

export default RestockRequest;
