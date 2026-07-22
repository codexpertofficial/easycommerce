import React, { useState } from 'react';
import { toast } from 'react-toastify';
import { __ } from '@wordpress/i18n';
import DeletePopup from '../../../../../../../../common/components/DeletePopup';

/**
 * AttributeSelectionTable Component
 *
 * State Management:
 * - showModal: Controls delete confirmation popup visibility
 * - attributeIdToDelete: Stores ID of attribute pending deletion
 *
 * Props:
 * @param {Array} attributes - Array of attribute objects to display
 * @param {Array<number>} selectedAttributes - Array of currently selected attribute IDs
 * @param {Function} setSelectedAttributes - Setter for selectedAttributes state
 * @param {Function} setAttributes - Setter for parent attributes state
 * @param {Function} fetchData - Function to refresh attributes data from server
 * @param {Function} onEdit - Callback when edit button is clicked (switches to edit mode)
 * @param {Function} setAttributesData - Setter for attribute form data
 * @param {Function} setField - Setter for field configurations
 * @param {Function} setEditingAttribute - Setter for currently editing attribute
 * @param {boolean} forceDelete - Whether to force delete attributes with dependencies
 *
 * Key Functions:
 * - getAttribute: Fetches full attribute data for editing
 * - handleDelete: Deletes an attribute from the global definitions
 * - handleCheckboxChange: Toggles attribute selection state
 *
 */
