import React, { useEffect, useState, useRef } from 'react';
import { motion } from 'framer-motion';
import { __ } from '@wordpress/i18n';

// Slot
import SlotField from '../../../../../../common/components/SlotField';
import NumberField from '../../../../../../common/components/inputs/NumberField';
import { Tooltip } from 'react-tooltip';

// components
import Thumbnail from './components/Thumbnail';
import Title from './components/Title';
import SKU from './components/SKU';
import ActionButton from './components/ActionButton';
import ProductType from './components/Type';
import Attributes from './components/Attributes';
import Price from './components/Price';
import ManageProfit from './components/ManageProfit';
import Stock from './components/Stock';
import Dimensions from './components/Dimensions';
import FileUpload from './components/FileUpload';
import ProModal from '../../../../../../common/ProModal';
import InstallationModal from '../../../../../../common/InstallationModal';

/**
 * Generates a random SKU (Stock Keeping Unit) string consisting of 10 uppercase alphanumeric characters.
 *
 * @returns {string} A randomly generated SKU.
 */
const generateRandomSKU = () => {
	const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	let result = '';
	for (let i = 0; i < 10; i++) {
		result += chars.charAt(Math.floor(Math.random() * chars.length));
	}
	return result;
};

/**
 * PriceItem component for managing and editing a single product pricing option.
 *
 * Handles display and editing of price, SKU, attributes, downloads, and other
 * product-specific fields. Supports both collapsed and expanded views, and
 * synchronizes local state with parent via setItem.
 *
 * @component
 * @param {Object} props
 * @param {Object} props.item - The initial price item data.
 * @param {Function} props.setItem - Callback to update the price item in the parent component.
 * @param {string} [props.productTitle=''] - The title of the parent product.
 * @param {number} props.index - The index of this price item in the list.
 * @param {string|number} props.id - Unique identifier for this price item.
 * @param {Array<Object>} props.productAttributes - List of all available product attributes.
 * @param {Array<Object>} props.itemAttributes - Attributes specific to this price item.
 * @param {Function} props.handleDuplicate - Handler to duplicate this price item.
 * @param {Function} props.handleDelete - Handler to delete this price item.
 *
 * @returns {JSX.Element} The rendered PriceItem component.
 */
