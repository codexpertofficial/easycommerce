import React, { useState, useEffect } from "react";
import TableSkeleton from "../../../../../admin/common/TableSkeleton";
import Pagination from "../../../../../admin/common/components/Pagination";
// import { setCurrentTab } from "./redux-store/slices/currentTab";

const typeColors = {
    payment: {
        color: "#009D68",
        background: "#009D680D",
    },
    refund: {
        color: "#FF3A52",
        background: "#FF3A520D",
    },
    adjustment: {
        color: "#E8A700",
        background: "#E8A7001A",
    },
};
const paymentMethods = EASYCOMMERCE.payment_methods;

const Transactions = () => {
    const [totalPage, setTotalPage] = useState(1);
    const [page, setPage] = useState(1);
    const [postPerPage, setPostPerPage] = useState(10);
    const [transactions, setTransactions] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [copiedTransactionId, setCopiedTransactionId] = useState(null);
    const [hoveredTransactionId, setHoveredTransactionId] = useState(null);

    useEffect(() => {
        const handleClick = (e) => {
            const link = e.target.closest('a[href^="#/transactions/page/"]');
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
        const url = `${EASYCOMMERCE.rest_base}/me/transactions?page=${page}&per_page=${postPerPage}`;
        setIsLoading(true);
        fetch(url, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then((res) => res.json())
            .then((data) => {
                setIsLoading(false);
                if (data.success && data.data?.transactions) {
                    setTotalPage(data.data.total_pages);
                    setTransactions(data.data.transactions);
                }
            });
    }, [page, postPerPage]);

    return (
        <>
            <div className="easycommerce-dashboard-section pb-[55px] flex flex-col gap-4">
                <h3 className="easycommerce-dashboard-section-title !text-lg sm:!text-2xl">
                    Transactions
                </h3>
                <div className="w-full">
                    {!isLoading ? (
                        <>
                            {transactions.length > 0 ? (
                                <table className="w-full border-none m-0">
                                    <thead className="easycommerce-dash-roth">
                                        <tr className="h-[42px]">
                                            <th className="font-inter font-medium text-left rtl:text-right rtl:pr-4 text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0 pl-0">
                                                Order ID
                                            </th>
                                            <th className="font-inter font-medium text-left rtl:text-right text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0">
                                                Amount
                                            </th>
                                            <th className="font-inter font-medium text-left rtl:text-right text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0">
                                                Transaction ID
                                            </th>
                                            <th className="font-inter font-medium text-left rtl:text-right text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0">
                                                Type
                                            </th>
                                            <th className="font-inter font-medium text-left rtl:text-right text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0">
                                                Date
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody className="easycommerce-dash-roth">
                                        {transactions.map((transaction, index) => {
                                            const type = transaction.type
                                                ? transaction.type.toLowerCase()
                                                : "unknown";
                                            const { color, background } =
                                                typeColors[type] || {
                                                    color: "#000",
                                                    background: "#fff",
                                                };

                                            const orderId =
                                                transaction.order_id || "N/A";
                                            const amount =
                                                transaction.amount || "$0.00";
                                            const transactionId =
                                                transaction.transaction_id ||
                                                "**** ****";

                                            const paymentGateway = transaction.payment_gateway
                                                ? transaction.payment_gateway
                                                : "unknown";
                                            const paymentMethod = paymentMethods[paymentGateway];

                                            const paymentIcon = paymentMethod?.icon;

                                            return (
                                                <tr
                                                    key={transaction.id}
                                                    className={`h-[76px] ${
                                                        index ===
                                                        transactions.length - 1
                                                            ? "last-row"
                                                            : ""
                                                    }`}>
                                                    <td className="font-inter font-normal text-left rtl:text-right rtl:pr-4 text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0 pl-0">
                                                        #{orderId}
                                                    </td>
                                                    <td className="font-inter font-normal text-left rtl:text-right text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0">
                                                        {amount}
                                                    </td>
                                                    <td className="font-inter font-normal text-left rtl:text-right text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0">
                                                        <div className="flex h-10 w-10 items-center gap-2">
                                                            {paymentIcon ? (
                                                                <img
                                                                    src={paymentIcon}
                                                                    alt="card-icon"
                                                                    className="w-[65px] h-[40px] pointer-events-none object-contain"
                                                                />
                                                            ) : (
                                                                <span className="text-ec-body font-semibold font-inter text-base border border-[#ffeeee] p-[9px]">
                                                                    {transaction.payment_gateway}
                                                                </span>
                                                            )}
                                                            {transactionId && transactionId !== "-" && (
                                                                <div 
                                                                    className="relative" 
                                                                    onMouseEnter={() => setHoveredTransactionId(transaction.id)} 
                                                                    onMouseLeave={() => setHoveredTransactionId(null)}>
                                                                    <p
                                                                        className="text-ec-body font-inter text-base font-normal leading-[26px] cursor-pointer"
                                                                        onClick={() => handleCopy(transactionId, transaction.id)}
                                                                        title="Click to copy"
                                                                    >
                                                                        {transactionId.length > 15
                                                                            ? `${transactionId.substring(0, 15)}...`
                                                                            : transactionId}
                                                                    </p>
                                                                    {hoveredTransactionId === transaction.id && copiedTransactionId !== transaction.id && (
                                                                        <span className="absolute text-sm text-ec-placeholder top-[-15px] left-[30px]">
                                                                            Copy
                                                                        </span>
                                                                    )}
                                                                    {copiedTransactionId === transaction.id && (
                                                                        <span className="absolute text-sm text-ec-placeholder top-[-15px] left-[30px]">
                                                                            Copied!
                                                                        </span>
                                                                    )}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="font-inter font-normal text-left rtl:text-right text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0">
                                                        <span
                                                            className="border rounded-[5px] font-inter font-normal text-base leading-[26px] py-[3px] px-[10px]"
                                                            style={{
                                                                color: color,
                                                                backgroundColor:
                                                                    background,
                                                                borderColor:
                                                                    background,
                                                            }}>
                                                            {transaction.type
                                                                ? transaction.type
                                                                    .charAt(0)
                                                                    .toUpperCase() +
                                                                transaction.type.slice(
                                                                    1
                                                                )
                                                                : "Pending"}
                                                        </span>
                                                    </td>
                                                    <td className="font-inter font-normal text-left rtl:text-right text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0">
                                                        <span className="flex flex-col justify-start items-start">
                                                            <span>
                                                                {transaction.created_at ? transaction.created_at : "N/A"}
                                                            </span>
                                                            <span className="text-sm leading-4 text-ec-placeholder">
                                                                {transaction.created_time ? transaction.created_time : "N/A"}
                                                            </span>
                                                        </span>
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            ) : (
                                <div>No transactions available</div>
                            )}
                                                    
                        </>
                    ) : (
                        <TableSkeleton numberOfRows={10} SkeletonHeight={30} />
                    )}
                </div>
            </div>
            {totalPage > 1 && (
                <Pagination
                    baseSlug="transactions"
                    current={page}
                    total={totalPage}
                />
            )}
        </> 
    );
};

export default Transactions;
