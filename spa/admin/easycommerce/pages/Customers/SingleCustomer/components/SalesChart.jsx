import React from "react";
import { __ } from "@wordpress/i18n";
import { Line } from "react-chartjs-2";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
} from "chart.js";

// Registering the Chart.js components
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
);

const SalesChart = ({ data, isLoading }) => {
    const labels = data?.[0]?.data.map((point) => point.x) || [];
    const datasets = data.map((serie) => ({
        label: serie.id,
        data: serie.data.map((point) => point.y),
        borderColor: serie.color,
        backgroundColor: "transparent",
        borderWidth: 2,
        tension: 0.4,
        pointRadius: 3,
        pointBorderColor: serie.color,
        pointBackgroundColor: "#fff",
    }));

    const chartData = {
        labels: labels,
        datasets: datasets,
    };

    const options = {
        responsive: true,
        interaction: {
            mode: "index",
            intersect: false,
        },
        scales: {
            y: {
                type: "linear",
                display: true,
                position: "left",
                ticks: {
                    callback: function (value) {
                        return value.toLocaleString();
                    },
                },
            },
            yRight: {
                type: "linear",
                display: true,
                position: "right",
                grid: {
                    drawOnChartArea: false, 
                },
                ticks: {
                    callback: function (value) {
                        return value; 
                    },
                },
            },
            x: {
                type: "category",
                ticks: {
                    color: "#000",
                },
            },
        },
        plugins: {
            legend: {
                display: false, 
                labels: {
                    color: "#000", 
                },
            },
        },
    };

    return (
        <>
            {!isLoading ? (
                <div className="w-full 2xl:w-[80%] 2xl:mx-auto ec-db-xl:w-full 3xl:mx-0 h-[350px] min-h-60 px-[30px]">
                    <Line data={chartData} options={options} className="!w-full" />
                </div>
            ) : (
                <div className="w-full h-[350px] min-h-60 flex items-center justify-center text-gray-400">
                    {__("Loading...", "easycommerce")}
                </div>
            )}
        </>
    );
};

export default SalesChart;
