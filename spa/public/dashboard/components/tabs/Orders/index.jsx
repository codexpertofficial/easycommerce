import React, { useEffect, useState } from 'react';
import TableSkeleton from '../../../../../admin/common/TableSkeleton';
import Pagination from '../../../../../admin/common/components/Pagination';

const viewIcon = `${EASYCOMMERCE.assets}public/img/icons/dashboard-view-icon.png`;

const statusColors = {
    pending: { color: '#F68D2B', bgColor: '#F68D2B0D' },
    processing: { color: '#344BFD', bgColor: '#009D680D' },
    cancelled: { color: '#EF4444', bgColor: '#EF44440D' },
    completed: { color: '#009D68', bgColor: '#344BFD0D' },
    on_hold: { color: '#555DFF', bgColor: '#555DFF1A' },
    partially_refunded: { color: '#F89102', bgColor: '#F891021A' },
    refunded: { color: '#FF001F', bgColor: '#FF001F1A' },
};

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
                                <table className="w-full border-none m-0">
                                    <thead className="easycommerce-dash-roth">
                                        <tr className="h-[42px]">
                                            <th className="font-inter font-medium text-left rtl:text-right rtl:pr-4 text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0 pl-0">
                                                Order ID
                                            </th>
                                            <th className="font-inter font-medium text-left rtl:text-right text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0">
                                                Date
                                            </th>
                                            <th className="font-inter font-medium text-left rtl:text-right text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0">
                                                Amount
                                            </th>
                                            <th className="font-inter font-medium text-left rtl:text-right text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0">
                                                Status
                                            </th>
                                            <th className="font-inter font-medium text-left rtl:text-right text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody className="easycommerce-dash-roth">
                                        {orders.map((order, index) => {
                                            const date =
                                                order.created_at.split(' ')[0];
                                            const status =
                                                order.status
                                                    .charAt(0)
                                                    .toUpperCase() +
                                                order.status.slice(1);
                                            const statusColor =
                                                statusColors[order.status]?.color ||
                                                '#000';
                                            const statusBgColor =
                                                statusColors[order.status]
                                                    ?.bgColor || '#0000';

                                            return (
                                                <tr
                                                    key={index}
                                                    className={`h-[76px] ${
                                                        index === orders.length - 1
                                                            ? 'last-row'
                                                            : ''
                                                    }`}
                                                >
                                                    <td className="font-inter font-normal text-left rtl:text-right rtl:pr-4 text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0 pl-0">
                                                        #{order.id}
                                                    </td>
                                                    <td className="font-inter font-normal text-left rtl:text-right text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0">
                                                        <span className="flex flex-col justify-start items-start">
                                                            <span>
                                                                {order.created_at
                                                                    ? order.created_at
                                                                    : 'N/A'}
                                                            </span>
                                                            <span className="text-sm leading-4 text-ec-placeholder">
                                                                {order.created_time
                                                                    ? order.created_time
                                                                    : 'N/A'}
                                                            </span>
                                                        </span>
                                                    </td>
                                                    <td className="font-inter font-normal text-left rtl:text-right text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0">
                                                        {
                                                            EASYCOMMERCE.currency_symbol
                                                        }
                                                        {order.total}
                                                    </td>
                                                    <td className="font-inter font-normal text-left rtl:text-right text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0">
                                                        <span
                                                            className="p-[3px] rounded-[4px] text-sm leading-4 font-medium"
                                                            style={{
                                                                color: statusColor,
                                                                backgroundColor:
                                                                    statusBgColor,
                                                            }}
                                                        >
                                                            {EASYCOMMERCE.order_statuses?.[order.status] ?? order.status}
                                                        </span>
                                                    </td>
                                                    <td className="font-inter font-normal text-left rtl:text-right text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0">
                                                        <a
                                                            href={`#orders/${order.id}`}
                                                        >
                                                            <img
                                                                src={viewIcon}
                                                                alt="view-icon"
                                                                className="w-[42px] h-6 pointer-events-none"
                                                            />
                                                        </a>
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            ) : (
                                <div>No orders available</div>
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
