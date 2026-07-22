import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';

import Table from '../common/Table';
import SeeReportLink from '../common/SeeReportLink';

const TopCustomers = ({ range }) => {
    const [customersData, setCustomersData] = useState([]);
    const [loading, setLoading] = useState(false);

    const fetchTopCustomers = (range) => {
        setLoading(true);
        apiFetch({
            path: addQueryArgs('/easycommerce/v1/reports/revenue/top-customers', {
                range,
            }),
        })
            .then((data) => {
                setCustomersData(data.data.customers);
                setLoading(false);
            })
            .catch((error) => {
                console.error('Error fetching top customers:', error);
            });
    };

    useEffect(() => {
        fetchTopCustomers(range);
    }, [range]);

    const columns = [
        { header: '#', width: '5%', accessor: 'rank', render: (item) => `#${item.rank}` },
        { header: __( 'Customer Name', 'easycommerce' ), width: '20%', accessor: 'customer_name', render: (item) => <span className="truncate">{item.customer_name}</span> },
        { header: __( 'Email', 'easycommerce' ), width: '20%', accessor: 'customer_email', render: (item) => <span className="truncate">{item.customer_email}</span> },
        { header: __( 'First Order', 'easycommerce' ), width: '15%', accessor: 'first_order_date' },
        { header: __( 'Last Order', 'easycommerce' ), width: '15%', accessor: 'last_order_date' },
        { header: __( 'Total Orders', 'easycommerce' ), width: '10%', accessor: 'total_orders' },
        { header: __( 'Revenue Earned', 'easycommerce' ), width: '15%', accessor: 'revenue_earned' },
    ];

    return (
        <div className="text-base text-[#1B2538]">
            <Table
                columns={columns}
                data={customersData}
                loading={loading}
                skeletonRows={5}
                skeletonColumns={6}
            />
        </div>
    );
};

export default TopCustomers;
