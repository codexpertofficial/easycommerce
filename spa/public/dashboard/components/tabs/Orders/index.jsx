import React, { useEffect, useState } from 'react';
import TableSkeleton from '../../../../../admin/common/TableSkeleton';
import Pagination from '../../../../../admin/common/components/Pagination';
import StatusBadge from '../../common/StatusBadge';
import ViewButton from '../../common/ViewButton';
import EmptyState from '../../common/EmptyState';

const Orders = () => {
    const [totalPage, setTotalPage] = useState(1);
    const [page, setPage] = useState(1);
    const [postPerPage, setPostPerPage] = useState(10);
    const [orders, setOrders] = useState([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        const handleClick = (e) => {
            const link = e.target.closest('a[href^="#/orders/page/"]');
            if (link) {
                e.preventDefault();
                const href = link.getAttribute('href');
                const match = href.match(/\/page\/(\d+)$/);
                if (match) {
                    setPage(Number(match[1]));
                }
            }
        };
        
        document.addEventListener('click', handleClick);
        return () => document.removeEventListener('click', handleClick);
    }, []);

    useEffect(() => {
        
        const url =`${EASYCOMMERCE.rest_base}/me/orders?page=${page}&per_page=${postPerPage}`;
        setIsLoading(true);
        fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': EASYCOMMERCE.nonce,
            },
        })
            .then((res) => res.json())
            .then((data) => {
                setIsLoading(false);

                if (data.success && data.data?.orders) {
                    setTotalPage(data.data.total_pages);
                    setOrders(data.data.orders);
                }
            });
    }, [page, postPerPage]);


    return (
        <>
            <div className="easycommerce-dashboard-section pb-[55px] flex flex-col gap-4">
                <h3 className="easycommerce-dashboard-section-title !text-lg sm:!text-2xl">
                    Orders
                </h3>
                <div className="w-full">
                    {!isLoading ? (
                        <>
                            {orders.length > 0 ? (
                                <div className="w-full border border-ec-border rounded-2xl overflow-x-auto bg-white">
                                    <table className="w-full min-w-[500px] border-none m-0">
                                        <thead className="easycommerce-dash-roth bg-ec-table-bg">
                                            <tr>
                                                <th className="font-inter font-semibold text-xs uppercase tracking-wide text-left rtl:text-right text-ec-light-black py-3.5 px-5 border-0">
                                                    Order ID
                                                </th>
                                                <th className="font-inter font-semibold text-xs uppercase tracking-wide text-left rtl:text-right text-ec-light-black py-3.5 px-5 border-0">
                                                    Date
                                                </th>
                                                <th className="font-inter font-semibold text-xs uppercase tracking-wide text-left rtl:text-right text-ec-light-black py-3.5 px-5 border-0">
                                                    Amount
                                                </th>
                                                <th className="font-inter font-semibold text-xs uppercase tracking-wide text-left rtl:text-right text-ec-light-black py-3.5 px-5 border-0">
                                                    Status
                                                </th>
                                                <th className="font-inter font-semibold text-xs uppercase tracking-wide text-right rtl:text-left text-ec-light-black py-3.5 px-5 border-0">
                                                    Action
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody className="easycommerce-dash-roth">
                                            {orders.map((order, index) => (
                                                <tr
                                                    key={index}
                                                    className={`transition-colors duration-150 hover:bg-ec-active ${
                                                        index === orders.length - 1
                                                            ? 'last-row'
                                                            : ''
                                                    }`}
                                                >
                                                    <td className="font-inter font-semibold text-sm text-left rtl:text-right text-ec-title py-4 px-5 border-0 border-b border-b-ec-border/70">
                                                        #{order.id}
                                                    </td>
                                                    <td className="font-inter text-sm text-left rtl:text-right text-ec-body py-4 px-5 border-0 border-b border-b-ec-border/70">
                                                        <span className="flex flex-col justify-start items-start">
                                                            <span className="font-medium">
                                                                {order.created_at
                                                                    ? order.created_at
                                                                    : 'N/A'}
                                                            </span>
                                                            <span className="text-xs leading-4 text-ec-placeholder">
                                                                {order.created_time
                                                                    ? order.created_time
                                                                    : 'N/A'}
                                                            </span>
                                                        </span>
                                                    </td>
                                                    <td className="font-inter font-semibold text-sm text-left rtl:text-right text-ec-title py-4 px-5 border-0 border-b border-b-ec-border/70">
                                                        {EASYCOMMERCE.currency_symbol}
                                                        {order.total}
                                                    </td>
                                                    <td className="font-inter text-sm text-left rtl:text-right py-4 px-5 border-0 border-b border-b-ec-border/70">
                                                        <StatusBadge status={order.status} />
                                                    </td>
                                                    <td className="font-inter text-sm text-right rtl:text-left py-4 px-5 border-0 border-b border-b-ec-border/70">
                                                        <ViewButton href={`#orders/${order.id}`} title="View order" />
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <div className="w-full border border-ec-border rounded-2xl bg-white">
                                    <EmptyState
                                        title="No orders available"
                                        message="Orders you place will appear here."
                                    />
                                </div>
                            )}
                        </>
                    ) : (
                        <TableSkeleton numberOfRows={10} SkeletonHeight={30} />
                    )}
                </div>
            </div>
            {totalPage > 1 && (
                <Pagination
                    baseSlug="orders"
                    current={page}
                    total={totalPage}
                />
            )}
        </>
    );
};

export default Orders;
