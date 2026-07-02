import * as React from 'react';
import * as RechartsPrimitive from 'recharts';

import { cn } from '../../lib/utils';

const ChartContext = React.createContext(null);

function useChart() {
	const context = React.useContext(ChartContext);
	return context || { config: {} };
}

function ChartContainer({
	id,
	className,
	children,
	config = {},
	initialDimension = { width: 320, height: 200 },
	...props
}) {
	const uniqueId = React.useId();
	const chartId = `chart-${id ?? uniqueId.replace(/:/g, '')}`;

	return (
		<ChartContext.Provider value={{ config }}>
			<div
				data-slot="chart"
				data-chart={chartId}
				className={cn('flex aspect-video justify-center text-xs', className)}
				{...props}
			>
				<RechartsPrimitive.ResponsiveContainer initialDimension={initialDimension}>
					{children}
				</RechartsPrimitive.ResponsiveContainer>
			</div>
		</ChartContext.Provider>
	);
}

const ChartTooltip = RechartsPrimitive.Tooltip;

function ChartTooltipContent({
	active,
	payload,
	className,
	hideLabel = false,
	hideIndicator = false,
}) {
	if (!active || !payload?.length) {
		return null;
	}

	return (
		<div
			className={cn(
				'grid min-w-32 items-start gap-1.5 rounded-lg border border-gray-400/25 bg-white px-2.5 py-1.5 text-xs shadow-xl',
				className,
			)}
		>
			{payload.map((item, index) => (
				<div key={index} className="flex items-center gap-2">
					{!hideIndicator && (
						<div
							className="h-2.5 w-2.5 rounded-[2px]"
							style={{ backgroundColor: item.color }}
						/>
					)}
					<span className="text-muted-foreground">{item.name}</span>
					<span className="font-medium">{item.value}</span>
				</div>
			))}
		</div>
	);
}

const ChartLegend = RechartsPrimitive.Legend;

export {
	ChartContainer,
	ChartTooltip,
	ChartTooltipContent,
	ChartLegend,
};
