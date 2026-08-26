import React, { useEffect, useState } from 'react';
import { toast } from 'react-toastify';
import { __, sprintf } from '@wordpress/i18n';
import Title from '../../../common/Title';

import AttributeForm from '../../../../../Attributes/components/AttributeForm'
import AttributeSelectionTable from './components/AttributeSelectionTable';

/**
 * AttributeModal Component
 *
 * State Management:
 * - attributes: Array of all available attributes fetched from server
 * - attributesData: Form data structure for new attribute creation
 * - field: Array of field configurations for attribute options (text, color, image uploader)
 * - mode: Current modal mode ('list', 'add', 'edit')
 * - editingAttribute: Currently selected attribute for editing
 *
 * Props:
 * @param {boolean} isOpen - Controls modal visibility
 * @param {Function} onClose - Callback when modal is closed
 * @param {Function} onAttributeAdded - Callback when attributes are selected for product (receives selected IDs array)
 * @param {Array<number>} selectedAttributes - Array of currently selected attribute IDs
 * @param {Function} setSelectedAttributes - Setter for selectedAttributes state
 *
 * Modes:
 * - 'list': Shows attribute selection table with checkboxes and action buttons
 * - 'add': Shows form to create a new global attribute
 * - 'edit': Shows form to edit an existing global attribute
 *
 * Key Functions:
 * - handleAddNew: Switches to add mode for creating new attributes
 * - handleEdit: Switches to edit mode for a specific attribute
 * - handleBackToList: Returns to list mode from add/edit modes
 * - handleAttributeCreated: Handles successful attribute creation, refreshes data
 * - handleAttributeUpdated: Handles successful attribute update, refreshes data
 * - fetchData: Fetches all attributes from the server
 * 
 */
