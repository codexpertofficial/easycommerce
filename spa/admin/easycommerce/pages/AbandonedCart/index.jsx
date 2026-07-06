import React, { useEffect, useState } from 'react';
import { useDispatch } from 'react-redux';
import { addToastData } from '../../redux-store/slices/toastSlice';
import TableSkeleton from '../../../common/TableSkeleton';
import AbandonedCartsList from './AbandonedCartsList';
import DeletePopup from '../../../common/components/DeletePopup';

const columnList = [
	'name',
	'email',
	'items',
	'total',
	'Last Activity',
	'reminders',
	'actions',
];

const AbandonedCart = ({ page }) => {
	const dispatch = useDispatch();
	const [tableColumns] = useState(columnList);
	const [postPerPage] = useState(20);
	const [abandonedCarts, setAbandonedCarts] = useState([]);
	const [isShowModal, setIsShowModal] = useState(false);
	const [abandonedCartHashToDelete, setAbandonedCartHashToDelete] =
		useState(null);
	const [abandonedCartNameToDelete, setAbandonedCartNameToDelete] =
		useState(null);
	const [refreshList, setRefreshList] = useState(false);

	// Filter state
	const [formState, setFormState] = useState({
		search: '',
		dateForm: null,
		dateTo: null,
	});
	const [cartsFiltered, setCartsFiltered] = useState(false);
	const [searchTrigger, setSearchTrigger] = useState(0);

	const deleteAbandonedCart = (hash) => {
		setIsShowModal(false);
		easycommerce_modal(true);
		fetch(`${EASYCOMMERCE.rest_base}/abandoned-carts/remove/?hash=${hash}`, {
			method: 'DELETE',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': EASYCOMMERCE.nonce,
			},
		})
			.then((res) => res.json())
			.then((data) => {
				easycommerce_modal(false);
				if (data.success && data.data?.hash) {
					dispatch(
						addToastData({
							type: 'success',
							message: data.data?.message || 'Abandoned Cart Deleted',
						}),
					);
					setAbandonedCarts((prev) =>
						prev.filter((cart) => cart.hash !== hash),
					);
					setRefreshList((prev) => !prev);
				}
			});
	};

	// Filter handlers
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
		setCartsFiltered(true);
		setSearchTrigger((prev) => prev + 1);
		window.location.hash = '#/abandoned-cart';
	};

	const resetFilter = () => {
		setFormState({
			search: '',
			dateForm: null,
			dateTo: null,
		});
		setCartsFiltered(false);
		setSearchTrigger((prev) => prev + 1);
		window.location.hash = '#/abandoned-cart';
	};

	return (
		<>
			<div className="product-panel-title mb-4">
				<h3>Abandoned Carts</h3>
			</div>
			<div className="w-full bg-white border border-ec-table-stock rounded-xl p-6 h-full">
				<AbandonedCartsList
					page={page}
					perPage={postPerPage}
					columnList={tableColumns}
					deleteAbandonedCart={(hash, name) => {
						setIsShowModal(true);
						setAbandonedCartHashToDelete(hash);
						setAbandonedCartNameToDelete(name);
					}}
					refreshList={refreshList}
					setRefreshList={setRefreshList}
					abandonedCarts={abandonedCarts}
					setAbandonedCarts={setAbandonedCarts}
					formState={formState}
					setFormState={setFormState}
					cartsFiltered={cartsFiltered}
					searchTrigger={searchTrigger}
					handleInputChange={handleInputChange}
					handleDropdownChange={handleDropdownChange}
					handleSubmit={handleSubmit}
					resetFilter={resetFilter}
				/>

				{isShowModal && (
					<DeletePopup
						onClose={() => {
							setIsShowModal(false);
							setAbandonedCartHashToDelete(null);
							setAbandonedCartNameToDelete(null);
						}}
						onConfirm={() => deleteAbandonedCart(abandonedCartHashToDelete)}
						itemName={abandonedCartNameToDelete || ''}
					/>
				)}
			</div>
		</>
	);
};

export default AbandonedCart;
