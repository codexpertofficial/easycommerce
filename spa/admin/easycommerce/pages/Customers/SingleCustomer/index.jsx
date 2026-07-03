import React, { useState, useEffect } from "react";

// Components
import CustomerSaleSummery from "./components/CustomerSaleSummery";
import CustomerDetails from "./components/CustomerDetails";
import Pagination from "../../../../common/components/Pagination";

// Icons
const paymentMethods = EASYCOMMERCE.payment_methods;

const statusClassMap = {
    completed: {
        bg: "bg-ec-completedBg",
        text: "text-ec-completedText",
        border: "border-ec-completedBorder",
    },
    pending: {
        bg: "bg-ec-pendingBg",
        text: "text-ec-pendingText",
        border: "border-ec-pendingBorder",
    },
    processing: {
        bg: "bg-ec-processingBg",
        text: "text-ec-processingText",
        border: "border-ec-processingBorder",
    },
    partially_refunded: {
        bg: "bg-ec-partiallyRefundedBg",
        text: "text-ec-partiallyRefundedText",
        border: "border-ec-partiallyRefundedBorder",
    },
    refunded: {
        bg: "bg-ec-refundedBg",
        text: "text-ec-refundedText",
        border: "border-ec-refundedBorder",
    },
    cancelled: {
        bg: "bg-ec-cancelledBg",
        text: "text-ec-cancelledText",
        border: "border-ec-cancelledBorder",
    },
    on_hold: {
        bg: "bg-ec-onHoldBg",
        text: "text-ec-onHoldText",
        border: "border-ec-onHoldBorder",
    },
    failed: {
        bg: "bg-ec-failedBg",
        text: "text-ec-failedText",
        border: "border-ec-failedBorder",
    },
};

const fulfill_status_class_map = {
    fulfilled: {
        bg: "bg-ec-fullfillBg",
        text: "text-ec-fullfillText",
        border: "border-ec-fullfillBorder",
    },
    partially_fulfilled: {
        bg: "bg-ec-partiallyFullfilledBg",
        text: "text-ec-partiallyFullfilledText",
        border: "border-ec-partiallyFullfilledBorder",
    },
    shipped: {
        bg: "bg-ec-shippedBg",
        text: "text-ec-shippedText",
        border: "border-ec-shippedBorder",
    },
    returned: {
        bg: "bg-ec-returnedBg",
        text: "text-ec-returnedText",
        border: "border-ec-returnedBorder",
    },
    unfulfilled : {
        bg: "bg-ec-unfullfilledBg",
        text: "text-ec-unfullfilledText",
        border: "border-ec-unfullfilledBorder",
    },
    delivered: {
        bg: "bg-ec-deliveredBg",
        text: "text-ec-deliveredText",
        border: "border-ec-deliveredBorder",
    }
};

