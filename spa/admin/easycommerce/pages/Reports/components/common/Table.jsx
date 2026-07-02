import React from 'react';
import TableSkeleton from './TableSkeleton';

const Table = ({
	columns = [],
	data = [],
	loading = false,
	className = '',
	headerClassName = '',
	rowClassName = '',
	skeletonRows = 5,
	skeletonColumns,
}) => {
	if (loading) {
		return (
			<div className={className}>
				<TableSkeleton
					rows={skeletonRows}
					columns={skeletonColumns || columns.length}
					columnWidths={columns.map((col) => col.width)}
				/>
			</div>
		);
	}

	return (
		<div className={className}>
			<div className={`bg-[#F7F7F7] rounded-lg flex items-center gap-4 px-4 py-3 font-medium ${headerClassName}`}>
				{columns.map((column, index) => (
					<div
						key={index}
						className="flex items-center justify-center"
						style={{ width: column.width }}
					>
						<h6 className="font-medium">{column.header}</h6>
					</div>
				))}
			</div>

			{data.map((item, index) => (
				<div
					className={`flex items-center gap-4 px-4 py-3 min-h-[70px] border-b border-[#EEF0FF] ${rowClassName}`}
					key={index}
				>
					{columns.map((column, colIndex) => (
						<div
							key={colIndex}
							className="flex items-center justify-center"
							style={{ width: column.width }}
						>
							{column.render ? (
								column.render(item)
							) : (
								item[column.accessor]
							)}
						</div>
					))}
				</div>
			))}
		</div>
	);
};

export default Table;
