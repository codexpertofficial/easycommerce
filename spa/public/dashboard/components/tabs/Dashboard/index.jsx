import React, { useEffect, useState } from "react";

// Components
import Summery from "./sections/Summery";
import Statuses from "./sections/Statuses";
import RecentOrders from "./sections/RecentOrders";
import DashboardSkeleton from "./Skeletons";

const Dashboard = () => {

    const orderStatusData = [
        {
            id: "completed",
            label: "Completed",
            value: 0,
            color: "#4DDFFF",
        },
        {
            id: "processing",
            label: "Processing",
            value: 0,
            color: "#F68D2B",
        },
        {
            id: "on_hold",
            label: "On Hold",
            value: 0,
            color: "#FFB92C",
        },
        {
            id: "pending",
            label: "Pending",
            value: 0,
            color: "#C89DFE",
        },
        {
            id: "refunded",
            label: "Refunded",
            value: 0,
            color: "#FFDA57",
        },
        {
            id: "cancelled",
            label: "Cancelled",
            value: 0,
            color: "#FF3A52",
        },
        // {
        //     id: "failed",
        //     label: "Failed",
        //     value: 0,
        //     color: "#FF4E7B",
        // },
    ];

    const [customer, setCustomer] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [orderStats, setOrderStats] = useState([]);
    const [orderStatus, setOrderStatus] = useState([]);
    const [orders, setOrders] = useState([]);

    useEffect(() => {
        fetch(`${EASYCOMMERCE.rest_base}/me/summary`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then((resp) => resp.json())
            .then((data) => {    
                if (data.success && data.data.customer?.orders) {
                    const fetchedOrders = data.data.customer.orders;
                    setCustomer(data.data.customer);
                    setOrderStats(fetchedOrders);
                    setOrders(fetchedOrders);
    
                    const statusCounts = fetchedOrders.reduce((acc, order) => {
                        if (order.status) {
                            acc[order.status] = (acc[order.status] || 0) + 1;
                        }
                        return acc;
                    }, {});

                    const updatedStatus = orderStatusData.map((item) => ({
                        ...item,
                        value: statusCounts[item.id] || 0,
                    }));

                    setOrderStatus(updatedStatus);
                }
    
                setIsLoading(false);
            });
    }, []);
    

    return (
        <>
            {isLoading ? (
                <DashboardSkeleton />
            ) : (
                <>
                    <div className="grid grid-cols-1 gap-5">
                        <Summery data={customer} />

                        {/* <Statuses isLoading={isLoading} orders={orderStatus} /> */}
                    </div>
                    <RecentOrders orders={orders} />
                </>
            )}
        </>
    );
};

export default Dashboard;
