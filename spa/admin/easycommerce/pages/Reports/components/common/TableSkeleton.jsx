import React from 'react';

const TableSkeleton = ({ rows = 5, columns = 6, columnWidths = [] }) => {
	// If columnWidths not provided, distribute evenly
	const widths = columnWidths.length === columns
		? columnWidths
		: Array(columns).fill(`${100 / columns}%`);

	return (
		<div>
			{Array.from({ length: rows }).map((_, rowIndex) => (
				<div
					key={`row-${rowIndex}`}
					className="flex items-center gap-4 px-4 py-3 min-h-[70px] border-b border-[#EEF0FF]"
				>
					{widths.map((width, colIndex) => (
						<div
							key={`cell-${rowIndex}-${colIndex}`}
							className="flex items-center justify-center"
							style={{ width }}
						>
							<div className="h-4 w-full rounded skeleton" />
						</div>
					))}
				</div>
			))}
		</div>
	);
};

export default TableSkeleton;
