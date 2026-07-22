import { useState } from 'react';
import { __ } from '@wordpress/i18n';

/**
 * ManageProfit component allows toggling and managing profit margin calculation for a product price item.
 *
 * Displays a checkbox to enable/disable profit margin calculation. When enabled, shows fields for entering
 * the cost of goods, and displays calculated profit and profit margin based on the entered cost and product prices.
 *
 * @component
 * @param {Object} props
 * @param {string|number} props.id - Unique identifier for the price item.
 * @param {Function} props.setPriceItem - Function to update the price item state.
 * @param {Object} props.priceItem - The current price item object containing pricing and meta information.
 * @returns {JSX.Element}
 */
const ManageProfit = ({ id, setPriceItem, priceItem }) => {
    const [manageProfit, setManageProfit] = useState(priceItem?.meta?.calculate_profit_margin || false)

	return (
		<div className="mt-4">
			<div>
				<input
					type="checkbox"
					class="easycommerce-input-checkoutbox"
					id={`manage-profit-${id}`}
					checked={manageProfit}
					onChange={(e) => {
						setManageProfit(e.target.checked);
						setPriceItem((prev) => ({
							...prev,
							meta: { ...prev.meta, calculate_profit_margin: e.target.checked },
						}));
					}}
				/>
				<label
					htmlFor={`manage-profit-${id}`}
					class="text-base font-inter font-normal ml-2 text-[#282828]"
				>
					{__('Calculate profit margin', 'easycommerce')}
				</label>
			</div>

			{manageProfit && (
				<div className=" grid grid-cols-3 gap-4 mt-3">
					<div>
						<h5 className="text-base text-ec-title font-inter mb-[6px]">
							{__('Cost of Goods', 'easycommerce')}
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
								placeholder={__('Enter Cost', 'easycommerce')}
								value={priceItem.meta.cost_per_item || ''}
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
										meta: { ...prev.meta, cost_per_item: value },
									}));
								}}
							/>
						</div>
					</div>

					<div>
						<h5 className="text-base text-ec-title font-inter mb-[6px]">
							{__('Profit', 'easycommerce')}
						</h5>

						{(() => {
							const salePrice = parseFloat(priceItem.sale_price) || 0;
							const regularPrice = parseFloat(priceItem.regular_price) || 0;
							const costPerItem =
								parseFloat(priceItem.meta?.cost_per_item) || 0;
							const profit =
								(priceItem.sale_price ? salePrice : regularPrice) - costPerItem;
							return (
								<div
									className={`h-ec-input rounded-lg font-inter text-[14px] leading-[20px] border ${
										profit < 0 ? 'border-ec-red' : 'border-ec-table-stock'
									} placeholder-ec-placeholder focus-within:border-ec-primary focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out overflow-hidden flex`}
								>
									<div className="h-full w-[50px] flex items-center justify-center border-r border-ec-table-stock bg-[#F9F9F9]">
										<span className="text-gray-400 text-base pointer-events-none">
											{EASYCOMMERCE.currency_symbol}
										</span>
									</div>
									<input
										type="number"
										name={`regular-price-` + id}
										className="w-[calc(100%_-_50px)] h-full border-none outline-none shadow-none text-ec-title"
										placeholder={__('Yet to calculate', 'easycommerce')}
										value={profit}
										disabled
									/>
								</div>
							);
						})()}
					</div>

					<div>
						<h5 className="text-base text-ec-title font-inter mb-[6px]">
							{__('Margin', 'easycommerce')}
						</h5>
						{(() => {
							const salePrice = parseFloat(priceItem.sale_price) || 0;
							const regularPrice = parseFloat(priceItem.regular_price) || 0;
							const costPerItem =
								parseFloat(priceItem.meta?.cost_per_item) || 0;
							const profit =
								(priceItem.sale_price ? salePrice : regularPrice) - costPerItem;
							const basePrice = priceItem.sale_price ? salePrice : regularPrice;
							const profitMargin =
								basePrice && profit
									? ((profit / costPerItem) * 100).toFixed(2)
									: 0;

							return (
								<div
									className={`h-ec-input rounded-lg font-inter text-[14px] leading-[20px] border ${
										profitMargin < 0 ? 'border-ec-red' : 'border-ec-table-stock'
									} placeholder-ec-placeholder focus-within:border-ec-primary focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out overflow-hidden flex`}
								>
									<input
										type="number"
										name={`regular-price-` + id}
										className={`w-[calc(100%_-_50px)] h-full border-none outline-none shadow-none text-ec-title pl-4`}
										placeholder={__('Yet to calculate', 'easycommerce')}
										value={profitMargin}
										disabled
									/>
									<div className="h-full w-[50px] flex items-center justify-center border-r border-ec-table-stock bg-[#F9F9F9]">
										%
									</div>
								</div>
							);
						})()}
					</div>
				</div>
			)}
		</div>
	);
};

export default ManageProfit;
