import React from "react";

const Input = ({
    id,
    label,
    value,
    placeholder,
    handleChange,
    type = "text",
}) => {
    return (
        <div className="flex flex-col gap-[6px]">
            <label
                htmlFor={id}
                className="font-inter font-normal text-ec-body text-base leading-[26px]"
            >
                {label}
            </label>

            <input
                type={type}
                id={id}
                name={id}
                value={value}
                min={type === "number" ? 0 : null}
                onChange={handleChange}
                className="easycommerce-coupon-input"
                placeholder={placeholder}
            />
        </div>
    );
};

export default Input;
