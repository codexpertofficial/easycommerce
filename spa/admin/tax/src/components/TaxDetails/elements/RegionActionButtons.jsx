import React from "react";

// Icons
const deleteIcon = `${EASYCOMMERCE.assets}admin/img/icons/delete.png`;
const deletehoverIcon = `${EASYCOMMERCE.assets}admin/img/icons/delete-hover.png`;
const plusIcon = `${EASYCOMMERCE.assets}admin/img/icons/plusIcon.png`;

const RegionActionButtons = ({ add, remove }) => {
    return (
        <td className="flex items-center justify-center">
            <button
                type="button"
                onClick={add}
                className="w-10 h-10 border border-ec-table-stock rounded-full mx-2 flex items-center justify-center"
            >
                <img src={plusIcon} alt="add-regions" />
            </button>

            <button
                type="button"
                onClick={remove}
                className="w-10 h-10 border border-ec-table-stock rounded-full flex items-center justify-center group hover:bg-[#FF3A521A]"
            >
                <img
                    className="w-[14px] h-[16px] block group-hover:hidden"
                    src={deleteIcon}
                    alt="delete-regions"
                />
                <img
                    className="w-[14px] h-[16px] hidden group-hover:block"
                    src={deletehoverIcon}
                    alt="delete-regions-hover"
                />
            </button>
        </td>
    );
};

export default RegionActionButtons;
