import React from "react";

const SaveButtons = ({ onCancel, onSave, taxId }) => {
    return (
        <div className="w-full flex justify-end items-center gap-[30px] mt-10">
            <button
                type="button"
                className="flex justify-center items-center font-inter border-b border-ec-body text-ec-body focus:shadow-none focus:ec-body text-base"
                onClick={onCancel}
            >
                Cancel
            </button>
            <button
                type="button"
                className="py-[12px] flex justify-center items-center gap-[8px] font-inter bg-white group border border-ec-primary px-4 rounded-lg text-ec-primary hover:text-white hover:bg-ec-primary focus:shadow-none focus:text-white focus:bg-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out duration-500 leading-[26px]"
                onClick={onSave}
            >
                {taxId ? "Update Taxation" : "Create Taxation"}
            </button>
        </div>
    );
};

export default SaveButtons;
