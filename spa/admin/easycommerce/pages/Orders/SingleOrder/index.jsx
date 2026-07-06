import { useState, useEffect, useCallback } from "react";
import { __ } from '@wordpress/i18n';
import { toast } from 'react-toastify';

import "./style.css";

// Components
import SendEmail from "./components/Modal/SendEmail";
import Button from "../../../../common/components/inputs/Button";
import OrderActions from "../components/OrderActions";
import THead from "./components/Table/THead";
import TBody from "./components/Table/TBody";
import TFoot from "./components/Table/TFoot";
import OrderInfo from "./components/OrderInfo";
import CustomerInfo from "./components/CustomerInfo";
import CustomerAddress from "./components/CustomerAddress";
import Refunds from "./components/Refunds";
import Notes from "./components/Notes";
import SlotField from "../../../../common/components/SlotField";

const SingleOrder = ({ id }) => {
    const [order, setOrder] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [statusCounts, setStatusCounts] = useState({});
    const [isSendEmailOpen, setisSendEmailOpen] = useState(false);

    const fetchOrderData = useCallback(async () => {
        try {
            easycommerce_modal(true);

            const response = await fetch(`${EASYCOMMERCE.rest_base}/orders/${id}`,{
                headers: {
                    "Content-Type": "application/json",
                    "X-WP-Nonce": EASYCOMMERCE.nonce,
                }
            });

            const data = await response.json();
			easycommerce_modal(false);

            if (data.success) {
                setOrder(data.data);
                setIsLoading(false);
            } else {
                toast.error("Order doesn't exist");
                window.location.hash = `#/orders`;
            }
        } catch (error) {
            easycommerce_modal(false);
            toast.error('Failed to fetch order data');
            console.error('Error fetching order data:', error);
        }
    }, [id]);

    useEffect(() => {
        fetchOrderData();
    }, [id]);

    return (
        <>
        {!isLoading && order && (
            <>
                <div className="product-panel-title flex font-inter items-center justify-between mb-4">
                    <div className="product-panel-title">
                        <h3>Order {`#${order.id}`}</h3>
                    </div>
                    <div class="flex items-center justify-between gap-3 w-max">
                        <OrderActions order={order} setOrder={setOrder} />
                        
                        <Button
                            className="px-[22px] h-[41px] rounded-lg text-white border border-ec-primary font-inter font-medium 
                            text-base eading-[26px] bg-ec-primary hover:bg-ec-secondary transition-all ease-in-out duration-500"
                            value={__("Send Email", "easycommerce")}
                            onClick={(e) => {
                                e.preventDefault();
                                setisSendEmailOpen(true);
                            }}
                        />
                    </div>
                </div>
                <div className="border border-solid border-ec-table-stock rounded-xl min-h-screen">
                    <div className="flex justify-between gap-5">
                        <div className="flex flex-col w-full">
                            <OrderInfo
                                order={order}
                                setStatusCounts={setStatusCounts}
                                updateOrder={setOrder}
                            />

                            <div className="mt-6 bg-white rounded-2xl">
                                <div className="flex flex-col border-b border--ec-table-stock pt-4 px-6">
                                    <h3 className="text-ec-title text-xl font-medium font-inter leading-8 pb-4">
                                        Items
                                    </h3>
                                </div>
                                <div className="p-5">
                                    <table className="overflow-scroll border-collapse border-spacing-0 w-full m-0">
                                        <THead />

                                        <TBody order={order} />

                                        <TFoot total={order.total} />
                                    </table>
                                </div>
                            </div>

                            <SlotField
                                name="easycommerce.order.details.after_items"
                                item={order}
                            />

                            {(() => {
                                const refundedTotal = parseFloat(order.refunded_total.replace(/[^0-9.]/g, ""))

                                if (refundedTotal > 0) {
                                    return <Refunds orderId={order.id} />
                                }
                            })()}
                        </div>
                        <div className="w-[550px] flex flex-col gap-5">
                            <CustomerInfo customer={order.customer} />
                            <CustomerAddress order={order} />
                            <Notes orderId={order.id} />
                        </div>
                    </div>                    
                    {isSendEmailOpen &&
                        <SendEmail
                            hideModal={() => setisSendEmailOpen(false)}
                            order={order}
                        />
                    }
                </div>
            </>
        )}
        </>
    );
};

export default SingleOrder;
