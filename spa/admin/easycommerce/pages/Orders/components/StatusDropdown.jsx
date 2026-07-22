import React, { useState, useEffect } from "react";
import { __ } from "@wordpress/i18n";
import globalToast from "../../../../common/components/globalToast";

const StatusDropdown = ({
    options,
    value,
    placeholder,
    width = "125px",
    menuWidth = "",
    orderId,
    prevStatus,
    onStatusChange,
    onBeforeStatusChange,
}) => {
    const statusColors = {
        completed: {
            color: "var(--color-ec-completedText)",
            background: "var(--color-ec-completedBg)",
            border: "1px solid var(--color-ec-completedBorder)",
        },
        cancelled: {
            color: "var(--color-ec-cancelledText)",
            background: "var(--color-ec-cancelledBg)",
            border: "1px solid var(--color-ec-cancelledBorder)",
        },
        partially_refunded: {
            color: "var(--color-ec-partiallyRefundedText)",
            background: "var(--color-ec-partiallyRefundedBg)",
            border: "1px solid var(--color-ec-partiallyRefundedBorder)",
        },
        refunded: {
            color: "var(--color-ec-refundedText)",
            background: "var(--color-ec-refundedBg)",
            border: "1px solid var(--color-ec-refundedBorder)",
        },
        pending: {
            color: "var(--color-ec-pendingText)",
            background: "var(--color-ec-pendingBg)",
            border: "1px solid var(--color-ec-pendingBorder)",
        },
        on_hold: {
            color: "var(--color-ec-onHoldText)",
            background: "var(--color-ec-onHoldBg)",
            border: "1px solid var(--color-ec-onHoldBorder)",
        },
        failed: {
            color: "var(--color-ec-failedText)",
            background: "var(--color-ec-failedBg)",
            border: "1px solid var(--color-ec-failedBorder)",
        },
        processing: {
            color: "var(--color-ec-processingText)",
            background: "var(--color-ec-processingBg)",
            border: "1px solid var(--color-ec-processingBorder)",
        },
    };

    const [isOpen, setIsOpen] = useState(false);
    const [selectedLabel, setSelectedLabel] = useState("");
    const [selectedKey, setSelectedKey] = useState("");
    const { addToastData } = globalToast();

    useEffect(() => {
        const matchedOption = options.find(opt => opt.value === value);
        if (matchedOption) {
            setSelectedLabel(matchedOption.label);
            setSelectedKey(matchedOption.value);
        }
    }, [value, options]);

    const handleOptionClick = (option) => {
        setIsOpen(false);

        if (typeof onBeforeStatusChange === "function") {
            const handled = onBeforeStatusChange(option.value);
            if (handled) {
                return;
            }
        }

        setSelectedLabel(option.label);
        setSelectedKey(option.value);

        easycommerce_modal(true);

        fetch(`${EASYCOMMERCE.rest_base}/orders/${orderId}?status=${option.value}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
        .then(res => res.json())
        .then(data => {
            easycommerce_modal(false);
            if (data.success && data.data?.order_id) {
                addToastData({
                    type: "success",
                    message: data.data.message,
                });
                if (typeof onStatusChange === "function") {
                    onStatusChange(prevStatus, option.value);
                }
            } else {
                addToastData({
                    type: "error",
                    message: data.data || __("Failed to update status", "easycommerce"),
                });
            }
        })
        .catch(() => {
            easycommerce_modal(false);
            addToastData({
                type: "error",
                message: __("An error occurred while updating status", "easycommerce"),
            });
        });
    };

    return (
        <div
            className="relative flex items-center justify-start rounded-lg"
            style={{
                color: statusColors[selectedKey]?.color,
                background: statusColors[selectedKey]?.background,
                border: statusColors[selectedKey]?.border,
            }}
        >
            <button
                type="button"
                onClick={() => setIsOpen(!isOpen)}
                onBlur={() => setIsOpen(false)}
                className="easycommerce-dropdown-field h-[32px] text-sm font-inter text-left pl-4 pr-10 transition-all ease-in-out duration-500 capitalize"
            >
                {selectedLabel || placeholder}
            </button>

            {isOpen && (
                <ul
                    className="absolute top-10 p-3 right-[-18px] border bg-white border-ec-border rounded-[12px] shadow-2xl z-[99]"
                    style={{ width: menuWidth }}
                >
                    {options.map((option) => (
                        <li
                            key={option.value}
                            className="px-4 py-2 text-sm font-normal text-ec-body leading-[26px] hover:bg-ec-modal cursor-pointer rounded-[4px]"
                            onMouseDown={() => handleOptionClick(option)}
                        >
                            {option.label}
                        </li>
                    ))}
                </ul>
            )}

            <svg
                className={`easycommerce-select-icon absolute w-3 right-3 transition-transform duration-300 ${
                    isOpen ? "rotate-180" : "rotate-0"
                }`}
                xmlns="http://www.w3.org/2000/svg"
                width="11"
                height="6"
                viewBox="0 0 11 6"
                fill="none"
            >
                <path
                    d="M9.87109 1.71094L5.71484 5.62109C5.56901 5.7487 5.41406 5.8125 5.25 5.8125C5.08594 5.8125 4.9401 5.7487 4.8125 5.62109L0.65625 1.71094C0.382812 1.40104 0.373698 1.09115 0.628906 0.78125C0.920573 0.507812 1.23047 0.498698 1.55859 0.753906L5.25 4.25391L8.96875 0.753906C9.27865 0.498698 9.57943 0.498698 9.87109 0.753906C10.1263 1.08203 10.1263 1.40104 9.87109 1.71094Z"
                    fill={statusColors[selectedKey]?.color}
                />
            </svg>
        </div>
    );
};

export default StatusDropdown;
