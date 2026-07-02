import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import Cards from './components/Cards';
import CardsSkeleton from './components/CardsSkeleton';

const Stats = ({ range }) => {
    const [overviewData, setOverviewData] = useState([]);
    const [loading, setLoading] = useState(true);

    const fetchStats = async (range) => {
        apiFetch({
            path: addQueryArgs('/easycommerce/v1/reports/overview/stats', {range}),
        }).then((data) => {
            setOverviewData(data.data.stats);
            setLoading(false);
        });
    };

    useEffect(() => {
        fetchStats(range);
    }, [range]);

    return loading ? <CardsSkeleton count={6} /> : <Cards items={overviewData} />;
};

export default Stats;
