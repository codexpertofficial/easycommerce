import React, { useState, useEffect } from "react";
import globalToast from "../../../../common/components/globalToast";

const arrowDown = `${EASYCOMMERCE.assets}admin/img/icons/arrowDown.png`;
const completeStatus = `${EASYCOMMERCE.assets}admin/img/icons/Complete.png`;
const cancelledStatus = `${EASYCOMMERCE.assets}admin/img/icons/Cancle.png`;
const pendingStatus = `${EASYCOMMERCE.assets}admin/img/icons/Pending.png`;

const ProductStatusDropdown = ({
    options,
    value,
    placeholder,
    width = "",
    menuWidth = "",
    productId,
    prevStatus,
    onStatusChange,
    productTitle,
}) => {
    const statusColors = {
        publish: {
            color: "var(--color-ec-liveText)",
            background: "var(--color-ec-liveBg)",
            icon: completeStatus,
            border: "1px solid var(--color-ec-liveBorder)",
        },
        draft: {
            color: "var(--color-ec-draftText)",
            background: "var(--color-ec-draftBg)",
            icon: pendingStatus,
            border: "1px solid var(--color-ec-draftBorder)",
        },
        trash: {
            color: "var(--color-ec-trashText)",
            background: "var(--color-ec-trashBg)",
            icon: cancelledStatus,
            border: "1px solid var(--color-ec-trashBorder)",
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
        setSelectedLabel(option.label);
        setSelectedKey(option.value);
        setIsOpen(false);

        easycommerce_modal(true);

        fetch(`${EASYCOMMERCE.rest_base}/products/${productId}?status=${option.value}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify({
                title: productTitle,
            }),
        })
        .then((res) => res.json())
        .then((data) => {
            easycommerce_modal(false);
            if (data.success && data.data?.product.id) {
                addToastData({
                    type: "success",
                    message: data.data.message,
                });
                if (typeof onStatusChange === "function") {
                    onStatusChange(prevStatus, option.value, data.data.product.id);
                }
            } else {
                addToastData({
                    type: "error",
                    message: data.data || "Failed to update status",
                });
            }
        });
    };

    return (
        <div
            className="relative flex items-center rounded-lg"
            style={{
                width,
                color: statusColors[selectedKey]?.color,
                background: statusColors[selectedKey]?.background,
                border: statusColors[selectedKey]?.border,
            }}
        >
            <button
                type="button"
                onClick={() => setIsOpen(!isOpen)}
                onBlur={() => setIsOpen(false)}
                className="easycommerce-dropdown-field h-[32px] text-sm font-inter text-left pl-4 pr-10 capitalize"
            >
                {selectedLabel || placeholder}
            </button>

            {isOpen && (
                <ul
                    className="absolute top-10 px-3 py-2 left-0 border bg-white border-ec-border rounded-[12px] shadow-2xl z-[99]"
                    style={{ width: menuWidth }}
                >
                    {options.map((option) => (
                        <li
                            key={option.value}
                            className="px-3 py-2 text-sm font-normal text-ec-body leading-[26px] hover:bg-ec-modal cursor-pointer rounded-[4px]"
                            onMouseDown={() => handleOptionClick(option)}
                        >
                            {option.label}
                        </li>
                    ))}
                </ul>
            )}

            <img
                src={statusColors[selectedKey]?.icon || arrowDown}
                alt="Dropdown Arrow"
                className={`easycommerce-select-icon absolute w-3 right-3 transition-transform duration-300 ${isOpen ? "rotate-180" : "rotate-0"}`}
            />
        </div>
    );
};

export default ProductStatusDropdown;
