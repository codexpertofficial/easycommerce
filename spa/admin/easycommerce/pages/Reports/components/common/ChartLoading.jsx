import React from 'react';
import { __ } from '@wordpress/i18n';

const ChartLoading = ({ height = 'h-[350px]' }) => {
	return (
		<div className={`w-full ${height} min-h-60 flex items-center justify-center text-gray-400`}>
			{__( 'Loading...', 'easycommerce' )}
		</div>
	);
};

export default ChartLoading;
