import React, { useState } from "react";
const arrowDown = `${EASYCOMMERCE.assets}admin/img/icons/arrowDown.png`;

const DropdownField = ({
    options,
    label,
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
                className=" h-[48px] rounded-md text-base font-inter text-ec-body bg-white border border-ec-border text-left pl-4 pr-10 py-2 focus:border-ec-primary"
                style={{ width }}
            >
                {selected}
            </button>
            {isOpen && (
                <ul
                    className="absolute top-16 p-3 right-0 border bg-white border-ec-border rounded-[12px] shadow-2xl z-[99]"
                    style={{ width: menuWidth }}
                >
                    {options.map((option) => (
                        <li
                            key={option.value}
                            className="px-4 py-3 text-base text-ec-body font-normal leading-[26px] hover:bg-[#F8F8F8] cursor-pointer rounded-[4px]"
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
