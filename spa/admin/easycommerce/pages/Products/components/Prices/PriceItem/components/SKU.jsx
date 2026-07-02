import { useEffect, useState, useRef } from 'react';
import { __ } from '@wordpress/i18n';

import TextField from '../../../../../../../common/components/inputs/TextField';

/**
 * SKU component for generating and editing the SKU (Stock Keeping Unit) of a price item.
 *
 * Automatically generates a SKU based on the product title and price item name,
 * and allows manual editing via a text field.
 *
 * @component
 * @param {Object} props - Component props.
 * @param {Object} props.priceItem - The price item object containing SKU and name.
 * @param {Function} props.setPriceItem - Function to update the price item state.
 * @param {string|number} props.id - Unique identifier for the price item.
 * @param {number} props.index - Index of the price item in the list.
 * @param {string} props.productTitle - Title of the product used for SKU generation.
 * @returns {JSX.Element} The SKU input field component.
 */
const SKU = ({ priceItem, setPriceItem, id, index, productTitle, prevData }) => {
    const isInitialMount = useRef(true);
    const [shouldCalculateSKU, setShouldCalculateSKU] = useState(true);

    const initialCalculatedSKU = (() => {
        let calculatedSKU = '';
        const baseTitle = productTitle?.replace(/[^a-zA-Z0-9]+/g, '').toUpperCase() || '';

        if (baseTitle) {
            let nameForSKU = priceItem.name || '';
            nameForSKU = nameForSKU.replace(/[^a-zA-Z0-9]+/g, '').toUpperCase();

            if (nameForSKU) {
                calculatedSKU = `${baseTitle}${nameForSKU}`;
            } else {
                calculatedSKU = `${baseTitle}${index + 1}`;
            }
        }

        return calculatedSKU;
    })();

    useEffect(() => {
        if (prevData && prevData.sku !== initialCalculatedSKU) {
            setShouldCalculateSKU(false);
        }
    }, []);

    useEffect(() => {
        if (isInitialMount.current) {
            isInitialMount.current = false;
            return;
        }

        if (!shouldCalculateSKU) {
            setPriceItem((prev) => ({
                ...prev,
                sku: prevData.sku
            }));
            return;
        }

        let calculatedSKU = '';
        const baseTitle = productTitle?.replace(/[^a-zA-Z0-9]+/g, '').toUpperCase() || '';

        if (baseTitle) {
            let nameForSKU = priceItem.name || '';
            nameForSKU = nameForSKU.replace(/[^a-zA-Z0-9]+/g, '').toUpperCase();

            if (nameForSKU) {
                calculatedSKU = `${baseTitle}${nameForSKU}`;
            } else {
                calculatedSKU = `${baseTitle}${index + 1}`;
            }
        }
        
        setPriceItem((prev) => ({
            ...prev,
            sku: calculatedSKU
        }));
    }, [productTitle, priceItem.name]);

	return (
        <TextField
            name={`pricing-sku-${id}`}
            placeholder={__('SKU', 'easycommerce')}
            className="h-ec-input"
            value={priceItem.sku}
            onChange={(e) => {
                const newSKU = e.target.value;
                setPriceItem((prev) => ({ ...prev, sku: newSKU }));
            }}
        />
    );
};

export default SKU;