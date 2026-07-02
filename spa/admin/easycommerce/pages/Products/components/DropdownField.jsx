import React, { useEffect, useState } from "react";

const DropdownField = ({
    currentValue,
    options,
    placeholder,
    width = "auto",
    menuWidth = "190px",
    onChange,
}) => {
    const [isOpen, setIsOpen] = useState(false);
    const [label, setLabel] = useState(placeholder);

    const handleOptionClick = (option) => {
        setLabel(option.label);
        setIsOpen(false);

        if (onChange) {
            onChange(option.value);
        }
    };

    useEffect(() => {
        if (!currentValue) {
            setLabel(placeholder);
        }
    }, [currentValue]);

    return (
        <div className="relative flex items-center justify-center" style={{ width }}>
            <button
                type="button"
                onClick={() => setIsOpen(!isOpen)}
                onBlur={() => setTimeout(() => setIsOpen(false), 150)}
                className={`w-full h-ec-input pl-3 pr-8 py-2 rounded-lg font-inter text-sm text-ec-body
                border border-ec-table-stock bg-white text-left truncate
                placeholder:text-ec-placeholder transition-colors duration-300 ease-in-out
                hover:border-ec-primary focus:outline-none focus:border-ec-primary
                focus:[box-shadow:0_0_0_4px_#F3F0FF] min-w-[120px]`}
                style={{
                    boxShadow: isOpen ? "0 0 0 4px #F3F0FF" : "",
                    borderColor: isOpen ? "var(--color-ec-primary)" : "",
                }}
            >
                {label}
            </button>

            {isOpen && (
                <ul
                    className="absolute top-[110%] left-0 z-[99] p-2 border bg-white border-ec-border rounded-[12px] shadow-2xl font-inter"
                    style={{ width: 'auto', minWidth: menuWidth }}
                >
                    {options.map((option) => (
                        <li
                            key={option.value}
                            className={`px-3 py-1 text-sm text-ec-body font-normal leading-[26px] 
                            hover:bg-[#F8F8F8] cursor-pointer rounded-[4px] m-0 
                            ${
                                option.value === currentValue
                                    ? "bg-[#F8F8F8]"
                                    : ""
                            }`}
                            onMouseDown={() => handleOptionClick(option)}
                        >
                            {option.label}
                        </li>
                    ))}
                </ul>
            )}

            <div
                className="easycommerce-select-icon absolute w-3 right-3 pointer-events-none"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="11"
                    height="6"
                    viewBox="0 0 11 6"
                    fill="none"
                >
                    <path
                        d="M9.87109 1.71094L5.71484 5.62109C5.56901 5.7487 5.41406 5.8125 5.25 5.8125C5.08594 5.8125 4.9401 5.7487 4.8125 5.62109L0.65625 1.71094C0.382812 1.40104 0.373698 1.09115 0.628906 0.78125C0.920573 0.507812 1.23047 0.498698 1.55859 0.753906L5.25 4.25391L8.96875 0.753906C9.27865 0.498698 9.57943 0.498698 9.87109 0.753906C10.1263 1.08203 10.1263 1.40104 9.87109 1.71094Z"
                        fill="#3C3C42"
                    />
                </svg>
            </div>
        </div>
    );
};

export default DropdownField;