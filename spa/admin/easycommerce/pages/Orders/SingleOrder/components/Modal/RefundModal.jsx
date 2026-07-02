import React, { useState } from "react";
import { __ } from '@wordpress/i18n';
import { toast } from "react-toastify";
import Dropdown from '../../../../../../common/components/inputs/Dropdown';

const cross = `${EASYCOMMERCE.assets}admin/img/icons/cross.png`;
const refund = `${EASYCOMMERCE.assets}admin/img/order/refund-payment.png`;

const options = Object.keys(EASYCOMMERCE.refund_reasons).map((key) => {
    return { value: key, label: EASYCOMMERCE.refund_reasons[key] };
});

const currencyIcon = (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="8"
        height="16"
        viewBox="0 0 8 16"
        fill="none"
    >
        <path
            d="M4.88889 7H4.66667V3.5H6.88889C7.25689 3.5 7.55556 3.1645 7.55556 2.75C7.55556 2.3355 7.25689 2 6.88889 2H4.66667V0.75C4.66667 0.3355 4.368 0 4 0C3.632 0 3.33333 0.3355 3.33333 0.75V2H2.88889C1.296 2 0 3.458 0 5.25C0 7.042 1.296 8.5 2.88889 8.5H3.33333V12.5H1.11111C0.743111 12.5 0.444444 12.8355 0.444444 13.25C0.444444 13.6645 0.743111 14 1.11111 14H3.33333V15.25C3.33333 15.6645 3.632 16 4 16C4.368 16 4.66667 15.6645 4.66667 15.25V14H4.88889C6.604 14 8 12.43 8 10.5C8 8.57 6.604 7 4.88889 7ZM2.88889 7C2.03111 7 1.33333 6.215 1.33333 5.25C1.33333 4.285 2.03111 3.5 2.88889 3.5H3.33333V7H2.88889ZM4.88889 12.5H4.66667V8.5H4.88889C5.86933 8.5 6.66667 9.397 6.66667 10.5C6.66667 11.603 5.86933 12.5 4.88889 12.5Z"
            fill="#7F7F98"
        />
    </svg>
);

