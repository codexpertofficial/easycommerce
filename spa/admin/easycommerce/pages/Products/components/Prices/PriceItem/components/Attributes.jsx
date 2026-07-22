import { __, sprintf } from '@wordpress/i18n';
import { flushSync } from 'react-dom';

import Dropdown from '../../../../../../../common/components/inputs/Dropdown';

/**
 * Renders a list of attribute dropdowns for a product price item, allowing selection of attribute values.
 *
 * @component
 * @param {Object[]} productAttributes - Array of attribute objects, each containing a name, id, and values.
 * @param {Object} priceItem - The current price item object, containing selected attributes.
 * @param {Function} setPriceItem - Function to update the price item state.
 * @param {Function} attrChangeTrigger - Callback function triggered after an attribute value changes.
 * @returns {JSX.Element} The rendered attribute dropdowns.
 */
const Attributes = ({
	productAttributes,
	globalAttributes,
	priceItem,
	setPriceItem,
	attrChangeTrigger,
}) => {
	// Validate all props exist and have correct types
	if (!Array.isArray(productAttributes)) {
		return null;
	}

	if (!priceItem || typeof priceItem !== 'object') {
		return null;
	}

	if (typeof setPriceItem !== 'function') {
		return null;
	}

	const isValidAttribute = (attr) => {
		const exists = !!attr;

		if (!exists) return false;

		const isObject = typeof attr === 'object';

		if (!isObject) return false;

		const hasId = !!attr.id;

		if (!hasId) return false;

		const isArray = Array.isArray(attr.values);

		if (!isArray) return false;

		const hasValues = attr.values.length > 0;

		if (!hasValues) return false;

		const everyValueValid = attr.values.every((value, index) => {
			if (!value) {
				return false;
			}

			const isObject = typeof value === 'object';
			if (!isObject) {
				return false;
			}

			const hasAttrId = !!value.attribute_id;
			const hasId = !!value.id;

			return hasAttrId && hasId;
		});

		return everyValueValid;
	};

	const attrOptions = (attribute) => {
		const attrID = attribute.id;
		const valueIds = new Set(attribute.values.map((value) => value.id));

		const filteredOptions = globalAttributes
			.find((globalAttr) => globalAttr.id === attrID)
			?.options.filter((option) => valueIds.has(option.id));

		return filteredOptions.map((option) => ({
			label: option.name,
			value: option.name,
			id: option.id,
		}));
	};

	return (
		<div className="grid grid-cols-3 items-center gap-4 mt-3">
			{productAttributes.map((productAttr, productAttrIndex) => {
				const attributeData = globalAttributes?.find(
					(attr) => attr.id === productAttr.id,
				);

				// if (!isValidAttribute(productAttr)) {
				// 	console.warn('Attributes: Invalid attribute structure', productAttr);
				// 	return null;
				// }
				const valueIds = new Set(productAttr.values.map((value) => String(value.id)));

				const filteredOptions = globalAttributes
					.find((globalAttr) => String(globalAttr.id) === String(productAttr.id))
					?.options.filter((option) => valueIds.has(String(option.id)));

				const attrOptions = filteredOptions?.map((option) => ({
					label: option.name,
					value: option.name,
					id: option.id,
				}));

				if (!attributeData || !attrOptions || attrOptions.length === 0) {
					return null;
				}

				const selectedValue = attrOptions.find((option) =>
					priceItem.attributes.some((item) => item.value_id === option.id),
				);

				return (
					<div
						key={`${attributeData.id}-${attributeData.name}-${productAttrIndex}`}
					>
						<h5 className="text-base text-ec-title font-inter mb-[6px]">
							{attributeData.name}
						</h5>
						<div className="h-ec-input">
							<Dropdown
								placeholder={
									// translators: %s: attribute name.
									sprintf(__('Select %s', 'easycommerce'), attributeData.name)
								}
								value={selectedValue?.value || null}
								options={attrOptions}
								onChange={(selected) => {
									// Validate selected option
									if (!selected || !selected.id) {
										console.warn('Attributes: Invalid selection');
										return;
									}

									// Find the full value object
									const selectedValueObj = attributeData.options.find(
										(value) =>
											value.id === selected.id || value.id == selected.id,
									);

									flushSync(() => {
										setPriceItem((prev) => {
											return {
												...prev,
												attributes: [
													...prev.attributes.filter(
														(prevAttr) =>
															prevAttr.attribute_id !== productAttr.id,
													),
													{
														attribute_id: productAttr.id,
														attribute_slug: attributeData.slug,
														value_slug: selectedValueObj.slug,
														value_id: selectedValueObj.id,
													},
												],
											};
										});
									});

									// Safely trigger callback
									if (typeof attrChangeTrigger === 'function') {
										try {
											attrChangeTrigger();
										} catch (error) {
											console.error(
												'Attributes: Error in attrChangeTrigger',
												error,
											);
										}
									}
								}}
							/>
						</div>
					</div>
				);
			})}
		</div>
	);
};

export default Attributes;
