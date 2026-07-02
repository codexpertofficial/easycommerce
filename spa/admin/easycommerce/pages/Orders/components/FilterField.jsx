import React, { useState } from "react";
const Filter = `${EASYCOMMERCE.assets}admin/img/icons/Filter.png`;

const FilterField = ({
    options,
    placeholder = "Filter...",
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
            <img
                src={Filter}
                alt="Filter Icon"
                className="easycommerce-select-icon absolute w-4 left-3 z-10 pointer-events-none"
            />

            <button
                type="button"
                onClick={() => setIsOpen(true)}
                onBlur={() => setIsOpen(false)}
                className="easycommerce-dropdown-field h-[48px] rounded-lg text-sm font-inter text-ec-placeholder bg-white border 
                border-ec-table-stock text-left pl-10 pr-4 py-2 focus:text-ec-body focus:border-ec-primary hover:border-ec-secondary 
                transition-all ease-in-out duration-500 w-full focus:[box-shadow:0_0_0_4px_#F3F0FF]"
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
                    className="absolute top-16 p-3 right-[-48px] border bg-white border-ec-border rounded-[12px] shadow-2xl z-[99]"
                    style={{ width: menuWidth }}
                >
                    {options.map((option) => (
                        <li
                            key={option.value}
                            className="px-4 py-2 text-sm text-ec-body font-normal leading-[26px] hover:bg-ec-modal hover:text-[#7351FD] cursor-pointer rounded-[4px]"
                            onMouseDown={() => handleOptionClick(option)}
                        >
                            {option.label}
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
};

export default FilterField;