const RefundModal = ({ hideModal, order, updateOrder }) => {
    const [label, setLabel] = useState("Select an option");
    const [reason, setReason] = useState("");
    const [refundAmount, setRefundAmount] = useState(null);
    const [refundTxnID, setRefundTxnID] = useState("");

    const supportsRefund = EASYCOMMERCE.payment_methods[order.transactions?.payment_gateway ?? order.payment_method]?.support_refund;

    const handleRefund = (e) => {
        e.preventDefault();

        if (!reason) {
            toast.error(`Please select a reason before refunding`);
            return;
        }

        if (!refundAmount) {
            toast.error(`Please enter an amount before refunding`);
            return;
        } else if (parseFloat(refundAmount) <= 0) {
            toast.error(
                `Please enter an amount greater than 0 before refunding`
            );
            return;
        }

        if (!supportsRefund && refundTxnID.trim() === '') {
            toast.error(`Please enter the Refund Transaction ID.`);
            return;
        }

        easycommerce_modal(true);

        fetch(`${EASYCOMMERCE.rest_base}/refunds`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify({
                order_id: order.id,
                reason: reason,
                amount: parseFloat(refundAmount),
                transaction_id: refundTxnID || '',
                payment_gateway: order.transactions?.payment_gateway,
            }),
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success) {
                    toast.success(data.data?.message);
                    const refundableAmount = currencyToNumber(order.available_for_refund);
                    const newAvailableForRefund = Math.max(0, refundableAmount - parseFloat(refundAmount));
                    const newRefundedTotal = currencyToNumber(order.refunded_total) + parseFloat(refundAmount);
                    const newStatus = newAvailableForRefund <= 0 ? "refunded" : "partially_refunded";
                    updateOrder({
                        ...order,
                        status: newStatus,
                        available_for_refund: newAvailableForRefund.toFixed(2),
                        refunded_total: newRefundedTotal.toFixed(2),
                    });

                    hideModal();
                } else {
                    toast.error(data.data.message || __('Failed to process refund. Please try again.', 'easycommerce'));
                }

                easycommerce_modal(false);
            });
    };

    const currencyToNumber = (value) => {
        if (!value) return 0;
        return parseFloat(String(value).replace(/[^0-9.-]/g, '')) || 0;
    };

    return (
        <>
            <div className="w-full top-0 left-0 h-lvh bg-[#00000082] fixed z-20">
                <div
                    className="absolute top-1/2 left-1/2 bg-white w-[530px] 
                    h-max -translate-y-1/2 -translate-x-1/2 rounded-xl"
                >
                    <button
                        className="group absolute w-[24px] h-[24px] top-[-20px] right-[-20px] bg-white rounded-full hover:bg-[#FF3A52] flex items-center justify-center transition-colors duration-200"
                        onClick={hideModal}
                    >
                        <img src={cross} className="w-[9px] h-auto" />
                    </button>
                    <form className="p-10" onSubmit={handleRefund}>
                        <div className="text-center mb-5">
                            <h2 className="text-ec-body font-semibold text-2xl leading-8 font-inter">
                                {__("Refund Payment", "easycommerce")}
                            </h2>
                        </div>
                        <div className="flex flex-col mb-5 border border-ec-table-stock rounded-lg overflow-hidden">
                            <div className="grid grid-cols-2 border-b border-ec-table-stock p-3">
                                <p className="text-sm text-ec-body">
                                    {__("Order Total", "easycommerce")}
                                </p>
                                <p className="text-ec-title text-sm justify-self-end">
                                    {order.total}
                                </p>
                            </div>
                            <div className="grid grid-cols-2 border-b border-ec-table-stock p-3">
                                <p className="text-sm text-ec-body">
                                    {__("Refunded", "easycommerce")}
                                </p>
                                <p className="text-ec-title text-sm justify-self-end">
                                    {order.refunded_total}
                                </p>
                            </div>
                            <div className="grid grid-cols-2 p-3">
                                <p className="text-sm text-ec-body">
                                    {__("Refundable", "easycommerce")}
                                </p>
                                <p className="text-ec-title text-sm justify-self-end">
                                    {order.available_for_refund}
                                </p>
                            </div>
                        </div>
                        <div>
                            <div className="mb-4">
                                <label
                                    htmlFor=""
                                    className="text-ec-body inline-block font-inter font-medium text-base leading-[26px] mb-2"
                                >
                                    {__("Refund Amount", "easycommerce")}
                                </label>
                                <div className="h-ec-input rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus-within:border-ec-primary focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out overflow-hidden flex ">
                                    <div className="h-full w-[50px] flex items-center justify-center border-r border-ec-table-stock bg-[#F9F9F9]">
                                        {currencyIcon}
                                    </div>
                                    <input
                                        type="number"
                                        name={"refundAmount"}
                                        className="w-[calc(100%_-_50px)] h-full border-none outline-none shadow-none"
                                        placeholder={__('Enter amount', 'easycommerce')}
                                        step={0.01}
                                        value={refundAmount}
                                        max={currencyToNumber(order.available_for_refund)}
                                        onChange={(e) => {
                                            if (e.target.value > currencyToNumber(order.available_for_refund)) {
                                                setRefundAmount(currencyToNumber(order.available_for_refund));
                                            } else {
                                                setRefundAmount(e.target.value);
                                            }
                                        }}
                                        min={0}
                                    />
                                </div>
                            </div>
                            <div className="mb-4 flex flex-col">
                                <label
                                    htmlFor="orderRefund"
                                    className="text-ec-body inline-block font-inter font-medium text-base leading-[26px] mb-2"
                                >
                                    {__("Refund Reason", "easycommerce")}
                                </label>
                                <div className="h-[48px]">
                                    <Dropdown
                                        placeholder={label || __('Select Refund Reason', 'easycommerce')}
                                        options={options}
                                        value={reason}
                                        setStatus={(val) => {
                                            setLabel(options.find(opt => opt.value === val)?.label || '');
                                            setReason(val);
                                        }}
                                        onChange={(selected) => {
                                            setLabel(selected.label);
                                            setReason(selected.value);
                                        }}
                                    />
                                </div>
                            </div>

                            {!supportsRefund && (
                                <div className="mt-2 mb-8">
                                    <p className="text-ec-body font-inter font-normal text-sm leading-[22px] mb-3">
                                        {__(`For ${EASYCOMMERCE.payment_methods[order.transactions?.payment_gateway ?? order.payment_method]?.title ?? order.payment_method}, refunds should be processed manually outside of the system. After succesful processing, enter the Refund Transaction ID`, 'easycommerce')}
                                    </p>

                                    <div className="h-ec-input rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus-within:border-ec-primary focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out overflow-hidden flex ">
                                        <input 
                                            className="w-[calc(100%_-_50px)] h-full border-none outline-none shadow-none"
                                            type="text"
                                            placeholder={__('Enter Refund Transaction ID', 'easycommerce')}
                                            value={refundTxnID}
                                            onChange={(e) =>
                                                setRefundTxnID(e.target.value)
                                            }
                                        />
                                    </div>
                                </div>
                            )}
                        </div>
                        <div className="flex items-center justify-between gap-4">
                            <button
                                className="p-[10px] rounded-lg text-ec-primary border border-ec-primary font-inter font-medium 
                                text-base leading-[26px] w-[224px] bg-white hover:bg-ec-primary hover:text-white transition-all 
								ease-in-out duration-500"
                                onClick={hideModal}
                            >
                                Cancel
                            </button>
                            <button
                                className="p-[10px] rounded-lg text-white border border-ec-primary font-inter font-medium text-base
                                leading-[26px] w-[224px] bg-ec-primary hover:bg-ec-secondary transition-all ease-in-out duration-500"
                                type="submit"
                            >
                                Refund now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
};

export default RefundModal;
