import React, { useState } from "react";
const arrowDown = `${EASYCOMMERCE.assets}admin/img/icons/arrowDown.png`;

const DropdownField = ({
    options,
    placeholder,
    width = "",
    menuWidth = "",
    onChange,
}) => {
    const [isOpen, setIsOpen] = useState(false);
    const [selected, setSelected] = useState(placeholder);

    const handleOptionClick = (option) => {
        setSelected(option.label);
        setIsOpen(false);
        if (onChange) {
            onChange(option.value);
        }
    };

    return (
        <div className="relative flex items-center justify-center">
            <button
                type="button"
                onClick={() => setIsOpen(true)}
                onBlur={() => setIsOpen(false)}
                className="easycommerce-dropdown-field h-[48px] rounded-lg text-sm font-inter text-ec-placeholder bg-white border 
                border-ec-table-stock text-left pl-4 pr-10 py-2 focus:text-ec-body focus:border-ec-primary hover:border-ec-secondary 
                transition-all ease-in-out duration-500"
                style={{
                    width,
                    boxShadow: isOpen
                        ? "0px 2px 4.5px 0px var(--color-ec-tertiary)"
                        : "",
                }}
            >
                {selected}
            </button>
            {isOpen && (
                <ul
                    className="absolute top-16 p-3 right-[-18px] border bg-white border-ec-border rounded-[12px] shadow-2xl z-[99]"
                    style={{ width: menuWidth }}
                >
                    {options.map((option) => (
                        <li
                            key={option.value}
                            className="px-4 py-2 text-sm text-ec-body font-normal leading-[26px] hover:bg-ec-modal cursor-pointer rounded-[4px]"
                            onMouseDown={() => handleOptionClick(option)}
                        >
                            {option.label}
                        </li>
                    ))}
                </ul>
            )}
            <img
                src={arrowDown}
                alt="Search Icon"
                className="easycommerce-select-icon absolute w-3 ml-0 right-3"
            />
        </div>
    );
};
export default DropdownField;
