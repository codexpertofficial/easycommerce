import React, { useState, useEffect } from "react";
import { __ } from "@wordpress/i18n";
import TableSkeleton from "../../../../../admin/common/TableSkeleton";
import Pagination from "../../../../../admin/common/components/Pagination";
import EmptyState from "../../common/EmptyState";
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

    const handleCopy = (value, id) => {
        if (!value) return;

        const done = () => {
            setCopiedTransactionId(id);
            setTimeout(() => setCopiedTransactionId(null), 2000);
        };

        if (navigator.clipboard?.writeText) {
            navigator.clipboard.writeText(value).then(done).catch(done);
        } else {
            done();
        }
    };

    return (
        <>
            <div className="easycommerce-dashboard-section pb-[55px] flex flex-col gap-4">
                <h3 className="easycommerce-dashboard-section-title !text-lg sm:!text-2xl">
                    {__( "Transactions", "easycommerce" )}
                </h3>
                <div className="w-full">
                    {!isLoading ? (
                        <>
                            {transactions.length > 0 ? (
                                <div className="w-full border border-ec-border rounded-2xl overflow-x-auto bg-white">
                                    <table className="w-full min-w-[560px] border-none m-0">
                                        <thead className="easycommerce-dash-roth bg-ec-table-bg">
                                            <tr>
                                                <th className="font-inter font-semibold text-xs uppercase tracking-wide text-left rtl:text-right text-ec-light-black py-3.5 px-5 border-0">
                                                    {__( "Order ID", "easycommerce" )}
                                                </th>
                                                <th className="font-inter font-semibold text-xs uppercase tracking-wide text-left rtl:text-right text-ec-light-black py-3.5 px-5 border-0">
                                                    {__( "Amount", "easycommerce" )}
                                                </th>
                                                <th className="font-inter font-semibold text-xs uppercase tracking-wide text-left rtl:text-right text-ec-light-black py-3.5 px-5 border-0">
                                                    {__( "Transaction ID", "easycommerce" )}
                                                </th>
                                                <th className="font-inter font-semibold text-xs uppercase tracking-wide text-left rtl:text-right text-ec-light-black py-3.5 px-5 border-0">
                                                    {__( "Type", "easycommerce" )}
                                                </th>
                                                <th className="font-inter font-semibold text-xs uppercase tracking-wide text-left rtl:text-right text-ec-light-black py-3.5 px-5 border-0">
                                                    {__( "Date", "easycommerce" )}
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
                                                    transaction.order_id || __( "N/A", "easycommerce" );
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
                                                        className={`transition-colors duration-150 hover:bg-ec-active ${
                                                            index ===
                                                            transactions.length - 1
                                                                ? "last-row"
                                                                : ""
                                                        }`}>
                                                        <td className="font-inter font-semibold text-sm text-left rtl:text-right text-ec-title py-4 px-5 border-0 border-b border-b-ec-border/70">
                                                            #{orderId}
                                                        </td>
                                                        <td className="font-inter font-semibold text-sm text-left rtl:text-right text-ec-title py-4 px-5 border-0 border-b border-b-ec-border/70">
                                                            {amount}
                                                        </td>
                                                        <td className="font-inter text-sm text-left rtl:text-right text-ec-body py-4 px-5 border-0 border-b border-b-ec-border/70">
                                                            <div className="flex items-center gap-3">
                                                                {paymentIcon ? (
                                                                    <img
                                                                        src={paymentIcon}
                                                                        alt={__( "card-icon", "easycommerce" )}
                                                                        className="w-[52px] h-[32px] pointer-events-none object-contain"
                                                                    />
                                                                ) : (
                                                                    <span className="text-ec-body font-medium font-inter text-xs rounded-md border border-ec-border px-2 py-1 capitalize">
                                                                        {transaction.payment_gateway}
                                                                    </span>
                                                                )}
                                                                {transactionId && transactionId !== "-" && (
                                                                    <div
                                                                        className="relative"
                                                                        onMouseEnter={() => setHoveredTransactionId(transaction.id)}
                                                                        onMouseLeave={() => setHoveredTransactionId(null)}>
                                                                        <p
                                                                            className="text-ec-body font-inter text-sm font-normal leading-[26px] cursor-pointer inline-flex items-center gap-1.5 hover:text-ec-primary transition-colors"
                                                                            onClick={() => handleCopy(transactionId, transaction.id)}
                                                                            title={__( "Click to copy", "easycommerce" )}
                                                                        >
                                                                            {transactionId.length > 15
                                                                                ? `${transactionId.substring(0, 15)}...`
                                                                                : transactionId}
                                                                            <svg className="w-3.5 h-3.5 opacity-60" data-slot="icon" fill="none" stroke="currentColor" strokeWidth="1.6" viewBox="0 0 24 24">
                                                                                <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                                                                            </svg>
                                                                        </p>
                                                                        {hoveredTransactionId === transaction.id && copiedTransactionId !== transaction.id && (
                                                                            <span className="absolute text-xs text-ec-placeholder -top-4 left-2">
                                                                                {__( "Copy", "easycommerce" )}
                                                                            </span>
                                                                        )}
                                                                        {copiedTransactionId === transaction.id && (
                                                                            <span className="absolute text-xs text-ec-green -top-4 left-2">
                                                                                {__( "Copied!", "easycommerce" )}
                                                                            </span>
                                                                        )}
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </td>
                                                        <td className="font-inter text-sm text-left rtl:text-right text-ec-body py-4 px-5 border-0 border-b border-b-ec-border/70">
                                                            <span
                                                                className="inline-flex items-center rounded-full font-inter font-semibold text-xs leading-4 py-1 px-3 capitalize"
                                                                style={{
                                                                    color: color,
                                                                    backgroundColor:
                                                                        background,
                                                                }}>
                                                                {transaction.type
                                                                    ? transaction.type
                                                                        .charAt(0)
                                                                        .toUpperCase() +
                                                                    transaction.type.slice(
                                                                        1
                                                                    )
                                                                    : __( "Pending", "easycommerce" )}
                                                            </span>
                                                        </td>
                                                        <td className="font-inter text-sm text-left rtl:text-right text-ec-body py-4 px-5 border-0 border-b border-b-ec-border/70">
                                                            <span className="flex flex-col justify-start items-start">
                                                                <span className="font-medium">
                                                                    {transaction.created_at ? transaction.created_at : __( "N/A", "easycommerce" )}
                                                                </span>
                                                                <span className="text-xs leading-4 text-ec-placeholder">
                                                                    {transaction.created_time ? transaction.created_time : __( "N/A", "easycommerce" )}
                                                                </span>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <div className="w-full border border-ec-border rounded-2xl bg-white">
                                    <EmptyState
                                        title={__( "No transactions available", "easycommerce" )}
                                        message={__( "Your payment history will appear here.", "easycommerce" )}
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
                    baseSlug="transactions"
                    current={page}
                    total={totalPage}
                />
            )}
        </> 
    );
};

export default Transactions;
