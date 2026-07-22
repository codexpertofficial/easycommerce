import React, { useState } from "react";

import { __ } from "@wordpress/i18n";

const actionIcon = `${EASYCOMMERCE.assets}admin/img/icons/actionIcon.png`;

const ActionDropdown = ({ onEdit, onDelete, icons = {}, menuWidth = "120px" }) => {
    const [isOpen, setIsOpen] = useState(false);

    const Actions = [
        { label: __( "Edit", "easycommerce" ), value: "edit", icon: icons.edit },
        { label: __( "Delete", "easycommerce" ), value: "delete", icon: icons.delete },
    ];

    const handleOptionClick = (option) => {
        setIsOpen(false);
        if (option.value === "edit") {
            onEdit?.();
        } else if (option.value === "delete") {
            onDelete?.();
        }
    };

    return (
        <div className="relative p-5">
            <button
                type="button"
                onClick={() => setIsOpen(true)}
                onBlur={() => setIsOpen(false)}
                className="flex items-center justify-center w-8 h-8 rounded-md mr-6"
            >
                <img src={actionIcon} alt={ __( "Action Icon", "easycommerce" ) } className="w-3" />
            </button>

            {isOpen && (
                <ul
                    className="absolute right-[51px] top-full z-[99] p-3 border bg-white border-ec-border rounded-[12px] shadow-2xl min-w-[100px]"
                    style={{ width: menuWidth }}
                >
                    {Actions.map((option) => (
                        <li
                            key={option.value}
                            className="group flex items-center gap-2 px-4 py-2 text-sm text-ec-body font-normal leading-[26px] hover:bg-ec-modal cursor-pointer rounded-[4px]"
                            onMouseDown={() => handleOptionClick(option)}
                        >
                            {option.icon && (
                                <img
                                    src={option.icon}
                                    alt={option.label}
                                    className="w-5 h-4 object-contain"
                                />
                            )}
                            {option.label}
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
};

export default ActionDropdown;