const AttributeModal = ({ globalAttributes, isOpen, onClose, onAttributeAdded, selectedAttributes, setSelectedAttributes }) => {
	const [attributes, setAttributes] = useState(globalAttributes || []);
	const [attributesData, setAttributesData] = useState({
		attributes: [
			{
				attribute_name: '',
				attribute_slug: '',
				attribute_type: '',
				options: [],
			},
		],
	});
	const [field, setField] = useState([
		{ label: '', color: '', image: null, uploader: false },
	]);
	const [mode, setMode] = useState('list'); // 'list', 'add', 'edit'
	const [editingAttribute, setEditingAttribute] = useState(null);

	const handleAddNew = () => {
		setMode('add');
		setEditingAttribute(null);
	};

	const handleEdit = (attribute) => {
		setMode('edit');
		setEditingAttribute(attribute);
	};

	const handleBackToList = () => {
		setMode('list');
		setEditingAttribute(null);
	};

	const handleAttributeCreated = async () => {
		await fetchData();
		setMode('list');
		setEditingAttribute(null);
		if (onAttributeAdded) {
			onAttributeAdded();
		}
	};

	const handleAttributeUpdated = async () => {
		await fetchData();
		setMode('list');
		setEditingAttribute(null);
	};

	const fetchData = async () => {
		try {
			const response = await fetch(`${EASYCOMMERCE.rest_base}/attributes`, {
				method: 'GET',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': EASYCOMMERCE.nonce,
				},
			});

			const jsonData = await response.json();
			setAttributes(jsonData.data.attributes);
		} catch (error) {
			console.error('Error fetching data:', error);
		} finally {
			easycommerce_modal(false);
		}
	};

	useEffect(() => {
		globalAttributes.length === 0 && fetchData();
	}, []);

	if (!isOpen) return null;

	return (
		<div className="w-screen h-screen inset-0 flex items-center justify-center font-inter backdrop-blur-sm fixed top-0 left-0 bg-[#00000082] z-[9999]">
			<div className="relative bg-white w-[800px] max-h-[90vh] rounded-xl shadow-lg z-10">
				{mode === 'list' && (
					<div>
						<div className="flex justify-between items-center border-b px-6 py-4">
							<Title title={__('Attributes', 'easycommerce')} />
							<button onClick={onClose}>
								<svg
									xmlns="http://www.w3.org/2000/svg"
									width="10"
									height="10"
									viewBox="0 0 10 10"
									fill="none"
								>
									<path
										fillRule="evenodd"
										clipRule="evenodd"
										d="M0.260418 0.260418C0.607642 -0.086806 1.17015 -0.086806 1.51731 0.260418L5 3.7431L8.48269 0.260418C8.82991 -0.086806 9.39241 -0.086806 9.73958 0.260418C10.0868 0.607642 10.0868 1.17015 9.73958 1.51731L6.2569 5L9.73958 8.48269C10.0868 8.82991 10.0868 9.39241 9.73958 9.73958C9.39236 10.0868 8.82985 10.0868 8.48269 9.73958L5 6.2569L1.51731 9.73958C1.17009 10.0868 0.607587 10.0868 0.260418 9.73958C-0.0867505 9.39236 -0.086806 8.82985 0.260418 8.48269L3.7431 5L0.260418 1.51731C-0.086806 1.17009 -0.086806 0.607587 0.260418 0.260418Z"
										fill="#7F7F98"
									/>
								</svg>
							</button>
						</div>
						<div className="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
							<div className="border-b border-ec-table-stock">
								<AttributeSelectionTable
									attributes={attributes}
									selectedAttributes={selectedAttributes}
									setSelectedAttributes={setSelectedAttributes}
									setAttributes={setAttributes}
									fetchData={fetchData}
									onEdit={handleEdit}
									setAttributesData={setAttributesData}
									setField={setField}
									setEditingAttribute={setEditingAttribute}
								/>
							</div>

							<div className="flex justify-between items-center">
								<button
									onClick={handleAddNew}
									className="border-b border-ec-primary text-ec-primary w-max py-1"
								>
									+ {__('Add New Attribute', 'easycommerce')}
								</button>

								{attributes.length > 0 && (
									<button
										onClick={(e) => {
											e.preventDefault();
											if (selectedAttributes.length > 0) {
												onAttributeAdded(selectedAttributes);
											}
										}}
										className="easycommerce-outline-button group flex gap-[6px] items-center"
									>
										{__('Add to Product', 'easycommerce')}
									</button>
								)}
							</div>
						</div>
					</div>
				)}

				{(mode === 'add' || mode === 'edit') && (
					<div>
						<div className="flex gap-3 items-center border-b px-6 py-4">
                            <button onClick={handleBackToList}>
								<svg
									class="rotate-180"
									width="19"
									height="14"
									viewBox="0 0 19 14"
									fill="none"
									xmlns="http://www.w3.org/2000/svg"
								>
									<path
										d="M12.7894 0.253559C12.7293 0.178316 12.6548 0.116973 12.5707 0.0734407C12.4866 0.0299081 12.3947 0.00514119 12.3009 0.000718845C12.2071 -0.0037035 12.1134 0.0123158 12.0259 0.0477555C11.9384 0.0831951 11.8589 0.137278 11.7925 0.206555C11.7261 0.275832 11.6743 0.358784 11.6403 0.450123C11.6064 0.541461 11.591 0.639182 11.5953 0.737055C11.5995 0.834928 11.6232 0.930806 11.6649 1.01858C11.7067 1.10635 11.7654 1.18408 11.8376 1.24683L16.6939 6.32046H0.641815C0.468532 6.33005 0.305376 6.40864 0.185969 6.54003C0.0665617 6.67142 0 6.8456 0 7.02667C0 7.20775 0.0665617 7.38193 0.185969 7.51332C0.305376 7.64471 0.468532 7.7233 0.641815 7.73289H16.6939L11.8299 12.8006C11.7075 12.9343 11.6392 13.1122 11.6392 13.2972C11.6392 13.4822 11.7075 13.66 11.8299 13.7938C11.8924 13.8592 11.9665 13.911 12.0482 13.9464C12.1299 13.9818 12.2174 14 12.3059 14C12.3943 14 12.4818 13.9818 12.5635 13.9464C12.6452 13.911 12.7194 13.8592 12.7818 13.7938L18.7975 7.51636C18.8616 7.45204 18.9125 7.37491 18.9473 7.28958C18.9821 7.20424 19 7.11246 19 7.01972C19 6.92698 18.9821 6.8352 18.9473 6.74987C18.9125 6.66453 18.8616 6.5874 18.7975 6.52309L12.7894 0.253559Z"
										fill="currentColor"
									></path>
								</svg>
							</button>
                            
							<Title
								title={
									mode === 'add'
										? __('Add New Attribute', 'easycommerce')
										: // translators: %s: attribute name.
										sprintf(__('Edit Attribute: %s', 'easycommerce'), editingAttribute?.name || '')
								}
							/>
							<button onClick={onClose} className="ml-auto">
								<svg
									xmlns="http://www.w3.org/2000/svg"
									width="10"
									height="10"
									viewBox="0 0 10 10"
									fill="none"
								>
									<path
										fillRule="evenodd"
										clipRule="evenodd"
										d="M0.260418 0.260418C0.607642 -0.086806 1.17015 -0.086806 1.51731 0.260418L5 3.7431L8.48269 0.260418C8.82991 -0.086806 9.39241 -0.086806 9.73958 0.260418C10.0868 0.607642 10.0868 1.17015 9.73958 1.51731L6.2569 5L9.73958 8.48269C10.0868 8.82991 10.0868 9.39241 9.73958 9.73958C9.39236 10.0868 8.82985 10.0868 8.48269 9.73958L5 6.2569L1.51731 9.73958C1.17009 10.0868 0.607587 10.0868 0.260418 9.73958C-0.0867505 9.39236 -0.086806 8.82985 0.260418 8.48269L3.7431 5L0.260418 1.51731C-0.086806 1.17009 -0.086806 0.607587 0.260418 0.260418Z"
										fill="#7F7F98"
									/>
								</svg>
							</button>
						</div>
						<div className="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
							<AttributeForm
								editingAttribute={editingAttribute}
								attributes={attributes}
								attributesData={attributesData}
								setAttributesData={setAttributesData}
								field={field}
								setField={setField}
								onAttributeCreated={handleAttributeCreated}
								onAttributeUpdated={handleAttributeUpdated}
								onCancel={handleBackToList}
								noPadding={true}
							/>
						</div>
					</div>
				)}
			</div>
		</div>
	);
};

export default AttributeModal;
