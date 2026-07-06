import React, { useState } from 'react';

import Header from './components/common/Header';
import Container from './components/common/Container';
import Stats from './components/Stats';
import LatestOrders from "./components/LatestOrders";
import RecentSales from "./components/RecentSales";
import TopSelling from "./components/TopSelling";
import LowStocks from "./components/LowStocks";
import AbandonedCarts from "./components/AbandonedCarts";
import RecentActivities from "./components/RecentActivities";

const Dashboard = () => {
    const [range, setRange] = useState({
        label: 'Last 30 days',
        value: 'last-30',
    });

    return (
        <>
            <Header
                title="Dashboard"
                range={range}
                setRange={setRange}
            />
            <div className="my-6 flex gap-6">
                <div className="flex flex-col gap-6 ec-db-lg:w-[70%] w-[60%]">
                    <Stats range={range.value} />

                    <div className="flex gap-6 ec-db-lg:flex-nowrap flex-wrap">
                        <div className="ec-db-lg:w-1/2 w-full">
                            <Container title="Latest Orders" fillHeight button_url="admin.php?page=easycommerce#/orders">
                                <LatestOrders range={range.value} />
                            </Container>
                        </div>
                        <div className="ec-db-lg:w-1/2 w-full">
                            <Container title="Recent Sales" fillHeight>
                                <RecentSales range={range.value} />
                            </Container>
                        </div>
                    </div>

                    <div className="flex gap-6 ec-db-lg:flex-nowrap flex-wrap">
                        <div className="ec-db-lg:w-1/2 w-full">
                            <Container title="Top Selling" fillHeight>
                                <TopSelling range={range.value} />
                            </Container>
                        </div>
                        <div className="ec-db-lg:w-1/2 w-full">
                            <Container title="Low Stock" fillHeight>
                                <LowStocks />
                            </Container>
                        </div>
                    </div>
                </div>

                <div className="flex flex-col gap-6 ec-db-lg:w-[30%] w-[40%]">
                    <Container title="Abandoned Carts" button_url="admin.php?page=easycommerce#/abandoned-cart">
                        <AbandonedCarts range={range.value} />
                    </Container>
                    <Container title="Recent Activities">
                        <RecentActivities range={range.value} />
                    </Container>
                </div>
            </div>
        </>
    );
};

export default Dashboard;
