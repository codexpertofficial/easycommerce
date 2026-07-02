import React from 'react';

const TrendIndicator = ({ type, value, size = 'base' }) => {
	const color = type === 'increment' ? '#00A63E' : '#FF6161';
	const textSize = size === 'sm' ? 'text-sm font-medium' : 'text-base font-semibold';

	return (
		<div className="flex items-center gap-1">
			{type === 'decrement' ? (
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M10.668 11.334H14.668V7.33398" stroke={color} strokeWidth="1.33333" strokeLinecap="round" strokeLinejoin="round"/>
					<path d="M14.6654 11.334L8.9987 5.66732L5.66536 9.00065L1.33203 4.66732" stroke={color} strokeWidth="1.33333" strokeLinecap="round" strokeLinejoin="round"/>
				</svg>
			) : (
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M10.668 4.66602H14.668V8.66602" stroke={color} strokeWidth="1.33333" strokeLinecap="round" strokeLinejoin="round"/>
					<path d="M14.6654 4.66602L8.9987 10.3327L5.66536 6.99935L1.33203 11.3327" stroke={color} strokeWidth="1.33333" strokeLinecap="round" strokeLinejoin="round"/>
				</svg>
			)}

			<span className={`${textSize}`} style={{ color }}>
				{value}
			</span>
		</div>
	);
};

export default TrendIndicator;
