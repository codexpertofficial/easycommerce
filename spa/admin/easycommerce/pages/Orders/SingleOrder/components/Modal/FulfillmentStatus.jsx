import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import { useDispatch } from "react-redux";
import { addToastData } from "../../../../../redux-store/slices/toastSlice";

const cross = `${EASYCOMMERCE.assets}admin/img/icons/cross.png`;
const FulfillmentStatusTop = `${EASYCOMMERCE.assets}admin/img/icons/ChangeStatusTop.png`;
const arrowDown = `${EASYCOMMERCE.assets}admin/img/icons/arrowDown.png`;

const options = Object.keys(EASYCOMMERCE.fulfill_statuses).map((key) => {
    return {
        value: key,
        label: EASYCOMMERCE.fulfill_statuses[key],
    };
});

const FulfillmentStatus = ({ hideModal, order, updateOrder }) => {
    const dispatch = useDispatch();
    const [isOpen, setIsOpen] = useState(false);
    const [label, setLabel] = useState(
        options.find((option) => option.value === order.fulfill_status).label
    );
    const [selectedStatus, setSelectedStatus] = useState(order.fulfill_status);

    const handleOptionClick = (option) => {
        setLabel(option.label);
        setSelectedStatus(option.value);

        setIsOpen(false);
    };

    const handleFulfillmentStatus = () => {
        if (!selectedStatus) {
            dispatch(
                addToastData({
                    type: "error",
                    message: __("Please select a status", "easycommerce"),
                })
            );

            return;
        }

        easycommerce_modal(true);

        fetch(
            `${EASYCOMMERCE.rest_base}/orders/${order.id}?fulfill_status=${selectedStatus}`,
            {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-WP-Nonce": EASYCOMMERCE.nonce,
                }
            }
        )
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success && data.data?.order_id) {
                    dispatch(
                        addToastData({
                            type: "success",
                            message: data.data?.message,
                        })
                    );

                    updateOrder({ ...order, fulfill_status: selectedStatus });
                    hideModal();
                } else {
                    dispatch(
                        addToastData({
                            type: "error",
                            message: data.data,
                        })
                    );
                }
            });
    };

    return (
        <>
            <div className="w-full top-0 left-0 h-lvh bg-[#00000082] fixed">
                <div
                    className="absolute top-1/2 left-1/2 bg-white w-[530px] 
                    min-h-[460px] -translate-y-1/2 -translate-x-1/2 rounded-xl"
                >
                    <div className="flex justify-end">
                        <button
                            className="mt-2 mr-2 border border-ec-border bg-[#F8F8F8] w-6 h-6
                            flex items-center justify-center rounded-full"
                            onClick={hideModal}
                        >
                            <img src={cross} className="w-[9px] h-auto" />
                        </button>
                    </div>
                    <div className="p-10">
                        <div className="text-center">
                            <img
                                src={FulfillmentStatusTop}
                                className="mx-auto  mb-5"
                            />
                            <h2 className="text-ec-body font-semibold text-2xl leading-8 font-inter">
                                {__("Change Fulfillment Status", "easycommerce")}
                            </h2>
                        </div>
                        <div>
                            <div className="mb-4 flex flex-col">
                                <label
                                    htmlFor="orderRefund"
                                    className="text-ec-body inline-block font-inter font-medium text-base leading-[26px] mb-2"
                                >
                                    {__("Status", "easycommerce")}*
                                </label>
                                <div className="relative flex items-center justify-center">
                                    <button
                                        type="button"
                                        onClick={() => setIsOpen(!isOpen)}
                                        onBlur={() => setIsOpen(false)}
                                        className="h-12 w-full rounded-md text-base font-inter text-ec-secondary focus:text-ec-body 
										bg-white border border-ec-border hover:border-ec-primary text-left pl-4 pr-10 py-2 
										focus:border-ec-primary capitalize transition-all ease-in-out duration-500"
                                    >
                                        {label || __("Select an Option", "easycommerce")}
                                    </button>
                                    {isOpen && (
                                        <ul
                                            className="absolute w-full top-14 p-3 right-0 border bg-white border-ec-border 
                                            rounded-[12px] shadow-2xl h-[300px] overflow-x-scroll"
                                        >
                                            {options.map((option) => (
                                                <li
                                                    key={option.value}
                                                    className={`p-3 ${
                                                        selectedStatus ===
                                                            option.value &&
                                                        "bg-[#F8F8F8] border border-ec-primary"
                                                    } text-base text-ec-body font-normal leading-[26px] hover:bg-[#F8F8F8] 
                                                    cursor-pointer rounded-[4px]`}
                                                    onMouseDown={() =>
                                                        handleOptionClick(
                                                            option
                                                        )
                                                    }
                                                >
                                                    {option.label}
                                                </li>
                                            ))}
                                        </ul>
                                    )}
                                    <img
                                        src={arrowDown}
                                        alt={__("Search Icon", "easycommerce")}
                                        className="easycommerce-select-icon absolute w-3 ml-0 right-3"
                                    />
                                </div>
                            </div>
                        </div>
                        <div className="mt-10 flex items-center justify-between gap-4">
                            <button
                                className="p-[10px] rounded-lg text-ec-primary border border-ec-primary font-inter font-medium 
                                text-base leading-[26px] w-[224px] bg-white hover:bg-ec-primary hover:text-white transition-all 
								ease-in-out duration-500"
                                onClick={hideModal}
                            >
                                {__("Cancel", "easycommerce")}
                            </button>
                            <button
                                className="p-[10px] rounded-lg text-white border border-ec-primary font-inter font-medium text-base
                                leading-[26px] w-[224px] bg-ec-primary hover:bg-ec-secondary transition-all ease-in-out duration-500"
                                onClick={handleFulfillmentStatus}
                            >
                                {__("Update Status", "easycommerce")}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

export default FulfillmentStatus;
