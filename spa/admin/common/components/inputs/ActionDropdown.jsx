import React, { useState, useRef, useEffect } from "react";
import { createPortal } from "react-dom";

const ActionDropdown = ({
    options,
    placeholder,
    onChange,
    value = null
}) => {
    const [isOpen, setIsOpen] = useState(false);
    const buttonRef = useRef(null);
    const [position, setPosition] = useState({ top: 0, left: 0, width: 0 });

    const selectedLabel = value?.label || placeholder;

    const handleOptionClick = (option) => {
        setIsOpen(false);
        if (onChange) {
            onChange(option);
        }
    };

    // Update dropdown position
    useEffect(() => {
        if (isOpen && buttonRef.current) {
            const rect = buttonRef.current.getBoundingClientRect();
            setPosition({
                top: rect.bottom + window.scrollY,
                left: rect.left + window.scrollX,
                width: rect.width,
            });
        }
    }, [isOpen]);

    // Handle outside click to close dropdown
    useEffect(() => {
        const handleClickOutside = (e) => {
            if (buttonRef.current && !buttonRef.current.contains(e.target)) {
                setIsOpen(false);
            }
        };

        if (isOpen) {
            document.addEventListener("mousedown", handleClickOutside);
        }
        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, [isOpen]);

    // Dropdown list rendered via portal
    const dropdownList = (
        <ul
            style={{
                position: "absolute",
                top: `${position.top}px`,
                left: `${position.left}px`,
                width: `${position.width}px`,
                zIndex: 9999,
            }}
            className="border bg-white border-ec-border rounded-lg shadow-2xl p-2 font-inter"
        >
            {options.map((option) => (
                <li
                    key={option.value}
                    className={`px-4 py-[6px] text-[14px] text-ec-body font-normal leading-[26px] hover:bg-[#F8F8F8] cursor-pointer rounded-[4px] m-0 ${
                        value?.value === option.value ? "bg-[#F8F8F8]" : ""
                    }`}
                    onMouseDown={() => handleOptionClick(option)}
                >
                    {option.label}
                </li>
            ))}
        </ul>
    );

    return (
        <div className="relative font-inter h-ec-input">
            <button
                ref={buttonRef}
                type="button"
                onClick={() => setIsOpen(!isOpen)}
                className={`w-full flex items-center justify-between rounded-lg text-[14px] capitalize leading-[20px] h-full py-[7px] px-[15px] border-[1px] border-solid duration-300 ${
                    isOpen
                        ? "border-ec-primary [box-shadow:0_0_0_4px_#F3F0FF]"
                        : "border-ec-table-stock hover:border-ec-primary"
                }`}
            >
                {selectedLabel}
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="6" viewBox="0 0 11 6" fill="none">
                    <path
                        d="M9.87109 1.71094L5.71484 5.62109C5.56901 5.7487 5.41406 5.8125 5.25 5.8125C5.08594 5.8125 4.9401 5.7487 4.8125 5.62109L0.65625 1.71094C0.382812 1.40104 0.373698 1.09115 0.628906 0.78125C0.920573 0.507812 1.23047 0.498698 1.55859 0.753906L5.25 4.25391L8.96875 0.753906C9.27865 0.498698 9.57943 0.498698 9.87109 0.753906C10.1263 1.08203 10.1263 1.40104 9.87109 1.71094Z"
                        fill="#3C3C42"
                    />
                </svg>
            </button>

            {isOpen && createPortal(dropdownList, document.body)}
        </div>
    );
};

export default ActionDropdown;
