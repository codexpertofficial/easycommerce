import React, { useState } from "react";
import { Doughnut } from "react-chartjs-2";
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
} from "chart.js";

ChartJS.register(ArcElement, Tooltip);

/**
 * DoughnutChart — react-chartjs-2 based doughnut chart
 *
 * @param {Array}   data      - Array of slice objects:
 *                              [{ id: string, value: number, color: string }]
 * @param {boolean} isLoading - Show loading state when true
 * @param {string}  title     - Optional chart title
 */
const DoughnutChart = ({ data = [], isLoading = false }) => {
    const [hiddenItems, setHiddenItems] = useState([]);

    const visibleData = data.filter((_, index) => !hiddenItems.includes(index));

    const chartData = {
        labels: visibleData.map((item) => item.id),
        datasets: [
            {
                data: visibleData.map((item) => item.value),
                backgroundColor: visibleData.map((item) => item.color),
                borderColor: visibleData.map((item) => item.color),
                borderWidth: 1,
                hoverOffset: 6,
            },
        ],
    };

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: "60%",
        plugins: {
            legend: {
                display: false,
            },
            tooltip: {
                backgroundColor: "#fff",
                titleColor: "#111827",
                bodyColor: "#6b7280",
                borderColor: "#e5e7eb",
                borderWidth: 1,
                padding: 10,
                cornerRadius: 8,
                callbacks: {
                    label: (ctx) =>
                        ` ${ctx.label}: ${ctx.parsed.toLocaleString()}`,
                },
            },
        },
    };

    const toggleLegend = (index) => {
        setHiddenItems((prev) =>
            prev.includes(index)
                ? prev.filter((i) => i !== index)
                : [...prev, index]
        );
    };

    return (
        <div className="flex items-center justify-center min-h-[340px]">
            <div className="h-[250px]">
                <Doughnut data={chartData} options={options} />
            </div>
            <div className="flex flex-col gap-2 pl-6">
                {data.map((item, index) => {
                    const isHidden = hiddenItems.includes(index);
                    return (
                        <div
                            key={index}
                            className={`flex items-center gap-2 cursor-pointer ${
                                isHidden ? "opacity-40 line-through" : ""
                            }`}
                            onClick={() => toggleLegend(index)}
                        >
                            <span
                                className="w-3 h-3 rounded-full flex-shrink-0"
                                style={{ backgroundColor: item.color }}
                            />
                            <span className="text-sm text-gray-600 whitespace-nowrap">
                                {item.id}
                            </span>
                            <span className="text-sm font-medium text-gray-900">
                                {item.value.toLocaleString()}
                            </span>
                        </div>
                    );
                })}
            </div>
        </div>
    );
};

export default DoughnutChart;