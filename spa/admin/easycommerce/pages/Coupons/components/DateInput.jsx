import React from "react";

const DateInput = ({ id, label, value, placeholder, handleChange }) => {
    return (
        <div className="flex flex-col gap-[6px] h-ec-input">
            <label
                htmlFor={id}
                className="font-inter font-normal text-ec-body text-base leading-[26px]"
            >
                {label}
            </label>

            <input
                type="date"
                id={id}
                name={id}
                value={value}
                onChange={handleChange}
                className="easycommerce-coupon-input h-ec-input"
                placeholder={placeholder}
                min={new Date().toISOString().split("T")[0]}
                onClick={(e) => e.target.showPicker && e.target.showPicker()}
            />
        </div>
    );
};

export default DateInput;
