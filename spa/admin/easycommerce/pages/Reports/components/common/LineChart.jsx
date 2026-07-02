import React, { useState, useEffect } from "react";
import { Line } from "react-chartjs-2";
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from "chart.js";


const shadowLinePlugin = {
    id: 'shadowLine',
    beforeDatasetDraw(chart, args) {
        const { ctx } = chart;
        const dataset = chart.data.datasets[args.index];

        ctx.save();

        ctx.shadowColor = dataset.borderColor + '50';
        ctx.shadowBlur = 15;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 12;
    },
    afterDatasetDraw(chart) {
        chart.ctx.restore();
    }
};

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const LineChart = ({ endpoint, params = {}, data = [], isLoading: externalLoading = false, showShadow = true, extraScales = {} }) => {
    const [chartData, setChartData] = useState([]);
    const [loading, setLoading] = useState(externalLoading || !endpoint);
    const [internalError, setInternalError] = useState(null);

    const fetchData = async () => {
        if (!endpoint) {
            setChartData(data);
            setLoading(false);
            return;
        }

        setLoading(true);
        setInternalError(null);
        
        try {
            const response = await apiFetch({
                path: addQueryArgs(endpoint, params),
            });
            setChartData(response.data.datasets || []);
        } catch (error) {
            console.error('Error fetching chart data:', error);
            setInternalError(error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, [endpoint, JSON.stringify(params)]);

    const isLoading = loading || externalLoading;
    const displayData = chartData.length > 0 ? chartData : data;
    const labels = displayData?.[0]?.data?.map((point) => point.x) || [];

    const datasets = displayData.flatMap((serie) => {
        const main = {
            label: serie.id,
            data: serie.data.map((point) => point.y),
            borderColor: serie.color,
            borderWidth: 2,
            borderDash: serie.borderDash ?? [],
            tension: 0.4,
            pointRadius: 3,
            pointBorderColor: serie.color,
            pointBackgroundColor: serie.color,
            pointHoverRadius: 6,
            backgroundColor: "transparent",
            fill: false,
            yAxisID: serie.yAxisID ?? "y",
        };

        if (!serie.previous) return [main];

        const previous = {
            label: `Previous ${serie.id}`,
            data: serie.previous.map((point) => point.y),
            borderColor: serie.previousColor ?? serie.color,
            borderWidth: 1.5,
            borderDash: [6, 4],          
            tension: 0.4,
            pointRadius: 0,             
            pointHoverRadius: 4,
            backgroundColor: "transparent",
            fill: false,
            yAxisID: serie.yAxisID ?? "y",
        };

        return [main, previous];
    });

    const chartDataFormatted = { labels, datasets };

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: "index",
            intersect: false,
        },
        scales: {
            y: {
                type: "linear",
                display: true,
                position: "left",
                grid: {
                    color: "rgba(0,0,0,0.06)",
                    drawBorder: false,
                },
                border: { display: true, color: "rgba(0,0,0,0.1)" },
                ticks: {
                    color: "#6b7280",
                    font: { size: 12 },
                    callback: (value) => value.toLocaleString(),
                },
            },
            x: {
                type: "category",
                grid: { display: false, drawBorder: false },
                border: { display: true, color: "rgba(0,0,0,0.1)" },
                ticks: {
                    color: "#6b7280",
                    font: { size: 12 },
                },
            },
            ...extraScales, 
        },
        plugins: {
            legend: {
                display: true,
                labels: {
                    usePointStyle: true,
                    pointStyle: "circle",
                    boxWidth: 8,   
                    boxHeight: 8,
                    padding: 20,
                    color: "#7A7A99",
                    font: { size: 16 },
                },
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
                        ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString()}`,
                },
            },
        },
    };

    return (
        <div className="w-full h-[350px] min-h-60 px-[0px]">
            <Line 
                data={chartDataFormatted} 
                options={options} 
                plugins={showShadow ? [shadowLinePlugin] : []} 
                className="!w-full" 
            />
        </div>
    );
};

export default LineChart;