const SingleCustomer = ({ id, page = 1, setBreadcrumbTitle }) => {
    const [customer, setCustomer] = useState(null);
    const [totalPage, setTotalPage] = useState();
    const [postPerPage, setPostPerPage] = useState(20);
    const [customerId, setCustomerId] = useState(id || null);
    const [currentPage, setCurrentPage] = useState(page || 1);
    const [orders, setOrders] = useState([]);

    useEffect(() => {
        const handleHashChange = () => {
            const hash = window.location.hash;
            let match = hash.match(/customers\/(\d+)\/page\/(\d+)/);

            if (match) {
                const newCustomerId = match[1];
                const newPage = parseInt(match[2], 10);

                setCustomerId(newCustomerId);
                setCurrentPage(newPage);
            } else {
                match = hash.match(/customers\/(\d+)/);
                if (match) {
                    const newCustomerId = match[1];
                    setCustomerId(newCustomerId);
                    setCurrentPage(1);
                }
            }
        };

        window.addEventListener("hashchange", handleHashChange);

        handleHashChange();

        return () => window.removeEventListener("hashchange", handleHashChange);
    }, []);

    useEffect(() => {
        if (!customerId) return;

        easycommerce_modal(true);
        fetch(`${EASYCOMMERCE.rest_base}/orders?page=${currentPage}&per_page=${postPerPage}&customer_id=${customerId}`, {
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            }
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success && Array.isArray(data.data?.orders)) {
                    setOrders(data.data.orders);
                    setTotalPage(data.data.total_pages);
                } else {
                    setTotalPage(1);
                }

                easycommerce_modal(false);
            });
    }, [customerId, currentPage, postPerPage]);

    useEffect(() => {
        if (!customerId) return;

        easycommerce_modal(true);
        fetch(`${EASYCOMMERCE.rest_base}/customers/${customerId}`, {
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success && data.data.customer) {
                    setCustomer(data.data.customer);
                    setBreadcrumbTitle(data.data.customer.name);
                }
                easycommerce_modal(false);
            });
    }, [customerId]);

    if (!customer) return null;

    return (
        <>
            <div className="product-panel-title mb-4">
                {customer && (
                    <>
                        <h3>{customer.name ? customer.name : "unknown"}</h3>
                    </>
                )}
            </div>
            <div className="border border-solid border-ec-table-stock rounded-xl min-h-screen">
                {customer && (
                    <>
                        <div className="flex gap-5">
                            <CustomerDetails customer={customer} />

                            <div className="w-[calc(100%_-_626px)] bg-white rounded-2xl">
                                <div className="border-b border-ec-table-stock">
                                    <p className="text-ec-title font-medium font-inter lg:text-xl md:text-lg leading-8 pb-4 px-6 pt-[17px]">
                                        Orders Over Time
                                    </p>
                                </div>
                                <CustomerSaleSummery id={id} />
                            </div>
                        </div>
                        <div className="bg-white max-w-full mt-6 pt-[17px] rounded-2xl">
                            <div className="border-b border-ec-table-stock">
                                <p className="text-ec-title font-medium font-inter lg:text-xl md:text-lg leading-8 pb-4 px-6">
                                    Orders
                                </p>
                            </div>
                            {orders.length > 0 ? (
                                <div className="px-8 pt-7 pb-8">
                                    <table className="w-full border-collapse overflow-hidden">
                                        <thead>
                                            <tr className="bg-ec-modal">
                                                <th className="font-inter font-normal text-sm text-ec-title text-left py-3 pl-5 rounded-l-lg ">
                                                    <span>Order ID</span>
                                                </th>
                                                <th className="w-[14%] font-inter font-normal text-sm text-ec-title text-left py-3">
                                                    Status
                                                </th>
                                                <th className="w-[15%] font-inter font-normal text-sm text-ec-title text-left py-3 pl-5">
                                                    Fullfillment
                                                </th>
                                                <th className="w-[12%] font-inter font-normal text-sm text-ec-title text-left py-3">
                                                    Products
                                                </th>
                                                <th className="w-[11%] font-inter font-normal text-sm text-ec-title text-left py-3">
                                                    offer
                                                </th>
                                                <th className="w-[22%] font-inter font-normal text-sm text-ec-title text-left py-3">
                                                    Transactions ID
                                                </th>
                                                <th className="w-[12%] font-inter font-normal text-sm text-ec-title text-left py-3 rounded-r-lg">
                                                    Date
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {orders.map((order, index) => {
                                                const statusClasses = statusClassMap[order.status] || {};
                                                const fullfillmentClasses = fulfill_status_class_map[order.fulfill_status] || {};
                                                return (
                                                    <tr
                                                        key={index}
                                                        className="border-b border-ec-table-stock h-[80px] transition-shadow hover:shadow-[0px_4px_40px_0px_#00000014] group"
                                                    >
                                                        <td className="pl-5">
                                                            <div className="flex items-center gap-4 justify-start mt-1 w-full">
                                                                <div className="flex items-center gap-4 focus:shadow-none grow">
                                                                    <div className="block w-full h-10 relative">
                                                                        <span className="text-sm text-ec-body font-inter font-normal absolute top-1/2 -translate-y-1/2 group-hover:top-0 group-hover:translate-y-0 duration-300">
                                                                            {`#${order.id}`}
                                                                        </span>

                                                                        <div className="invisible group-hover:visible opacity-0 group-hover:opacity-100 duration-300 absolute bottom-0">
                                                                            <div className="flex items-center gap-1.5 font-inter font-normal text-xs text-ec-light-black">
                                                                                <a
                                                                                    className="hover:text-ec-primary duration-300 hover:cursor-pointer"
                                                                                    href={`#/orders/${order.id}`}
                                                                                >
                                                                                    View
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span
                                                                className={`inline-block w-[116px] text-center rounded-lg py-[6px] font-inter text-sm font-normal capitalize border
                                                                ${statusClasses.bg || ""}
                                                                ${statusClasses.text || ""}
                                                                ${statusClasses.border || ""}
                                                            `}
                                                            >
                                                                {order.status.replace("_", " ")}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span
                                                                className={`inline-block w-[140px] text-center rounded-lg py-[6px] font-inter text-sm font-normal capitalize border
                                                                ${fullfillmentClasses.bg || ""}
                                                                ${fullfillmentClasses.text || ""}
                                                                ${fullfillmentClasses.border || ""}
                                                            `}
                                                            >
                                                                {order.fulfill_status.replace("_", " ")}
                                                            </span>
                                                        </td>
                                                        <td className="text-ec-body font-inter font-normal text-sm">
                                                            {order.items}
                                                        </td>
                                                        <td>
                                                            <span className="text-ec-body font-inter font-normal text-sm">
                                                                {EASYCOMMERCE.currency_symbol}
                                                                {order.total}
                                                            </span>
                                                        </td>
                                                        <td className="text-sm">
                                                            <div className="flex items-center gap-2">
                                                                {(() => {
                                                                    const txnRaw = order.transactions;
                                                                    const transactions = Array.isArray(txnRaw)
                                                                        ? txnRaw
                                                                        : txnRaw && typeof txnRaw === "object"
                                                                        ? [txnRaw]
                                                                        : [];

                                                                    if (transactions.length === 0) {
                                                                        return (
                                                                            <span className="text-left py-4 font-inter font-normal lg:w-[13%] text-sm text-ec-body">
                                                                                N/A
                                                                            </span>
                                                                        );
                                                                    }

                                                                    const txn = transactions[transactions.length - 1];
                                                                    const paymentGateway = txn.payment_gateway
                                                                        ? txn.payment_gateway.toLowerCase()
                                                                        : "unknown";
                                                                    const paymentMethod = paymentMethods[paymentGateway];
                                                                    const paymentIcon = paymentMethod?.icon;
                                                                    const maxLength = 6;
                                                                    let transactionId = txn.transaction_id || "**** ****";
                                                                    if (transactionId.length > maxLength) {
                                                                        transactionId = `...${transactionId.substring(
                                                                            transactionId.length - maxLength
                                                                        )}`;
                                                                    }

                                                                    return (
                                                                        <>
                                                                            {paymentIcon ? (
                                                                                <img
                                                                                    src={paymentIcon}
                                                                                    className="pointer-events-none object-contain rounded h-[30px] min-w-[54px] p-[3px]"
                                                                                    alt="payment-icon"
                                                                                    style={{ border: "1px solid #f0edfb" }}
                                                                                />
                                                                            ) : (
                                                                                <span className="text-ec-body font-inter text-sm font-normal rounded-[4px] border border-ec-table-stock py-[9px] px-[7px]">
                                                                                    {txn.payment_gateway}
                                                                                </span>
                                                                            )}
                                                                            <p className="text-ec-body font-inter text-sm font-normal">
                                                                                {transactionId && transactionId !== "-"
                                                                                    ? transactionId
                                                                                    : ""}
                                                                            </p>
                                                                        </>
                                                                    );
                                                                })()}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <p className="text-ec-body font-inter font-normal text-sm mb-1">
                                                                {order.created_at ? order.created_at : "N/A"}
                                                            </p>
                                                            <span>
                                                                <p className="text-ec-light-black font-inter font-normal text-xs mb-1">
                                                                    {order.created_time ? order.created_time : "N/A"}
                                                                </p>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                    {totalPage > 1 && (
                                        <Pagination baseSlug={`customers/${customerId}`} current={currentPage} total={totalPage} />
                                    )}
                                </div>
                            ) : (
                                <p className="text-ec-body font-inter font-normal text-sm p-4">
                                    No order found for this customer
                                </p>
                            )
                        }
                        </div>
                    </>
                )}
            </div>
        </>
    );
};

export default SingleCustomer;