const PriceItem = ({
	item,
	setItem,
	productTitle = '',
	index,
	id,
	productAttributes,
	globalAttributes,
	handleDuplicate,
	handleDelete,
	prevData
}) => {
	const [expanded, setExpanded] = useState(false);
	const [isVariableProduct, setIsVariableProduct] = useState(true);
	const initialSKU = item.sku || generateRandomSKU();

	const [priceItem, setPriceItem] = useState({
		...item,
		sku: initialSKU,
		downloads: item.downloads || [],
	});

	const [downloads, setDownloads] = useState([]);
	const attrRef = useRef();
	const [showPopup, setShowPopup] = useState(false);
	const [showInstallPopup, setShowInstallPopup] = useState(false);
	const [addonToInstall, setAddonToInstall] = useState(null);
	const [addonName, setAddonName] = useState('');
	const [addonDescription, setAddonDescription] = useState('');

	/**
	 * Handles click on a Pro feature, showing appropriate modal based on license status.
	 *
	 * @param {Object} addon - The addon feature being accessed.
	 */
	const handleProClick = (addon) => {
		if (EASYCOMMERCE.pro.licensed) {
			setShowInstallPopup(true);
			setAddonToInstall(addon);
		} else {
			setAddonName(addon.name);
			setAddonDescription(addon.description);
			setShowPopup(true);
		}
	};

	/**
	 * Determine if product is variable based on presence of product attributes.
	 * Sets `isVariableProduct` and `expanded` state accordingly.
	 */
	useEffect(() => {
		const isVariable = Array.isArray(productAttributes) && productAttributes.length > 0;
		setIsVariableProduct(isVariable);
	}, [productAttributes]);

	/**
	 * Push local `priceItem` changes back up to parent.
	 * Relies on the `setItem` function passed from the parent component.
	 */
	useEffect(() => {
		setItem(priceItem);
	}, [priceItem]);

	/**
	 * Syncs local `downloads` array into `priceItem`.
	 * Ensures only the necessary fields (media_id, name) are persisted.
	 */
	useEffect(() => {
		setPriceItem((prev) => ({
			...prev,
			downloads: downloads.map((d) => ({
				media_id: d.media_id || '',
				name: d.name || '',
			})),
		}));
	}, [downloads]);

	return (
		<div className="easycommerce-price-item">
			{isVariableProduct && (
				<motion.div
					initial={false}
					animate={{
						opacity: expanded ? 0 : 1,
						transition: { duration: 0.3, ease: 'easeInOut' },
					}}
					style={{ display: expanded ? 'none' : 'block' }}
				>
					<div className="flex items-end gap-3">
						<div className="flex items-end gap-3">
							<Thumbnail
								priceItem={priceItem}
								index={index}
								setPriceItem={setPriceItem}
								width="42px"
								height="42px"
							/>

							<div className="grid grid-cols-3 gap-3 w-[calc(100%_-_62px)]">
								<div className="col-span-1">
									{index === 0 && (
										<label className="text-ec-title font-normal text-sm block mb-2">
											{__('Variation Name', 'easycommerce')}
										</label>
									)}
									<Title
										priceItem={priceItem}
										setPriceItem={setPriceItem}
										id={id}
										ref={attrRef}
									/>
								</div>

								<div className="col-span-1">
									{/* Currency icon overlay */}
									{index === 0 && (
										<label className="text-ec-title font-normal text-sm block mb-2">
											{__('Price', 'easycommerce')}
										</label>
									)}

									<div className="relative">
										<span className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none">
											{EASYCOMMERCE.currency_symbol}
										</span>
										<NumberField
											name={`price-${id}`}
											placeholder={__('Enter Price', 'easycommerce')}
											className="pl-8 w-full h-ec-input"
											value={priceItem.regular_price}
											min={0}
											onKeyDown={(e) => {
												if (e.key === '-' || e.key === 'e' || e.key === 'E') {
													e.preventDefault();
												}
											}}
											onChange={(e) => {
												const value = e.target.value;
												if (value < 0) return;
												setPriceItem((prev) => ({
													...prev,
													regular_price: value,
												}));
											}}
										/>
									</div>
								</div>

								<div>
									{index === 0 && (
										<label className="text-ec-title font-normal text-sm block mb-2">
											{__('SKU', 'easycommerce')}
										</label>
									)}
									<SKU
										priceItem={priceItem}
										setPriceItem={setPriceItem}
										id={id}
										productTitle={productTitle}
										index={index}
										prevData={prevData}
									/>
								</div>
							</div>
						</div>

						{/* Action buttons: Expand, Duplicate, Delete */}
						<div className="flex gap-3">
							{/* Expand Button */}
							<ActionButton onClick={() => setExpanded(true)} icon="plus" />

							{/* Duplicate Button */}
							<ActionButton onClick={handleDuplicate} icon="copy" />

							{/* Delete Button */}
							<ActionButton
								onClick={handleDelete}
								icon="trash"
								color="ec-red"
							/>
						</div>
					</div>
				</motion.div>
			)}

			<motion.div
				initial={false}
				animate={{
					height: expanded || !isVariableProduct ? 'auto' : 0,
					opacity: expanded || !isVariableProduct ? 1 : 0,
					overflow: 'hidden',
					transition: { duration: 0.3, ease: 'easeInOut' },
				}}
				style={{
					visibility: expanded || !isVariableProduct ? 'visible' : 'hidden',
				}}
			>
				<div
					className={`flex flex-col rounded-lg ${isVariableProduct ? '[&>div]:p-6 border-solid border-[1.5px] border-[#E0E0E0] my-3' : '[&>div]:py-6 [&>div.ec-product-type]:pt-0 [&>div:last-child]:pb-0'}`}
				>
					{/* Thumbnail, Title & SKU */}
					<div
						className="gap-3 border-b border-solid border-[#F3F3F3]"
						style={{
							display: isVariableProduct ? 'flex' : 'none',
						}}
					>
						<div className="flex gap-3 grow items-end">
							<Thumbnail
								priceItem={priceItem}
								index={index}
								setPriceItem={setPriceItem}
							/>
							<div className="grid grid-cols-9 gap-3 w-[calc(100%_-_62px)]">
								<div className="col-span-5">
									<label className="text-ec-title font-normal text-sm block mb-2">
										{__('Variation Name', 'easycommerce')}
									</label>
									<Title
										priceItem={priceItem}
										setPriceItem={setPriceItem}
										id={id}
										ref={attrRef}
									/>
								</div>

								<div className="col-span-4">
									<label className="text-ec-title font-normal text-sm block mb-2">
										{__('SKU', 'easycommerce')}
									</label>
									<SKU
										priceItem={priceItem}
										setPriceItem={setPriceItem}
										id={id}
										productTitle={productTitle}
										index={index}
										label="SKU"
										prevData={prevData}
									/>
								</div>
							</div>
						</div>

						{/* Action Buttons: Collapse, Duplicate, Delete */}
						<div className="flex gap-3 items-end">
							{/* Collapse */}
							<ActionButton
								icon="minus"
								onClick={() => setExpanded(false)}
								color="ec-primary"
							/>

							{/* Duplicate */}
							<ActionButton
								icon="copy"
								onClick={handleDuplicate}
								color="ec-primary"
							/>

							{/* Delete */}
							<ActionButton
								icon="trash"
								onClick={handleDelete}
								color="ec-red"
							/>
						</div>
					</div>

					{/* Product Type */}
					<div className="border-b border-solid border-[#F3F3F3] ec-product-type grid ec-db-lg:grid-cols-3 grid-cols-1 gap-4">
						<ProductType
							priceItem={priceItem}
							setPriceItem={setPriceItem}
							id={id}
						/>

						{!isVariableProduct && (
							<div>
								<h5 className="text-base font-normal text-[#282828] font-inter mb-[6px]">
									{__('SKU', 'easycommerce')}
								</h5>
								<SKU
									priceItem={priceItem}
									setPriceItem={setPriceItem}
									id={id}
									productTitle={productTitle}
									index={index}
									prevData={prevData}
								/>
							</div>
						)}
					</div>

					{/* Attributes */}
					{Array.isArray(productAttributes) &&
						productAttributes.filter(
							(attr) => Array.isArray(attr?.values) && attr.values.length > 0
						).length > 0 && (
							<div className="border-b border-solid border-[#F3F3F3]">
								<h4 className="flex gap-3 font-inter font-medium text-xl text-ec-title">
									{__('Attributes', 'easycommerce')}
									<Tooltip
										text={__(
											'Choose attributes that apply to this pricing option',
											'easycommerce',
										)}
									/>
								</h4>
								<Attributes
									productAttributes={productAttributes}
									globalAttributes={globalAttributes}
									priceItem={priceItem}
									setPriceItem={setPriceItem}
									attrChangeTrigger={() => {
										attrRef?.current?.changeTitle();
									}}
								/>
							</div>
						)}

					<div className="border-b border-solid border-[#F3F3F3]">
						<h4 className="font-inter font-medium text-xl text-[#282828]">
							{__('Price', 'easycommerce')}
						</h4>

						<Price priceItem={priceItem} setPriceItem={setPriceItem} id={id} />

						{priceItem.type === 'physical' && (
							<ManageProfit
								priceItem={priceItem}
								setPriceItem={setPriceItem}
								id={id}
							/>
						)}
					</div>

					<div className="ec-slot-field border-b border-solid border-[#F3F3F3]">
						{(!EASYCOMMERCE.pro?.licensed ||
							!EASYCOMMERCE.pro?.addons?.find(
								(addon) => addon.slug === 'easycommerce-subscriptions',
							)?.is_active) && (
							<>
								<div className="flex items-center gap-2">
									<input
										type="checkbox"
										id={`enable_subscription`}
										className="easycommerce-input-checkoutbox cursor-pointer"
										checked={false}
										onClick={() =>
											handleProClick({
												slug: 'easycommerce-subscriptions',
												name: 'Subscription Manager',
												description: __(
													'Add recurring billing to your store with the EasyCommerce Subscription Addon.',
													'easycommerce',
												),
											})
										}
									/>
									<label
										htmlFor={`enable_subscription`}
										className="text-[#282828] font-medium text-xl"
									>
										{__('Enable Subscription', 'easycommerce')}
									</label>
									
									<div className="w-5 h-5 flex items-center justify-center">
										<svg
											width="16"
											height="16"
											viewBox="0 0 16 16"
											fill="none"
											xmlns="http://www.w3.org/2000/svg"
										>
											<path
												d="M7.70796 2.17745C7.73673 2.12518 7.77901 2.0816 7.83037 2.05125C7.88173 2.02089 7.9403 2.00488 7.99996 2.00488C8.05962 2.00488 8.11818 2.02089 8.16955 2.05125C8.22091 2.0816 8.26318 2.12518 8.29196 2.17745L10.26 5.91345C10.3069 5.99995 10.3724 6.075 10.4518 6.13319C10.5311 6.19138 10.6224 6.23128 10.719 6.25002C10.8156 6.26876 10.9152 6.26587 11.0106 6.24156C11.106 6.21726 11.1948 6.17214 11.2706 6.10945L14.122 3.66678C14.1767 3.62226 14.2441 3.59626 14.3146 3.59251C14.3851 3.58877 14.4549 3.60748 14.514 3.64594C14.5732 3.68441 14.6186 3.74065 14.6437 3.80657C14.6689 3.87249 14.6725 3.94469 14.654 4.01278L12.7646 10.8434C12.7261 10.9832 12.643 11.1066 12.528 11.1949C12.413 11.2832 12.2723 11.3316 12.1273 11.3328H3.87329C3.72818 11.3318 3.58736 11.2834 3.47222 11.1951C3.35707 11.1068 3.27389 10.9833 3.23529 10.8434L1.34662 4.01345C1.32812 3.94536 1.3317 3.87316 1.35685 3.80724C1.382 3.74132 1.42741 3.68508 1.48656 3.64661C1.5457 3.60814 1.61553 3.58944 1.68598 3.59318C1.75644 3.59692 1.82389 3.62293 1.87862 3.66745L4.72929 6.11011C4.80516 6.17281 4.89396 6.21793 4.98933 6.24223C5.0847 6.26654 5.18427 6.26942 5.28089 6.25069C5.37751 6.23195 5.46878 6.19205 5.54815 6.13386C5.62752 6.07567 5.69303 6.00062 5.73996 5.91411L7.70796 2.17745Z"
												stroke="#D08700"
												stroke-width="1.33333"
												stroke-linecap="round"
												stroke-linejoin="round"
											/>
											<path
												d="M3.3335 14H12.6668"
												stroke="#D08700"
												stroke-width="1.33333"
												stroke-linecap="round"
												stroke-linejoin="round"
											/>
										</svg>
									</div>
								</div>
								<span class="text-[#717182] text-sm block mt-[8px]">
									{__(
										'Turn on subscriptions to sell products with recurring payments.',
										'easycommerce',
									)}
								</span>
							</>
						)}

						<SlotField name={`easycommerce-after-price-${index}`} item={item} />
					</div>

					{priceItem.type === 'physical' && (
						<>
							<div className="border-b border-solid border-[#F3F3F3]">
								<Stock
									id={id}
									priceItem={priceItem}
									setPriceItem={setPriceItem}
								/>
							</div>

							<div>
								<Dimensions priceItem={priceItem} setPriceItem={setPriceItem} />
							</div>
						</>
					)}

					{priceItem.type === 'digital' && (
						<>
							<div className="ec-slot-field border-b border-solid border-[#F3F3F3]">
								{(!EASYCOMMERCE.pro?.licensed ||
									!EASYCOMMERCE.pro?.addons?.find(
										(addon) => addon.slug === 'easycommerce-license',
									)?.is_active) && (
									<div className="transition-colors">
										<div className="flex items-center">
											<input
												type="checkbox"
												id={`generate_license`}
												className="easycommerce-input-checkoutbox cursor-pointer"
												checked={false}
												onClick={() =>
													handleProClick({
														slug: 'easycommerce-license',
														name: 'License Manager',
														description: __(
															'Configure your ecommerce store with licensing capability and sell digital products and manage their licenses.',
															'easycommerce',
														),
													})
												}
											/>
											<label
												htmlFor={`generate_license`}
												className="font-inter font-medium text-xl text-ec-title mr-2"
											>
												&nbsp; Generate License
											</label>
											<div className="w-5 h-5 ml-2 flex items-center justify-center">
												<svg
													width="16"
													height="16"
													viewBox="0 0 16 16"
													fill="none"
													xmlns="http://www.w3.org/2000/svg"
												>
													<path
														d="M7.70796 2.17745C7.73673 2.12518 7.77901 2.0816 7.83037 2.05125C7.88173 2.02089 7.9403 2.00488 7.99996 2.00488C8.05962 2.00488 8.11818 2.02089 8.16955 2.05125C8.22091 2.0816 8.26318 2.12518 8.29196 2.17745L10.26 5.91345C10.3069 5.99995 10.3724 6.075 10.4518 6.13319C10.5311 6.19138 10.6224 6.23128 10.719 6.25002C10.8156 6.26876 10.9152 6.26587 11.0106 6.24156C11.106 6.21726 11.1948 6.17214 11.2706 6.10945L14.122 3.66678C14.1767 3.62226 14.2441 3.59626 14.3146 3.59251C14.3851 3.58877 14.4549 3.60748 14.514 3.64594C14.5732 3.68441 14.6186 3.74065 14.6437 3.80657C14.6689 3.87249 14.6725 3.94469 14.654 4.01278L12.7646 10.8434C12.7261 10.9832 12.643 11.1066 12.528 11.1949C12.413 11.2832 12.2723 11.3316 12.1273 11.3328H3.87329C3.72818 11.3318 3.58736 11.2834 3.47222 11.1951C3.35707 11.1068 3.27389 10.9833 3.23529 10.8434L1.34662 4.01345C1.32812 3.94536 1.3317 3.87316 1.35685 3.80724C1.382 3.74132 1.42741 3.68508 1.48656 3.64661C1.5457 3.60814 1.61553 3.58944 1.68598 3.59318C1.75644 3.59692 1.82389 3.62293 1.87862 3.66745L4.72929 6.11011C4.80516 6.17281 4.89396 6.21793 4.98933 6.24223C5.0847 6.26654 5.18427 6.26942 5.28089 6.25069C5.37751 6.23195 5.46878 6.19205 5.54815 6.13386C5.62752 6.07567 5.69303 6.00062 5.73996 5.91411L7.70796 2.17745Z"
														stroke="#D08700"
														strokeWidth="1.33333"
														strokeLinecap="round"
														strokeLinejoin="round"
													/>
													<path
														d="M3.3335 14H12.6668"
														stroke="#D08700"
														strokeWidth="1.33333"
														strokeLinecap="round"
														strokeLinejoin="round"
													/>
												</svg>
											</div>
										</div>
										<span className="text-[#717182] text-sm block mt-[8px]">
											{__(
												'Turn on licensing to sell products that require license keys.',
												'easycommerce',
											)}
										</span>
									</div>
								)}

								<SlotField
									name={`easycommerce-digital-product-before-file-upload-${index}`}
									item={item}
								/>
							</div>

							<div>
								<FileUpload
									wpNonce={EASYCOMMERCE.nonce}
									setDownloads={setDownloads}
									prevFiles={priceItem.downloads.downloads || []}
								/>
							</div>
						</>
					)}
				</div>
				{showPopup && (
					<ProModal
						setShowPopup={setShowPopup}
						addonName={addonName}
						addonDescription={addonDescription}
					/>
				)}
				{showInstallPopup && (
					<InstallationModal
						addon={addonToInstall}
						setShowPopup={setShowInstallPopup}
					/>
				)}
			</motion.div>

			<input
				type="hidden"
				name="ec-variation-data"
				value={JSON.stringify(priceItem)}
			/>
		</div>
	);
};

export default PriceItem;
