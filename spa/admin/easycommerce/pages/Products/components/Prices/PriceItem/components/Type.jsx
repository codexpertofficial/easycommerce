import { __ } from '@wordpress/i18n';

/**
 * Renders a radio button group for selecting the product type (physical or digital).
 *
 * @component
 * @param {Object} props
 * @param {Object} props.priceItem - The current price item object containing product details.
 * @param {Function} props.setPriceItem - Function to update the price item state.
 * @param {string|number} props.id - Unique identifier for the radio input elements.
 * @returns {JSX.Element} The rendered component for selecting product type.
 */
const Type = ({ priceItem, setPriceItem, id }) => {
	return (
        <div>
            <h5 className="text-base font-normal text-[#282828] font-inter mb-[6px]">
                {__('Product Type', 'easycommerce')}
            </h5>

            <fieldset className="flex items-center gap-4 mt-4">
                {['digital', 'physical'].map((type) => (
                    <div key={type} className="flex items-center gap-[6px]">
                        <input
                            type="radio"
                            id={`${type}-${id}`}
                            name={`product-type`}
                            className={"easycommerce-input-type-radio " + (priceItem.type === type ? 'checked' : '')}
                            value={type}
                            checked={priceItem.type === type}
                            onChange={(e) =>
                                setPriceItem((prev) => ({ ...prev, type: e.target.value }))
                            }
                        />
                        <label
                            htmlFor={`${type}-${id}`}
                            className="text-sm leading-[20px] font-normal font-inter text-[#3C3C42]"
                        >
                            {__(`${type === 'physical' ? 'Physical Product' : 'Digital Product'}`, 'easycommerce')}
                        </label>
                    </div>
                ))}
            </fieldset>
        </div>
    );
};

export default Type;
