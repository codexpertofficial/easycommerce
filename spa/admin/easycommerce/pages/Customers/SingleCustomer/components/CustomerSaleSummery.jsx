import React, { useEffect, useState } from "react";
import SalesChart from "./SalesChart";

const getCurrentMonthDates = () => {
    const dates = [];
    const today = new Date();
    const year = today.getFullYear();
    const month = today.getMonth(); 

    const lastDay = new Date(year, month + 1, 0).getDate(); 

    for (let day = 1; day <= lastDay; day++) {
        const date = new Date(year, month, day);
        const label = date.toLocaleDateString("en-GB", {
            day: "numeric",
            month: "short",
        }); 
        const key = date.toISOString().split("T")[0]; 
        dates.push({ key, label });
    }

    return dates;
};

const CustomerSaleSummery = ({ id }) => {
    const [salesData, setSalesData] = useState([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const response = await fetch(
                    `${EASYCOMMERCE.rest_base}/customers/${id}`,
                    {
                        headers: {
                            "Content-Type": "application/json",
                            "X-WP-Nonce": EASYCOMMERCE.nonce,
                        },
                    }
                );

                const result = await response.json();
                const orders = result.data.customer.orders;

                const dailyData = {};
                orders.forEach((order) => {
                    const key = new Date(order.created_at).toISOString().split("T")[0]; 
                    if (!dailyData[key]) {
                        dailyData[key] = { totalSales: 0, orderCount: 0 };
                    }
                    dailyData[key].totalSales += parseFloat(order.total);
                    dailyData[key].orderCount += 1;
                });

                const monthDates = getCurrentMonthDates();
                const sales = [];
                const counts = [];

                monthDates.forEach(({ key, label }) => {
                    sales.push({
                        x: label,
                        y: dailyData[key]?.totalSales || 0,
                    });
                    counts.push({
                        x: label,
                        y: dailyData[key]?.orderCount || 0,
                    });
                });

                setSalesData([
                    {
                        id: "Purchase Amount",
                        color: "#06D264",
                        data: sales,
                    },
                    {
                        id: "Order Count",
                        color: "#FF1074",
                        data: counts,
                    },
                ]);
                setIsLoading(false);
            } catch (error) {
                console.error("Error:", error);
                setIsLoading(false);
            }
        };

        fetchData();
    }, [id]);

    return (
        <div className="w-full min-h-96 flex justify-center items-center">
            <div className="w-full p-8">
                <SalesChart data={salesData} isLoading={isLoading} />
            </div>
        </div>
    );
};

export default CustomerSaleSummery;
