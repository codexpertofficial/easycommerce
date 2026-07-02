import React, { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

import Table from './Table';

const DataTable = ({ endpoint, params = {} }) => {
    const [data, setData] = useState([]);
    const [columns, setColumns] = useState([]);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        setLoading(true);
        apiFetch({
            path: addQueryArgs(endpoint, params),
        })
            .then((res) => {
                if (res.data?.data) {
                    setData(res.data.data);
                }
                if (res.data?.columns) {
                    setColumns(res.data.columns.map((col) => {
                        const column = {
                            header: col.header,
                            width: col.width,
                            accessor: col.accessor,
                        };

                        if (col.formatter === 'rank') {
                            column.render = (item) => `#${item.rank}`;
                        }

                        if (col.truncate) {
                            column.render = (item) => (
                                <span className="truncate">{item[col.accessor]}</span>
                            );
                        }

                        if (col.action === 'link') {
                            column.render = (item) => (
                                <a href={`${col.url_base}${item[col.url_field]}`}>
                                    <svg width="24" height="15" viewBox="0 0 24 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 0C7.41454 0 3.25621 2.50875 0.187788 6.58361C-0.0625959 6.91746 -0.0625959 7.38386 0.187788 7.7177C3.25621 11.7975 7.41454 14.3062 12 14.3062C16.5855 14.3062 20.7438 11.7975 23.8122 7.72261C24.0626 7.38877 24.0626 6.92237 23.8122 6.58852C20.7438 2.50875 16.5855 0 12 0ZM12.3289 12.1902C9.28506 12.3817 6.7714 9.87297 6.96287 6.82418C7.11998 4.31052 9.15741 2.27309 11.6711 2.11599C14.7149 1.92452 17.2286 4.43326 17.0371 7.48205C16.8751 9.99079 14.8377 12.0282 12.3289 12.1902ZM12.1767 9.86315C10.537 9.96625 9.18196 8.61614 9.28997 6.97637C9.37343 5.62136 10.4732 4.52654 11.8282 4.43817C13.4679 4.33507 14.823 5.68518 14.7149 7.32495C14.6266 8.68487 13.5268 9.77969 12.1767 9.86315Z" fill="#3C3C42"/>
                                    </svg>
                                </a>
                            );
                        }

                        return column;
                    }));
                }
                setLoading(false);
            })
            .catch((error) => {
                console.error('Error fetching table data:', error);
            });
    }, [endpoint, JSON.stringify(params)]);

    return (
        <div className="text-base text-[#1B2538]">
            <Table
                columns={columns}
                data={data}
                loading={loading}
                skeletonRows={5}
                skeletonColumns={columns.length}
            />
        </div>
    );
};

export default DataTable;
