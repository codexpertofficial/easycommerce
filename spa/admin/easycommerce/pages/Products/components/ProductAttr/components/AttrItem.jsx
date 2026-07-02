import React, { useEffect, useState, useRef, useCallback } from 'react';
import { createPortal } from 'react-dom';
import { __ } from '@wordpress/i18n';
import MultiSelect from '../../common/MultiSelect'

/**
 * @typedef {Object} AttrItemProps
 * @property {Object} item - The attribute item object.
 * @property {Array<Object>} globalAttributes - List of all available attribute types.
 * @property {Array<Object>} selectedAttrs - List of currently selected attributes.
 * @property {Function} updateAttr - Callback when attribute data updates.
 * @property {Function} handleDelete - Callback when deleting the attribute item.
 */

/**
 * Attribute Item component used to select an attribute and its values.
 *
 * @param {AttrItemProps} props - Component props.
 */
const AttrItem = ({ item, globalAttributes, selectedAttrs, updateAttr, handleDelete }) => {
	const [isDropdownOpen, setIsDropdownOpen] = useState(false);
	const [dropdownStyle, setDropdownStyle] = useState({});
	const inputWrapperRef = useRef(null);

	const [attr, setAttr] = useState(globalAttributes.find((a) => a.id === item.id) || null);
	const [values, setValues] = useState(attr?.options?.find((o) => o.id === item.values.id) || []);

	const suggestions = Object.values(
		globalAttributes.filter((attr) => String(attr.id) == String(item.id))[0]?.options || {}
	);

	useEffect(() => {
		if (!globalAttributes?.length || !item) return;

		const filteredAttr = globalAttributes.find((attr) => String(attr.id) == String(item.id));
		if (!filteredAttr) {
			console.warn(`Attribute with ID ${item.id} not found in global attributes.`);
			return
		};

		const matchedValues = filteredAttr.options?.filter((opt) =>
			item.values?.some(val => String(val.id) == String(opt.id) && String(val.attribute_id) == String(opt.attribute_id))
		) || [];

		setAttr({
			id: filteredAttr?.id || null,
			values: matchedValues.map((v) => {
				return {
					id: v.id || null,
					attribute_id: filteredAttr?.id || null
				};
			}),
		});

		setValues(matchedValues);
	}, [globalAttributes]);

	useEffect(() => {
		const filteredAttr = globalAttributes.find((attr) => String(attr.id) == String(item.id));
		if (!filteredAttr) return;

		setAttr(filteredAttr)
	}, [item]);

	useEffect(() => {
		if (!attr || !attr.options) return;
		
		const filteredValues = attr.options.filter((opt) =>
			item.values?.some(val => String(val.id) == String(opt.id) && String(val.attribute_id) == String(opt.attribute_id))
		) || [];

		setValues(filteredValues);
	}, [attr])

	/**
	 * Update parent component when attribute or values change.
	 */
	useEffect(() => {
		if (!attr) return;
		
		updateAttr(item, {
			id: attr?.id || null,
			values: values.map((v) => {
				return {
					id: v.id || null,
					attribute_id: attr?.id || null
				};
			}),
		});
	}, [attr, values]);

	/**
	 * Update dropdown position when it opens.
	 */
	useEffect(() => {
		if (isDropdownOpen && inputWrapperRef.current) {
			const rect = inputWrapperRef.current.getBoundingClientRect();
			setDropdownStyle({
				position: 'absolute',
				top: `${rect.bottom + window.scrollY + 10}px`,
				left: `${rect.left + window.scrollX}px`,
				minWidth: '160px',
				zIndex: 1000,
			});
		}
	}, [isDropdownOpen]);

	/**
	 * Adds a value to the selected values list.
	 *
	 * @param {string} val - The selected attribute value.
	 */
	const handleAddAttr = useCallback((val) => {
		setValues((prev) => Array.from(new Set([...prev, val])));
	}, []);

	/**
	 * Removes a value from the selected values list by index.
	 *
	 * @param {number} index - Index of the value to remove.
	 */
	const handleRemoveAttr = useCallback((index) => {
		setValues((prev) => prev.filter((_, i) => i !== index));
	}, []);

	/**
	 * Handles selecting a new attribute from the dropdown.
	 *
	 * @param {Object} attrItem - The selected attribute item.
	 */
	const handleAttrSelect = useCallback((attrItem) => {
		setAttr(attrItem);
		setValues([]);
		setIsDropdownOpen(false);
	}, []);

	/**
	 * Toggles the attribute dropdown.
	 *
	 * @param {React.MouseEvent} e
	 */
	const toggleDropdown = useCallback((e) => {
		e.preventDefault();
		setIsDropdownOpen((prev) => !prev);
	}, []);

	/**
	 * Closes the attribute dropdown.
	 */
	const closeDropdown = useCallback(() => {
		setIsDropdownOpen(false);
	}, []);

	/**
	 * Renders the dropdown menu for selecting an attribute.
	 *
	 * @returns {React.ReactNode}
	 */
	const renderAttrDropdown = () => {
		const existingIDs = selectedAttrs.map(attr => String(attr.id));
		return (
			<ul className="border bg-white border-ec-border rounded-lg shadow-2xl max-h-[500px] overflow-y-auto" style={dropdownStyle}>
				{globalAttributes.map((attrItem, index) => {
					const isDisabled = existingIDs.includes(String(attrItem.id)) && String(attrItem.id) !== String(item.id);
					return (
						<li
							key={index}
							className={`px-4 py-3 text-[14px] text-ec-body font-normal leading-[26px] hover:bg-[#F8F8F8] cursor-pointer rounded-[4px] m-0 ${isDisabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#F8F8F8]'}`}
							onMouseDown={() => !isDisabled && handleAttrSelect(attrItem)}
						>
							{attrItem.name}
							{isDisabled && (
								<span className="ml-2 text-xs text-gray-500">({__('Selected', 'easycommerce')})</span>
							)}
						</li>
					);
				})}
			</ul>
		)
	};

	if (!attr) {
		return (
			<div className="flex gap-8 items-center h-max min-h-12">
				<div className="flex grow rounded-lg h-max">
					<span className="text-ec-body">{__('Loading attribute...', 'easycommerce')}</span>
				</div>
			</div>
		);
	}

	return (
		<div className="flex gap-8 items-center h-max min-h-12" ref={inputWrapperRef}>
			<div className="flex grow rounded-lg h-max">
				<div className="relative bg-[#F9F9F9] border border-ec-table-stock border-r-0 rounded-l-lg">
					<button
						type="button"
						className="w-[140px] rounded-l-lg h-12 p-4 flex items-center justify-between bg-[#F9F9F9] border-r border-ec-table-stock text-ec-title text-base"
						onClick={toggleDropdown}
						onBlur={closeDropdown}
					>
						{attr?.name || __('Loading...', 'easycommerce')}
						{globalAttributes.length > 0 && (
							<svg xmlns="http://www.w3.org/2000/svg" width="11" height="6" viewBox="0 0 11 6" fill="none">
								<path
									d="M9.87109 1.71094L5.71484 5.62109C5.56901 5.7487 5.41406 5.8125 5.25 5.8125C5.08594 5.8125 4.9401 5.7487 4.8125 5.62109L0.65625 1.71094C0.382812 1.40104 0.373698 1.09115 0.628906 0.78125C0.920573 0.507812 1.23047 0.498698 1.55859 0.753906L5.25 4.25391L8.96875 0.753906C9.27865 0.498698 9.57943 0.498698 9.87109 0.753906C10.1263 1.08203 10.1263 1.40104 9.87109 1.71094Z"
									fill="#3C3C42"
								/>
							</svg>
						)}
					</button>
					{isDropdownOpen &&
						createPortal(renderAttrDropdown(), document.body)}
				</div>

				<MultiSelect
					options={suggestions}
					selectedValues={values || []}
					placeholder={__('Select Options', 'easycommerce')}
					handleSelect={handleAddAttr}
					handleRemove={handleRemoveAttr}
				/>
			</div>

			<button
				type="button"
				className="group w-6 h-6 flex items-center justify-center rounded-full bg-[#7351FD08] border border-ec-table-stock hover:bg-ec-red hover:border-ec-red transition duration-300"
				onClick={() => handleDelete(item.id)}
			>
				<svg xmlns="http://www.w3.org/2000/svg" width="12" height="14" viewBox="0 0 12 14" fill="none">
					<path
						className="fill-ec-body group-hover:fill-white transition duration-300"
						fillRule="evenodd"
						clipRule="evenodd"
						d="M10.6942 4.84412H1.30866L2.05026 12.1333C2.1106 12.7288 2.60298 13.1754 3.19796 13.1754H8.80242C9.3986 13.1754 9.88978 12.7288 9.95012 12.1333L10.6917 4.84412H10.6942ZM11.1842 3.61669C11.1842 3.06434 10.7346 2.61166 10.1861 2.61166H1.81858C1.27008 2.61166 0.820541 3.06436 0.820541 3.61669V4.01955H11.1842L11.1842 3.61669ZM3.56242 1.78891V1.74394C3.56242 0.783863 4.34083 0 5.29423 0H6.70742C7.66081 0 8.43922 0.783863 8.43922 1.74394V1.78891H10.1849C11.1842 1.78891 12 2.61043 12 3.61669V4.43154C12 4.65879 11.816 4.84351 11.5909 4.84351H11.5118L10.7612 12.2166C10.6574 13.2325 9.81444 13.9994 8.80077 13.9994L3.19923 14C2.1861 14 1.34246 13.2332 1.23881 12.2172L0.488156 4.84412H0.409109C0.183438 4.84412 0 4.65879 0 4.43215V3.6173C0 2.61104 0.815807 1.78951 1.81507 1.78951H3.56078L3.56242 1.78891ZM7.62153 1.78891V1.74394C7.62153 1.23838 7.20879 0.822752 6.70675 0.822752H5.29356C4.79152 0.822752 4.37879 1.23838 4.37879 1.74394V1.78891H7.62153ZM4.06554 10.4241C4.06554 10.6514 3.8815 10.8361 3.65643 10.8361C3.43076 10.8361 3.24733 10.6508 3.24733 10.4241V7.56035C3.24733 7.3331 3.43137 7.14837 3.65643 7.14837C3.8821 7.14837 4.06554 7.3337 4.06554 7.56035V10.4241ZM7.9243 7.56035C7.9243 7.3331 8.10834 7.14837 8.33341 7.14837C8.55908 7.14837 8.74252 7.3337 8.74252 7.56035V10.4241C8.74252 10.6514 8.55848 10.8361 8.33341 10.8361C8.10774 10.8361 7.9243 10.6508 7.9243 10.4241V7.56035ZM6.40431 11.1595C6.40431 11.3867 6.22027 11.5714 5.9952 11.5714C5.76953 11.5714 5.58609 11.3861 5.58609 11.1595V6.82395C5.58609 6.59669 5.77013 6.41197 5.9952 6.41197C6.22087 6.41197 6.40431 6.5973 6.40431 6.82395V11.1595Z"
					/>
				</svg>
			</button>
		</div>
	);
};

export default AttrItem;