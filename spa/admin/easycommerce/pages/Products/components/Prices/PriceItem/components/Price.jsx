import { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { toast } from 'react-toastify';

import Dropdown from '../../../../../../../common/components/inputs/Dropdown';
import { Tooltip } from 'react-tooltip';

const Price = ({ priceItem, setPriceItem, id }) => {
    // Tax & stock settings
	const [taxClass, setTaxClass] = useState(priceItem.meta.tax_class);

	// Tax classes
	const [isLoading, setIsLoading] = useState(true);
	const [taxClasses, setTaxClasses] = useState([]);

	useEffect(() => {
		easycommerce_modal(true);

		// Fetch tax class options
		fetch(`${EASYCOMMERCE.rest_base}/taxes`, {
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': EASYCOMMERCE.nonce,
			},
		})
			.then((res) => res.json())
			.then((data) => {
				setIsLoading(false);
				easycommerce_modal(false);
				if (data.success && data.data.classes) {
					/**
					 * Transforms an array of tax class objects into an array of option objects.
					 *
					 * @typedef {Object} TaxClass
					 * @property {string} name - The display name of the tax class.
					 * @property {string|number} id - The unique identifier of the tax class.
					 *
					 * @typedef {Object} Option
					 * @property {string} label - The label for the option, derived from the tax class name.
					 * @property {string|number} value - The value for the option, derived from the tax class id.
					 *
					 * @type {Option[]}
					 * An array of option objects with `label` and `value` properties, suitable for use in select inputs.
					 */
					const formatted = data.data.classes.map((tax) => ({
						label: tax.name,
						value: tax.id,
					}));
					setTaxClasses(formatted);
				}
			});

		return () => {
			setTaxClasses([]);
		};
	}, []);

	return (
		<div className="grid ec-db-lg:grid-cols-3 grid-cols-2 gap-4 mt-3">
			<div>
				<h5 className="text-base font-normal text-[#282828] font-inter mb-[6px]">
					{__('Regular Price', 'easycommerce')}
				</h5>
				<div className="h-ec-input rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus-within:border-ec-primary focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out overflow-hidden flex ">
					<div className="h-full w-[50px] flex items-center justify-center border-r border-ec-table-stock bg-[#F9F9F9]">
						<span className="text-gray-400 text-base pointer-events-none">
							{EASYCOMMERCE.currency_symbol}
						</span>
					</div>
					<input
						type="number"
						name={`regular-price-` + id}
						className="w-[calc(100%_-_50px)] h-full border-none outline-none shadow-none"
						placeholder={__('Enter Price', 'easycommerce')}
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
				<h5 className="flex gap-3 text-base text-ec-title font-inter mb-[6px]">
					{__('Sale Price', 'easycommerce')}
					<Tooltip text={__('If there is no sale going on for this variation, simply leave it blank.', 'easycommerce')} />
				</h5>
				<div className="h-ec-input rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus-within:border-ec-primary focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out overflow-hidden flex ">
					<div className="h-full w-[50px] flex items-center justify-center border-r border-ec-table-stock bg-[#F9F9F9]">
						<span className="text-gray-400 text-base pointer-events-none">
							{EASYCOMMERCE.currency_symbol}
						</span>
					</div>
					<input
						type="number"
						name={`sale-price-` + id}
						className="w-[calc(100%_-_50px)] h-full border-none outline-none shadow-none"
						placeholder={__('Enter Price', 'easycommerce')}
						value={priceItem.sale_price || null}
						onKeyDown={(e) => {
							if (e.key === '-' || e.key === 'e' || e.key === 'E') {
								e.preventDefault();
							}
						}}
						onChange={(e) => {
							const salePrice = parseFloat(e.target.value);
							const regularPrice = parseFloat(priceItem.regular_price);
							if (salePrice > regularPrice) {
								toast.error(
									__(
										'Sale price should be less than regular price.',
										'easycommerce'
									)
								);
								return;
							}
							setPriceItem((prev) => ({
								...prev,
								sale_price: e.target.value,
							}));
						}}
						min={0}
					/>
				</div>
			</div>

			{/* <div>
				<h5 className="flex gap-3 text-base text-ec-title font-inter mb-[6px]">
					{__('Tax Classes', 'easycommerce')}
					<Tooltip text="If a tax class applies to this variation, select it here." />
				</h5>
				{isLoading ? (
					<div className="flex flex-col gap-8 m-[15px] mb-10 rounded-2xl">
						Loading...
					</div>
				) : taxClasses && taxClasses.length > 0 ? (
					<div className="h-ec-input">
						<Dropdown
							placeholder={
								priceItem.meta?.tax_class ||
								__('Select Tax Class', 'easycommerce')
							}
							options={taxClasses}
							setStatus={setTaxClass}
							onChange={(selected) => {
								setTaxClass(selected.value);
								setPriceItem((prev) => ({
									...prev,
									meta: { ...prev.meta, tax_class: selected.value },
								}));
							}}
							value={taxClass || ''}
						/>
					</div>
				) : (
					__('No tax classes found', 'easycommerce')
				)}
			</div> */}
		</div>
	);
};

export default Price;
