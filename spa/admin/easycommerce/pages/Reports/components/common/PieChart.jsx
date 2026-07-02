'use client';

import { PieChart as RePieChart, Pie, Cell, LabelList } from 'recharts';
import {
	ChartContainer,
	ChartTooltip,
	ChartTooltipContent,
} from '../../../../components/ui/chart';

export default function PieChart({ data = [] }) {
	if (!data.length) {
		return (
			<div className="h-[300px] w-full flex items-center justify-center text-gray-400">
				No data available
			</div>
		);
	}

	// Map id to name for recharts
	const chartData = data.map((item) => ({
		name: item.id,
		value: item.value,
		fill: item.color,
	}));

	return (
		<ChartContainer className="h-[300px] w-full">
			<RePieChart>
				<ChartTooltip content={<ChartTooltipContent />} />

				<Pie
					data={chartData}
					dataKey="value"
					nameKey="name"
					outerRadius={100}
					label={({ name, value }) => `${name}: ${value}`}
				>
					{chartData.map((entry, index) => (
						<Cell key={`cell-${index}`} fill={entry.fill} />
					))}
				</Pie>
			</RePieChart>
		</ChartContainer>
	);
}