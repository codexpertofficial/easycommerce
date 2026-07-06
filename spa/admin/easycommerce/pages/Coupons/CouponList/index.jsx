import React, { useEffect, useState } from "react";
import { applyFilters } from '@wordpress/hooks';
import "./style.css";
import THead from "../components/Table/THead";
import TBody from "../components/Table/TBody";
import Pagination from "../../../../common/components/Pagination";
import TableSkeleton from "../../../../common/TableSkeleton";
import NotFound from "../components/NotFound";
import ActionBar from "../components/ActionBar";
import TableFilter from "../../Transactions/components/TableFilter";

const TAB_STORAGE_KEY = "easycommerce_coupon_active_tab";

const tabOptions = [
	{ key: "all", label: "All", countStyle: "bg-ec-allBg" },
	{ key: "active", label: "Active", countStyle: "bg-ec-activeBg text-ec-activeText" },
	{ key: "inactive", label: "Inactive", countStyle: "bg-ec-inactiveBg text-ec-inactiveText" },
];

const columnList = [
    "name", "code", "type", "offer", "usage", "status"
];

const CouponList = ({
	page,
	perPage,
	deleteCoupon,
	statusCounts,
	refreshStatusCounts,
	setRefreshList,
	coupons,
	setCoupons,
	formState,
	setFormState,
	couponsFiltered,
	searchTrigger,
	handleInputChange,
	handleDropdownChange,
	handleSubmit,
	resetFilter
}) => {

	const [activeTab, setActiveTab] = useState(() => {
		const saved = localStorage.getItem(TAB_STORAGE_KEY);
		return saved && ["all", "active", "inactive"].includes(saved) ? saved : "all";
	});

	const [isLoading, setIsLoading] = useState(true);
	const [totalPage, setTotalPage] = useState(1);
	const [selectedCoupons, setSelectedCoupons] = useState([]);
	const [postPerPage, setPostPerPage] = useState(10);

	useEffect(() => {
		localStorage.setItem(TAB_STORAGE_KEY, activeTab);
	}, [activeTab]);

	const convertDateFormat = (dateString) => {
		if (!dateString) return "";

		const date = new Date(dateString);
		const day = String(date.getDate()).padStart(2, "0");
		const month = String(date.getMonth() + 1).padStart(2, "0");
		const year = date.getFullYear();

		return `${day}-${month}-${year}`;
	};

	useEffect(() => {
		setIsLoading(true);

		const searchValue = formState.search.trim();
		const fromDate = convertDateFormat(formState.dateForm);
		const toDate = convertDateFormat(formState.dateTo);
		let url = `${EASYCOMMERCE.rest_base}/coupons?page=${page}&per_page=${postPerPage}`;

		if (activeTab === "active") {
			url += "&active=1";
		} else if (activeTab === "inactive") {
			url += "&active=0";
		}

		if (couponsFiltered) {
			if (searchValue) {
				url += `&search=${encodeURIComponent(searchValue)}`;
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
				"Content-Type": "application/json",
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			}
		})
			.then((res) => res.json())
			.then((data) => {
				setIsLoading(false);
				if (data.success && Array.isArray(data.data?.coupons)) {
					setCoupons(data.data.coupons);
					setTotalPage(data.data?.total_pages || 1);
				} else {
					setCoupons([]);
					setTotalPage(1);
				}
			});
	}, [activeTab, page, perPage, searchTrigger]);

	/**
	 * Filters the coupons list.
	 *
	 * @since 1.0.0
	 * @param {Array} coupons The coupons array.
	 */
	const filteredCoupons = applyFilters('easycommerce.coupons.list', coupons);

 	return (
 		<div className="flex flex-col pb-16 h-full">
 			<div className="flex flex-row justify-between lg:flex-wrap items-center mb-4">
				<div className="flex justify-between gap-5">
					<div className="w-max flex gap-4">
						{selectedCoupons.length > 0 ? (
							<ActionBar
								selectedCoupons={selectedCoupons}
								refreshStatusCounts={refreshStatusCounts}
								setRefreshList={setRefreshList}
								setSelectedCoupons={setSelectedCoupons}
								setCoupons={setCoupons}
							/>

						) : (
							<div className="w-max flex gap-4 xl:flex-wrap border-b-2 border-[#F0EDFB] xl:mt-4">
								{tabOptions.map((tab) => {
									const isActive = activeTab === tab.key;
									const count = statusCounts?.[tab.key] ?? 0;
									return (
										<button
											key={tab.key}
											onClick={() => {
												setActiveTab(tab.key);
												window.location.hash = '#/coupons';
											}}
											className={`h-9 relative flex items-center gap-[3px] font-inter text-sm text-ec-body transition-colors duration-300
												${isActive ? "after:absolute after:bottom-[-2px] after:left-0 after:right-0 after:h-[2px] after:bg-ec-primary" : ""}`}
										>
											<span>{tab.label}</span>
											<span className={`text-xs font-medium px-2 py-0.5 rounded-full ${tab.countStyle}`}>
												{count}
											</span>
										</button>
									);
								})}
							</div>
						)}
					</div>
				</div>
				{selectedCoupons.length === 0 && (
					<TableFilter
						formState={formState}
						handleSubmit={handleSubmit}
						handleInputChange={handleInputChange}
						transactionsFiltered={couponsFiltered}
						resetFilter={resetFilter}
						handleDropdownChange={handleDropdownChange}
						setFormState={setFormState}
					/>
				)}

			</div>

			{isLoading ? (
				<TableSkeleton numberOfRows={10} SkeletonHeight={30} />
			) : filteredCoupons.length === 0 ? (
				<NotFound title="No coupons found for this tab." />
			) : (
				<>
					<div className="w-full overflow-x-auto h-full">
						<table className="w-full border-collapse border-spacing-0">
							<THead
								columnList={columnList}
								allChecked={filteredCoupons.length > 0 && selectedCoupons.length === filteredCoupons.length}
								toggleAll={() => {
									if (selectedCoupons.length === filteredCoupons.length) {
										setSelectedCoupons([]);
									} else {
										setSelectedCoupons(filteredCoupons.map((c) => c.id));
									}
								}}
							/>

							<TBody
								coupons={filteredCoupons}
								setCoupons={setCoupons}
								columnList={columnList}
								deleteCoupon={deleteCoupon}
								onCouponStatusChange={() => {
									refreshStatusCounts();
									setRefreshList((prev) => !prev);
								}}
								selectedCoupons={selectedCoupons}
								toggleCoupon={(id) => {
									setSelectedCoupons((prev) =>
										prev.includes(id)
											? prev.filter((item) => item !== id)
											: [...prev, id]
									);
								}}
							/>
						</table>
					</div>

					{totalPage > 1 && (
						<Pagination baseSlug="coupons" current={page} total={totalPage} />
					)}
				</>
			)}

		</div>
	);
};

export default CouponList;
