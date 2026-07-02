import React from 'react';

const ChartLoading = ({ height = 'h-[350px]' }) => {
	return (
		<div className={`w-full ${height} min-h-60 flex items-center justify-center text-gray-400`}>
			Loading...
		</div>
	);
};

export default ChartLoading;
