import React, { useState, useEffect } from "react";
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import LineChart from "../../Reports/components/common/LineChart";

const RecentSales = ({ range }) => {
    const [salesData, setSalesData] = useState([]);
    const [isLoading, setIsLoading] = useState(false);

    const fetchSales = async (range) => {
        setIsLoading(true);
        apiFetch({
            path: addQueryArgs('/easycommerce/v1/dashboard/sales', { range }),
        }).then((data) => {
            if (data.success) {
                const sales = data.data.sales.map((serie) => ({
                    ...serie,
                    // serie.id is the translated label; match on the stable key instead.
                    yAxisID: serie.key === "sales_count" ? "y1" : "y",
                }));
                setSalesData(sales);
            }
            setIsLoading(false);
        });
    };

    useEffect(() => {
        fetchSales(range);
    }, [range]);

        const extraScales = {
        y1: {
            type: "linear",
            display: true,
            position: "right",
            grid: { drawOnChartArea: false },
            border: { display: true, color: "rgba(0,0,0,0.1)" },
            ticks: {
                color: "#6b7280",
                font: { size: 12 },
                callback: (value) => value.toLocaleString(),
            },
        },
    };


    return (
        <LineChart data={salesData} isLoading={isLoading} showShadow={false} extraScales={extraScales} />
    );
};

export default RecentSales;