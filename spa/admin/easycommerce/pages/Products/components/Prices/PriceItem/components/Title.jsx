import { useEffect, useState, forwardRef, useImperativeHandle } from 'react';
import { __ } from '@wordpress/i18n';

import TextField from '../../../../../../../common/components/inputs/TextField';

/**
 * Title component for editing the name of a price item.
 *
 * This component renders a text field for the price item's name and exposes an imperative
 * handle method `changeTitle` to programmatically update the name based on the first value
 * of each attribute in `priceItem.attributes`.
 *
 * @component
 * @param {Object} props
 * @param {Object} props.priceItem - The price item object containing attributes and name.
 * @param {Function} props.setPriceItem - Function to update the price item state.
 * @param {string|number} props.id - Unique identifier for the price item.
 * @param {React.Ref} ref - Ref forwarded to expose imperative methods.
 *
 * @example
 * const ref = useRef();
 * <Title ref={ref} priceItem={item} setPriceItem={setItem} id={item.id} />
 *
 * * To programmatically change the title:
 * ref.current.changeTitle();
 */
const Title = forwardRef(({ priceItem, setPriceItem, id }, ref) => {
	useImperativeHandle(ref, () => ({
		changeTitle: () => {
			const priceItemName = Object.values(priceItem.attributes).map(
				(attr) => attr.value_slug || '',
			);

			setPriceItem((prev) => ({
				...prev,
				name: priceItemName.length > 0 ? priceItemName.join('/') : prev.name,
			}));
		},
	}));

	return (
		<TextField
			name={`pricing-title-${id}`}
			placeholder={__('Write variation name', 'easycommerce')}
			className="h-ec-input"
			value={priceItem.name}
			onChange={(e) => {
				setPriceItem((prev) => ({ ...prev, name: e.target.value }));
			}}
		/>
	);
});

export default Title;
