import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';

import Header from './components/common/Header';
import Container from './components/common/Container';
import Stats from './components/Stats';
import LatestOrders from "./components/LatestOrders";
import RecentSales from "./components/RecentSales";
import TopSelling from "./components/TopSelling";
import LowStocks from "./components/LowStocks";
import AbandonedCarts from "./components/AbandonedCarts";
import RecentActivities from "./components/RecentActivities";
import JoinCommunity from "./components/JoinCommunity";

const Dashboard = () => {
    const [range, setRange] = useState({
        label: __( 'Last 30 days', 'easycommerce' ),
        value: 'last-30',
    });

    return (
        <>
            <Header
                title={__( 'Dashboard', 'easycommerce' )}
                range={range}
                setRange={setRange}
            />
            <div className="my-6 flex gap-6">
                <div className="flex flex-col gap-6 ec-db-lg:w-[70%] w-[60%]">
                    <Stats range={range.value} />

                    <div className="flex gap-6 ec-db-lg:flex-nowrap flex-wrap">
                        <div className="ec-db-lg:w-1/2 w-full">
                            <Container title={__( 'Latest Orders', 'easycommerce' )} fillHeight button_url="admin.php?page=easycommerce#/orders">
                                <LatestOrders range={range.value} />
                            </Container>
                        </div>
                        <div className="ec-db-lg:w-1/2 w-full">
                            <Container title={__( 'Recent Sales', 'easycommerce' )} fillHeight>
                                <RecentSales range={range.value} />
                            </Container>
                        </div>
                    </div>

                    <div className="flex gap-6 ec-db-lg:flex-nowrap flex-wrap">
                        <div className="ec-db-lg:w-1/2 w-full">
                            <Container title={__( 'Top Selling', 'easycommerce' )} fillHeight>
                                <TopSelling range={range.value} />
                            </Container>
                        </div>
                        <div className="ec-db-lg:w-1/2 w-full">
                            <Container title={__( 'Low Stock', 'easycommerce' )} fillHeight>
                                <LowStocks />
                            </Container>
                        </div>
                    </div>
                </div>

                <div className="flex flex-col gap-6 ec-db-lg:w-[30%] w-[40%]">
                    <Container title={__( 'Abandoned Carts', 'easycommerce' )} button_url="admin.php?page=easycommerce#/abandoned-cart">
                        <AbandonedCarts range={range.value} />
                    </Container>
                    <Container title={__( 'Recent Activities', 'easycommerce' )}>
                        <RecentActivities range={range.value} />
                    </Container>
                    <JoinCommunity />
                </div>
            </div>
        </>
    );
};

export default Dashboard;
