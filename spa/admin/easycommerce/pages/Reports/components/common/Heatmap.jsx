import React, { useEffect, useState } from "react";
import { Chart } from "react-chartjs-2";
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __, sprintf } from '@wordpress/i18n';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
} from "chart.js";
import { MatrixController, MatrixElement } from "chartjs-chart-matrix";

ChartJS.register(
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    MatrixController,
    MatrixElement
);

/**
 * HeatmapChart — chartjs-chart-matrix based heatmap
 *
 * Pass endpoint + params to fetch data from the API:
 *   <Heatmap
 *     endpoint="/easycommerce/v1/reports/orders/heatmap"
 *     params={{ range: 'last-30' }}
 *   />
 *
 * Expected API response shape:
 *   { success: true, data: { from, to, data: [ { x, y, v }, ... ] } }
 *
 * xLabels and yLabels are derived automatically from the response — no hardcoding needed.
 *
 * @param {string}  endpoint   - REST endpoint to fetch heatmap data from
 * @param {object}  params     - Query params appended to the endpoint URL
 * @param {string}  colorStart - Low value color
 * @param {string}  colorEnd   - High value color
 * @param {boolean} showValues - Render value text inside each cell
 * @param {string}  title      - Optional chart title
 */
const HeatmapChart = ({
    endpoint,
    params = {},
    colorStart = "#EEF0FF",
    colorEnd = "#9B85F5",
    showValues = false,
    title = "",
}) => {
    const [data, setData] = useState([]);
    const [xLabels, setXLabels] = useState([]);
    const [yLabels, setYLabels] = useState([]);
    const [isLoading, setIsLoading] = useState(!!endpoint);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (!endpoint) return;

        const fetchData = async () => {
            setIsLoading(true);
            setError(null);
            try {
                const result = await apiFetch({
                    path: addQueryArgs(endpoint, params),
                });

                if (result.success && result.data) {
                    const rows = result.data.data;

                    // Derive unique ordered labels directly from the response
                    const seenX = new Set();
                    const seenY = new Set();
                    const derivedX = [];
                    const derivedY = [];

                    rows.forEach(({ x, y }) => {
                        if (!seenX.has(x)) { seenX.add(x); derivedX.push(x); }
                        if (!seenY.has(y)) { seenY.add(y); derivedY.push(y); }
                    });

                    setData(rows);
                    setXLabels(derivedX);
                    setYLabels(derivedY);
                } else {
                    setError( __( 'Failed to load heatmap data.', 'easycommerce' ) );
                }
            } catch (err) {
                console.error('Heatmap fetch error:', err);
                setError( __( 'Error loading data.', 'easycommerce' ) );
            } finally {
                setIsLoading(false);
            }
        };

        fetchData();
    }, [endpoint, JSON.stringify(params)]);

    const valueLabelsPlugin = {
        id: "valueLabels",
        afterDatasetsDraw(chart) {
            if (!showValues) return;
            const { ctx } = chart;
            chart.data.datasets.forEach((dataset, i) => {
                const meta = chart.getDatasetMeta(i);
                meta.data.forEach((bar, index) => {
                    const value = dataset.data[index]?.v;
                    if (value == null) return;
                    const { x, y } = bar.getCenterPoint();
                    ctx.save();
                    ctx.fillStyle = "#000";
                    ctx.font = "bold 11px inherit";
                    ctx.textAlign = "center";
                    ctx.textBaseline = "middle";
                    ctx.fillText(value, x, y);
                    ctx.restore();
                });
            });
        },
    };

    const interpolateColor = (start, end, factor) => {
        const hexToRgb = (hex) => {
            const clean = hex.replace("#", "");
            return [
                parseInt(clean.substring(0, 2), 16),
                parseInt(clean.substring(2, 4), 16),
                parseInt(clean.substring(4, 6), 16),
            ];
        };
        const s = hexToRgb(start);
        const e = hexToRgb(end);
        const r = Math.round(s[0] + factor * (e[0] - s[0]));
        const g = Math.round(s[1] + factor * (e[1] - s[1]));
        const b = Math.round(s[2] + factor * (e[2] - s[2]));
        return `rgb(${r},${g},${b})`;
    };

    const values = data.map((d) => d.v);
    const minVal = Math.min(...values);
    const maxVal = Math.max(...values);

    const cellHeight = 40;
    const chartHeight = Math.max(300, yLabels.length * cellHeight + 60);

    const chartData = {
        datasets: [
            {
                label: title || "Heatmap",
                data,
                backgroundColor(ctx) {
                    const value = ctx.dataset.data[ctx.dataIndex]?.v ?? 0;
                    const factor = maxVal === minVal ? 0.5 : (value - minVal) / (maxVal - minVal);
                    return interpolateColor(colorStart, colorEnd, factor);
                },
                borderColor: "#EEF0FF",
                borderWidth: 1,
                width: ({ chart }) => {
                    const area = chart.chartArea;
                    if (!area) return 20;
                    return (area.right - area.left) / xLabels.length - 2;
                },
                height: () => cellHeight - 2,
            },
        ],
    };

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: "#ffffff",
                titleColor: "#121216",
                bodyColor: "#7A7A99",
                borderColor: "#EEF0FF",
                borderWidth: 1,
                padding: 12,
                cornerRadius: 8,
                callbacks: {
                    title(ctx) {
                        const d = ctx[0].dataset.data[ctx[0].dataIndex];
                        return `${d.x} — ${d.y}`;
                    },
                    label(ctx) {
                        const d = ctx.dataset.data[ctx.dataIndex];
                        // translators: %s: formatted data point value.
                        return sprintf( __( ' Value: %s', 'easycommerce' ), d.v.toLocaleString() );
                    },
                },
            },
        },
        scales: {
            x: {
                type: "category",
                labels: xLabels,
                grid: { display: false },
                border: { display: false },
                ticks: { color: "#7A7A99", font: { size: 14 } },
            },
            y: {
                type: "category",
                labels: yLabels,
                offset: true,
                grid: { display: false },
                border: { display: false },
                ticks: { color: "#7A7A99", font: { size: 14 } },
            },
        },
    };

    if (error) {
        return (
            <div className="w-full h-[350px] flex items-center justify-center text-red-400">
                {error}
            </div>
        );
    }

    return (
        <div style={{ height: `${chartHeight}px`, position: "relative" }}>
            <Chart
                type="matrix"
                data={chartData}
                options={options}
                plugins={[valueLabelsPlugin]}
            />
        </div>
    );
};

export default HeatmapChart;