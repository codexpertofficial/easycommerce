import React, { useState, useEffect, useRef } from 'react';
import { motion } from 'framer-motion';
import { __ } from '@wordpress/i18n';
import PanelTitle from '../common/PanelTitle';
import PriceItem from './PriceItem';
import Popup from './Popup';
import { applyFilters } from '@wordpress/hooks';
import { Tooltip } from 'react-tooltip';

/**
 * Prices component for managing and displaying product pricing, variants, and stock information.
 *
 * @component
 * @param {Object} props - Component props.
 * @param {string} props.productTitle - The title of the product.
 * @param {Array<Object>} props.globalAttributes - Array of all available product attributes.
 * @param {Array<Object>} props.productAttributes - Array of selected product attributes for the current product.
 * @param {Array<Object>} [props.prevData] - Previously saved price items (variants/prices).
 *
 * @returns {JSX.Element} The rendered Prices component.
 */
const Prices = ({ productTitle, productAttributes, prevData, globalAttributes }) => {
	const [isOpen, setIsOpen] = useState(true);
	const [isPopupOpen, setIsPopupOpen] = useState(false);

	const pricingStructure = {
		name: '',
		type: 'digital',
		status: 'in_stock',
		regular_price: 0,
		sale_price: null,
		sku: '',
		stock_quantity: null,
		stock_limit: null,
		attributes: [],
		meta: {
			is_managed_stock: false,
			tax_class: '',
			thumbnail: {
				id: '',
				url: '',
			},
			width: { value: null, unit: null },
			height: { value: null, unit: null },
			weight: { value: null, unit: null },
			length: { value: null, unit: null },
		},
		downloads: [],
	};

	const [priceItems, setPriceItems] = useState(
		prevData && prevData.length > 0 ? prevData : [pricingStructure]
	);

	let isInitialMount = true;
	const titleRef = useRef();

	/**
	 * Adds a new price item using the base pricing structure.
	 */
	const handleAddPriceItem = () => {
		setPriceItems((prev) => [
			...prev,
			{ ...pricingStructure, id: Date.now() + prev.length },
		]);
	};

	/**
	 * Duplicates a given price item and inserts it after the original.
	 *
	 * @param {Object} itemToDuplicate - The price item to duplicate.
	 */
	const handleDuplicatePriceItem = (itemToDuplicate) => {
		setPriceItems((prev) => {
			const newItems = [...prev];
			const index = newItems.findIndex(
				(item) => item.id === itemToDuplicate.id
			);
			if (index !== -1) {
				const duplicatedItem = {
					...newItems[index],
					id: Date.now() + index,
				};
				newItems.splice(index + 1, 0, duplicatedItem);
			}
			return newItems;
		});
	};

	/**
	 * Deletes a price item from the list.
	 *
	 * @param {Object} itemToRemove - The price item to remove.
	 */
	const handleDeletePriceItem = (itemToRemove) => {
		setPriceItems((prev) => prev.filter((item) => item.id !== itemToRemove.id));
	};

	/**
	 * Automatically generates variant price items based on product attributes.
	 * Each combination of attribute values creates a new price item.
	 */
	const autoGenerateVariants = () => {
		if (!productAttributes?.length) {
			return;
		}

		// Generate all combinations of attribute values
		const generatedVariants = productAttributes.reduce(
			(acc, attr) => {
				if (!attr.values || attr.values.length === 0) {
					return acc;
				}
				const newVariants = [];
				acc.flatMap((prevCombo) =>
					attr.values.map((option) =>
						newVariants.push({
							...prevCombo,
							[option.id]: option,
						})
					)
				);
				return newVariants;
			},
			[{}]
		);

		// Convert each combination into a structured price item
		const items = generatedVariants.map((variant, idx) => {
			const attributes = Object.values(variant).map((selectedValue) => {
				const parentAttr = globalAttributes.find((globalAttr) => globalAttr.id === selectedValue.attribute_id);
				const value = parentAttr?.options.find((option) => option.id === selectedValue.id);

				return {
					attribute_id: selectedValue.attribute_id,
					attribute_slug: parentAttr?.slug || '',
					value_id: selectedValue.id || '',
					value_slug: value?.slug || '',
				};
			});

			const attributeNames = attributes.map((attr) => attr.value_slug);

			return {
				...JSON.parse(JSON.stringify(pricingStructure)),
				id: Date.now() + idx,
				attributes,
				name: attributeNames.join('/'),
				sku: (productTitle + attributeNames.join()).replace(/[^a-zA-Z0-9]+/g, '').toUpperCase(),
			};
		});

		setPriceItems(() => [...items]);
	};

	/**
	 * Ensures that if there are no product attributes, only one price item remains.
	 *
	 * This prevents multiple price items when variants cannot be generated.
	 */
	useEffect(() => {
		if (isInitialMount) {
			isInitialMount = false;
			return;
		}

		if (productAttributes.length === 0 && priceItems.length > 1) {
			setPriceItems([priceItems[0]]);
		}
	}, [productAttributes]);

	/**
	 * Updates a specific price item by merging new values.
	 *
	 * @param {Object} item - The item to update.
	 * @returns {Function} A setter function that takes the new item properties.
	 */
	const setItem = (item) => (newItem) => {
		setPriceItems((prev) =>
			prev.map((priceItem) =>
				priceItem.id === item.id ? { ...priceItem, ...newItem } : priceItem
			)
		);
	};

	/**
	 * Syncs priceItems with WordPress filters whenever it changes.
	 */
	useEffect(() => {
		applyFilters('easycommerce_price_items', priceItems);
	}, [priceItems]);

	return (
		<div>
			<div class="bg-white rounded-xl border-ec-table-stock border border-solid">
				<div class="py-[14px] px-6 flex items-center justify-between border-b border-ec-table-stock border-solid rounded-t-xl">
					<PanelTitle
						title={__('Pricing', 'easycommerce')}
						notice={__(
							'Set product type, price, sale price, stock count, and dimensions of the product.',
							'easycommerce'
						)}
					/>
					<div className="panel-actions">
						{productAttributes.length === 0 && (
							<Tooltip
								id="auto-generate-variant"
								style={{
									backgroundColor: '#7351fd',
									color: '#fff',
									fontSize: '14px',
									width: '210px',
									textAlign: 'center',
								}}
							/>
						)}

						{productAttributes.length > 0 && (
							<button
								type="button"
								data-tooltip-id="auto-generate-variant"
								data-tooltip-content={__('Add attributes to enable variant generation', 'easycommerce')}
								class={
									`text-ec-body py-2 px-0 font-inter text-sm w-max duration-300 focus:shadow-none border-0 border-b-[1px] border-solid rounded-none rounded-tr-xl` +
									(productAttributes.length > 0 &&
										'focus:text-ec-primary hover:text-ec-primary hover:border-ec-primary')
								}
								disabled={productAttributes.length === 0}
								style={{
									cursor: productAttributes.length === 0 ? 'not-allowed' : 'pointer',
									opacity: productAttributes.length === 0 ? 0.5 : 1,
									borderColor: '#3c3c42',
								}}
								onClick={() => {
									if (priceItems.length === 0) {
										if (productAttributes.length > 0) {
											autoGenerateVariants();
										}
									} else {
										if (productAttributes.length > 0) {
											setIsPopupOpen(true);
										}
									}
								}}
							>
								<span>{__('Auto Generate Variants', 'easycommerce')}</span>
							</button>
						)}

						{productAttributes.length === 0 && (
							<span
								className="text-ec-body/90 font-inter py-2 text-sm self-center border-b-[1px] border-solid border-[#3c3c42]/90 cursor-help"
								data-tooltip-id="auto-generate-variant"
								data-tooltip-content={__('Add attributes above to add multiple price options.', 'easycommerce')}
							>
								{__('Need multiple prices?', 'easycommerce')}
							</span>
						)}

						<button
							className="panel-collapse"
							type="button"
							onClick={() => setIsOpen(!isOpen)}
						>
							<svg
								className={`transition-transform duration-300 ${
									isOpen ? '' : 'rotate-180'
								}`}
								xmlns="http://www.w3.org/2000/svg"
								width="11"
								height="6"
								viewBox="0 0 11 6"
								fill="none"
							>
								<path
									d="M1.12891 4.28906L5.28516 0.378906C5.43099 0.251302 5.58594 0.1875 5.75 0.1875C5.91406 0.1875 6.0599 0.251302 6.1875 0.378906L10.3438 4.28906C10.6172 4.59896 10.6263 4.90885 10.3711 5.21875C10.0794 5.49219 9.76953 5.5013 9.44141 5.24609L5.75 1.74609L2.03125 5.24609C1.72135 5.5013 1.42057 5.5013 1.12891 5.24609C0.873698 4.91797 0.873698 4.59896 1.12891 4.28906Z"
									fill="#3C3C42"
								/>
							</svg>
						</button>
					</div>
				</div>
				<motion.div
					initial={false}
					animate={{
						height: isOpen ? 'auto' : 0,
						opacity: isOpen ? 1 : 0,
						overflow: 'hidden',
						transition: { duration: 0.3, ease: 'easeInOut' },
					}}
					style={{
						visibility: isOpen ? 'visible' : 'hidden',
					}}
				>
					<div class="p-6 duration-300">
						<div class="flex flex-col gap-3 mb-8">
							{priceItems.length > 0 ? (
								priceItems.map((item, index) => {
									return (
										<PriceItem
											key={item.id}
											item={item}
											index={index}
											setItem={setItem(item)}
											id={item.id}
											productTitle={productTitle}
											productAttributes={productAttributes}
											globalAttributes={globalAttributes}
											handleDelete={() => handleDeletePriceItem(item)}
											handleDuplicate={() => handleDuplicatePriceItem(item)}
											prevData={prevData?.find((dataItem) => dataItem.id === item.id)}
										/>
									);
								})
							) : (
								<span className="text-ec-body font-inter text-[16px] leading-[20px]">
									{__('No prices added yet', 'easycommerce')}
								</span>
							)}
						</div>
						<div class="flex items-center justify-end gap-5">
							<button
								type="button"
								class={
									`easycommerce-outline-button group flex gap-[6px] items-center ` +
									(productAttributes?.length === 0 &&
										priceItems.length > 0 &&
										'bg-gray-400 text-white border-gray-400')
								}
								onClick={() =>
									(productAttributes?.length > 0 || priceItems.length === 0) &&
									handleAddPriceItem()
								}
								style={{
									opacity:
										productAttributes?.length === 0 && priceItems.length > 0
											? 0.5
											: 1,
									cursor:
										productAttributes?.length === 0 && priceItems.length > 0
											? 'not-allowed'
											: 'pointer',
								}}
							>
								<svg
									class={
										productAttributes?.length === 0 && priceItems.length > 0
											? 'fill-white'
											: 'fill-ec-primary group-hover:fill-white duration-300'
									}
									xmlns="http://www.w3.org/2000/svg"
									width="13"
									height="13"
									viewBox="0 0 13 13"
									fill="none"
								>
									<path d="M6.5 0C6.1119 0 5.7973 0.314618 5.7973 0.702703V5.79732H0.702703C0.314618 5.79732 0 6.11192 0 6.50002C0 6.88812 0.314618 7.20272 0.702703 7.20272H5.7973V12.2973C5.7973 12.6854 6.1119 13 6.5 13C6.8881 13 7.2027 12.6854 7.2027 12.2973V7.20272H12.2973C12.6854 7.20272 13 6.88812 13 6.50002C13 6.11192 12.6854 5.79732 12.2973 5.79732H7.2027V0.702703C7.2027 0.314618 6.8881 0 6.5 0Z" />
								</svg>
								{__('Add New Price', 'easycommerce')}
							</button>
						</div>

						{isPopupOpen &&
							productAttributes.length > 0 &&
							priceItems.length > 0 && (
								<Popup
									onClose={() => setIsPopupOpen(false)}
									onConfirm={() => {
										autoGenerateVariants();
										setIsPopupOpen(false);
										titleRef.current?.changeTitle();
									}}
									itemName={productTitle}
								/>
							)}
					</div>
				</motion.div>
			</div>
		</div>
	);
};

export default Prices;
