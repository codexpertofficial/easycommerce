import React, { useEffect, useState } from 'react';
import { useDispatch } from 'react-redux';
import { addToastData } from '../../redux-store/slices/toastSlice';
import CouponsSkeleton from './components/CouponsSkeleton';
import CouponList from './CouponList';
import DeletePopup from '../../../common/components/DeletePopup';

const Coupons = ({ page }) => {
	const dispatch = useDispatch();
	const [coupons, setCoupons] = useState([]);
	const [isShowModal, setIsShowModal] = useState(false);
	const [couponIdToDelete, setCouponIdToDelete] = useState(null);
	const [statusCounts, setStatusCounts] = useState({});
	const [isStatusLoaded, setIsStatusLoaded] = useState(false);
	const [refreshList, setRefreshList] = useState(false);
	const [couponNameToDelete, setCouponNameToDelete] = useState(null);
	const [formState, setFormState] = useState({
		search: '',
		dateForm: null,
		dateTo: null,
	});
	const [couponsFiltered, setCouponsFiltered] = useState(false);
	const [searchTrigger, setSearchTrigger] = useState(0);

	const fetchStatusCounts = () => {
		fetch(`${EASYCOMMERCE.rest_base}/coupons?page=1&per_page=1`, {
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': EASYCOMMERCE.nonce,
			},
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success && data.data?.statuses) {
					setStatusCounts(data.data.statuses);
				}
				setIsStatusLoaded(true);
			});
	};

	const deleteCoupon = (id) => {
		setIsShowModal(false);
		easycommerce_modal(true);
		fetch(`${EASYCOMMERCE.rest_base}/coupons/${id}`, {
			method: 'DELETE',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': EASYCOMMERCE.nonce,
			},
		})
			.then((res) => res.json())
			.then((data) => {
				easycommerce_modal(false);
				if (data.success && data.data?.id) {
					dispatch(
						addToastData({
							type: 'success',
							message: data.data?.message || 'Coupon Deleted',
						}),
					);
					setCoupons((prev) => prev.filter((coupon) => coupon.id !== id));
					fetchStatusCounts();
				}
			});
	};

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

	const handleSubmit = () => {
		setCouponsFiltered(true);
		setSearchTrigger((prev) => prev + 1);
		window.location.hash = '#/coupons';
	};

	const resetFilter = () => {
		setFormState({
			search: '',
			dateForm: null,
			dateTo: null,
		});
		setCouponsFiltered(false);
		setSearchTrigger((prev) => prev + 1);
		window.location.hash = '#/coupons';
	};

	useEffect(() => {
		fetchStatusCounts();
	}, []);

	return (
		<>
			<div className="flex items-center justify-start gap-4 mb-4">
				<div className="product-panel-title">
					<h3>Coupons</h3>
				</div>
				<a
					href="#/coupons/new"
					className="flex h-[41px] justify-center items-center gap-1 font-inter bg-white group border border-ec-primary
                     px-3 py-2 rounded-lg text-ec-primary hover:text-white hover:bg-ec-primary focus:shadow-none
                     focus:text-white focus:bg-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out
                     duration-500"
				>
					<svg
						class="w-4 h-4 font-medium"
						data-slot="icon"
						fill="none"
						stroke-width="1.5"
						stroke="currentColor"
						viewBox="0 0 24 24"
						xmlns="http://www.w3.org/2000/svg"
						aria-hidden="true"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							d="M12 4.5v15m7.5-7.5h-15"
						></path>
					</svg>
					Create Coupon
				</a>
			</div>
			<div className="w-full bg-white border border-ec-table-stock rounded-xl p-6 h-full">
				{isStatusLoaded ? (
					<CouponList
						page={page}
						deleteCoupon={(id, name) => {
							setIsShowModal(true);
							setCouponIdToDelete(id);
							setCouponNameToDelete(name);
						}}
						statusCounts={statusCounts}
						refresh={refreshList}
						setRefreshList={setRefreshList}
						refreshStatusCounts={fetchStatusCounts}
						coupons={coupons}
						setCoupons={setCoupons}
						formState={formState}
						setFormState={setFormState}
						couponsFiltered={couponsFiltered}
						searchTrigger={searchTrigger}
						handleInputChange={handleInputChange}
						handleDropdownChange={handleDropdownChange}
						handleSubmit={handleSubmit}
						resetFilter={resetFilter}
					/>
				) : (
					<CouponsSkeleton />
				)}

				{isShowModal && (
					<DeletePopup
						onClose={() => {
							setIsShowModal(false);
							setCouponIdToDelete(null);
							setCouponNameToDelete(null);
						}}
						onConfirm={() => deleteCoupon(couponIdToDelete)}
						itemName={couponNameToDelete || ''}
					/>
				)}
			</div>
		</>
	);
};

export default Coupons;
