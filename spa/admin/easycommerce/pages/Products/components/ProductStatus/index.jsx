import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import Dropdown from '../../../../../common/components/inputs/Dropdown';

const ProductStatus = ({ prevStatus, onStatusChange }) => {
	const statusMap = {
		publish: __('Live', 'easycommerce'),
		draft: __('Draft', 'easycommerce'),
	};

	const [status, setStatus] = useState(
		prevStatus
			? prevStatus.toLowerCase() === 'publish'
				? 'live'
				: 'draft'
			: 'draft'
	);

	const productStatusOptions = [
		{ value: 'publish', label: statusMap.publish },
		{ value: 'draft', label: statusMap.draft },
	];
	const statusValue = status.toLowerCase() === 'live' ? 'publish' : 'draft';

	return (
		<div className="w-[100px] h-[41px] bg-white rounded-lg ec-product-status-dropdown">
			<Dropdown
				options={productStatusOptions}
				placeholder={statusMap[statusValue]}
				setStatus={setStatus}
				value={statusMap[statusValue]}
				onChange={(e) => {
					// Use the raw option value, never the (translated) label.
					const newStatusValue = e.value === 'publish' ? 'publish' : 'draft';
					setStatus(newStatusValue === 'publish' ? 'live' : 'draft');
					if (onStatusChange) {
						onStatusChange(newStatusValue);
					}
				}}
				iconColor="#7351FD"
			/>
			<input type="hidden" name="product_status" value={statusValue} />
		</div>
	);
};

export default ProductStatus;
