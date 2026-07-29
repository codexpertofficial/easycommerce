import React from 'react';

/**
 * Renders the single highest-precedence badge for a product.
 *
 */
const POSITION_CLASSES = {
	'top-left': 'top-2 left-2 rtl:left-auto rtl:right-2',
	'top-center': 'top-2 left-1/2 -translate-x-1/2',
	'top-right': 'top-2 right-2 rtl:right-auto rtl:left-2',
};

const ProductBadge = ({ badges = [] }) => {
	if (!badges || badges.length === 0) {
		return null;
	}

	// Out of stock overrides everything else when present, per spec.
	const outOfStock = badges.find((b) => b.type === 'out_of_stock');
	const badge = outOfStock || badges[0];

	const positionClass = POSITION_CLASSES[badge.position] || POSITION_CLASSES['top-left'];

	return (
		<span
			className={`absolute z-10 text-xs font-bold px-2 py-1 rounded ${positionClass}`}
			style={{ backgroundColor: badge.color, color: badge.text_color || '#FFFFFF' }}
		>
			{badge.label}
		</span>
	);
};

export default ProductBadge;