import { __ } from '@wordpress/i18n';

import TextField from '../../../../../../../common/components/inputs/TextField';
import { Tooltip } from 'react-tooltip';

/**
 * Stock component for managing inventory details of a product variation.
 *
 * @component
 * @param {Object} props - Component props.
 * @param {string|number} props.id - Unique identifier for the price item.
 * @param {Object} props.priceItem - The current price item object containing stock information.
 * @param {function} props.setPriceItem - Function to update the price item state.
 * @returns {JSX.Element} The rendered Stock component.
 */
const Stock = ({ id, priceItem, setPriceItem }) => {
	return (
		<div>
			<h4 className="font-inter font-normal text-xl text-[#282828]">
				{__('Inventory', 'easycommerce')}
			</h4>
			<div className="flex mt-4 gap-[25px]">
				<div className="w-full">
					<div>
						<label
							for={`manage-stock-` + id}
							class="flex gap-3 text-base font-inter mr-2 text-[#282828]"
						>
							{__('Stock Count', 'easycommerce')}
							<Tooltip text="Number of items we have in stock. If you don't manage stock for this variation, simply leave it blank." />
						</label>
					</div>
					<div className="h-ec-input mt-3">
						<TextField
							className="h-ec-input"
							name={'stock-count-' + id}
							placeholder={__('Enter Stock Count', 'easycommerce')}
							value={priceItem.stock_quantity}
							onChange={(e) =>
								setPriceItem((prev) => ({
									...prev,
									stock_quantity: e.target.value,
								}))
							}
						/>
					</div>
				</div>
				<div className="w-full">
					<div>
						<label class="flex gap-3 text-base font-inter mr-2 text-ec-title">
							{__('Low Stock Limit', 'easycommerce')}
							<Tooltip text="When should we call it low stock?" />
						</label>
					</div>
					<div className="h-ec-input mt-3">
						<TextField
							name={'low-stock-limit-' + id}
							className="h-ec-input"
							placeholder={__('Stock Limit', 'easycommerce')}
							value={priceItem.stock_limit}
							onChange={(e) =>
								setPriceItem((prev) => ({
									...prev,
									stock_limit: e.target.value,
								}))
							}
						/>
					</div>
				</div>
			</div>
		</div>
	);
};

export default Stock;