const AttributeSelectionTable = ({
	attributes,
	selectedAttributes,
	setSelectedAttributes,
	setAttributes,
	fetchData,
	onEdit,
	setAttributesData,
	setField,
	setEditingAttribute,
	forceDelete = false,
}) => {
	const [showModal, setShowModal] = useState(false);
	const [attributeIdToDelete, setAttributeIdToDelete] = useState(null);

	const getAttribute = async (attributeId) => {
		try {
			easycommerce_modal(true);
			const response = await fetch(
				`${EASYCOMMERCE.rest_base}/attributes/${attributeId}`,
				{
					method: 'GET',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': EASYCOMMERCE.nonce,
					},
				}
			);

			const result = await response.json();

			if (result.success) {
				if (onEdit) {
					onEdit(result.data);
				}
				easycommerce_modal(false);
			} else {
				console.error('API returned an error:', result);
			}
		} catch (error) {
			console.error('Failed to fetch attribute:', error);
		} finally {
			easycommerce_modal(false);
		}
	};

	const handleDelete = async () => {
		try {
			easycommerce_modal(true);
			const response = await fetch(
				`${EASYCOMMERCE.rest_base}/attributes/${attributeIdToDelete}?force=${forceDelete}`,
				{
					method: 'DELETE',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': EASYCOMMERCE.nonce,
					},
				}
			);

			const data = await response.json();
			easycommerce_modal(false);

			if (data.success) {
				setAttributes(
					attributes.filter((attribute) => attribute.id !== attributeIdToDelete)
				);
				fetchData();
				setAttributeIdToDelete(null);
				setAttributesData({
					attributes: [
						{
							attribute_name: '',
							attribute_slug: '',
							attribute_type: '',
							options: [],
						},
					],
				});
				setField([{ label: '', color: '', image: null, uploader: false }]);
				setEditingAttribute(null);

				toast.success(__('Attribute deleted successfully!', 'easycommerce'));
				setAttributeIdToDelete(null);
				setShowModal(false);
			} else {
				toast.error(__('Failed to delete attribute.', 'easycommerce'));
			}
		} catch (error) {
			console.error('Delete failed', error);
			toast.error(__('Delete failed, please try again.', 'easycommerce'));
			easycommerce_modal(false);
		} finally {
			easycommerce_modal(false);
		}
	};

	const handleCheckboxChange = (attributeId) => {
		setSelectedAttributes((prev) =>
			prev.includes(attributeId)
				? prev.filter((id) => id !== attributeId)
				: [...prev, attributeId]
		);
	};

	return (
		<>
			{attributes && attributes.length > 0 ? (
				<table className="w-full text-left text-ec-body bg-white border-separate border-spacing-0 overflow-hidden">
					<thead>
						<tr className="w-full h-[44px] text-ec-title text-sm bg-ec-table-bg">
							<th className="w-[10%] pl-5 font-normal rounded-l-md">
								<input
									type="checkbox"
									className="easycommerce-input-checkoutbox"
									onChange={() => {
										if (selectedAttributes.length === attributes.length) {
											setSelectedAttributes([]);
										} else {
											setSelectedAttributes(attributes.map((attr) => attr.id));
										}
									}}
									checked={
										selectedAttributes.length === attributes.length &&
										attributes.length > 0
									}
								/>
							</th>
							<th className="w-[15%] font-normal">{__('Name', 'easycommerce')}</th>
							<th className="w-[15%] font-normal">{__('Type', 'easycommerce')}</th>
							<th className="w-[30%] font-normal">{__('Options', 'easycommerce')}</th>
							<th className="w-[20%] text-center pr-5 font-normal rounded-r-md">
								{__('Action', 'easycommerce')}
							</th>
						</tr>
					</thead>
					<tbody>
						{attributes.map((value, i) => {
							const hasOptions = value.options && Array.isArray(value.options) && value.options.length > 0;
							const optionsString = hasOptions
								? value.options
										.map((opt) => opt.name)
										.join(', ')
								: '';
							
							return (
								<tr
									key={value.id}
									className={`w-full text-sm font-normal`}
									style={{ height: '56px' }}
								>
									<td
										className={`pl-5 ${
											i !== attributes.length - 1
												? 'border-b border-ec-table-stock'
												: ''
										}`}
									>
										<input
											type="checkbox"
											className="easycommerce-input-checkoutbox"
											checked={selectedAttributes.includes(value.id)}
											onChange={() => handleCheckboxChange(value.id)}
										/>
									</td>
									<td
										className={`${
											i !== attributes.length - 1
												? 'border-b border-ec-table-stock'
												: ''
										}`}
									>
										{value.name}
									</td>

									<td
										className={`${
											i !== attributes.length - 1
												? 'border-b border-ec-table-stock'
												: ''
										}`}
									>
										{value.type}
									</td>

									<td
										className={`${
											i !== attributes.length - 1
												? 'border-b border-ec-table-stock'
												: ''
										}`}
									>
										{optionsString.length > 30
											? optionsString.slice(0, 30) + '...'
											: optionsString}
									</td>

									<td
										className={`text-center pr-5 ${
											i !== attributes.length - 1
												? 'border-b border-ec-table-stock'
												: ''
										}`}
									>
										<div className="flex justify-end pr-5 gap-3">
											<button
												onClick={(e) => {
													e.preventDefault();
													getAttribute(value.id);
												}}
												className="flex h-8 w-8 rounded-full justify-center items-center hover:bg-ec-primary hover:text-white transition-colors duration-300"
											>
												<svg
													xmlns="http://www.w3.org/2000/svg"
													fill="none"
													viewBox="0 0 24 24"
													strokeWidth="1.5"
													stroke="currentColor"
													className="size-5 text-current"
												>
													<path
														strokeLinecap="round"
														strokeLinejoin="round"
														d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"
													/>
												</svg>
											</button>

											<button
												onClick={(e) => {
													e.preventDefault();
													setShowModal(true);
													setAttributeIdToDelete(value.id);
												}}
												className="group flex h-8 w-8 rounded-full justify-center items-center hover:bg-ec-red transition-colors duration-300"
											>
												<svg
													width="17"
													height="20"
													viewBox="0 0 17 20"
													fill="none"
													xmlns="http://www.w3.org/2000/svg"
													className="size-4 fill-ec-body group-hover:fill-white duration-300"
												>
													<path
														fillRule="evenodd"
														clipRule="evenodd"
														d="M15.1501 6.92017H1.85394L2.90454 17.3333C2.99002 18.184 3.68756 18.822 4.53044 18.822H12.4701C13.3147 18.822 14.0105 18.184 14.096 17.3333L15.1466 6.92017H15.1501ZM15.8442 5.1667C15.8442 4.37763 15.2074 3.73094 14.4303 3.73094H2.57632C1.79927 3.73094 1.16243 4.37765 1.16243 5.1667V5.74221H15.8443L15.8442 5.1667ZM5.04676 2.55558V2.49134C5.04676 1.1198 6.14951 0 7.50016 0H9.50217C10.8528 0 11.9556 1.1198 11.9556 2.49134V2.55558H14.4287C15.8443 2.55558 17 3.72918 17 5.1667V6.33077C17 6.65542 16.7393 6.91931 16.4204 6.91931H16.3084L15.245 17.4522C15.098 18.9036 13.9038 19.9991 12.4678 19.9991L4.53225 20C3.09698 20 1.90182 18.9045 1.75498 17.4531L0.691554 6.92017H0.579571C0.25987 6.92017 0 6.65542 0 6.33164V5.16757C0 3.73005 1.15573 2.55645 2.57135 2.55645H5.04444L5.04676 2.55558ZM10.7972 2.55558V2.49134C10.7972 1.76912 10.2125 1.17536 9.50123 1.17536H7.49921C6.78799 1.17536 6.20328 1.76912 6.20328 2.49134V2.55558H10.7972ZM5.75952 14.8916C5.75952 15.2163 5.49879 15.4802 5.17995 15.4802C4.86025 15.4802 4.60038 15.2154 4.60038 14.8916V10.8005C4.60038 10.4759 4.8611 10.212 5.17995 10.212C5.49965 10.212 5.75952 10.4767 5.75952 10.8005V14.8916ZM11.2261 10.8005C11.2261 10.4759 11.4868 10.212 11.8057 10.212C12.1254 10.212 12.3852 10.4767 12.3852 10.8005V14.8916C12.3852 15.2163 12.1245 15.4802 11.8057 15.4802C11.486 15.4802 11.2261 15.2154 11.2261 14.8916V10.8005ZM9.07277 15.9421C9.07277 16.2667 8.81205 16.5306 8.4932 16.5306C8.1735 16.5306 7.91363 16.2659 7.91363 15.9421V9.7485C7.91363 9.42385 8.17435 9.15996 8.4932 9.15996C8.8129 9.15996 9.07277 9.42472 9.07277 9.7485V15.9421Z"
													/>
												</svg>
											</button>
										</div>
									</td>
								</tr>
							)
						})}
					</tbody>
				</table>
			) : (
				<span className="text-ec-body font-inter text-sm leading-[20px]">
					{__('No attributes found.', 'easycommerce')}
				</span>
			)}

			{showModal && (
				<DeletePopup
					onClose={() => {
						setShowModal(false);
						setAttributeIdToDelete(null);
					}}
					onConfirm={handleDelete}
					itemName={
						attributes.find((attr) => attr.id === attributeIdToDelete)?.name ||
						''
					}
				/>
			)}
		</>
	);
};

export default AttributeSelectionTable;
