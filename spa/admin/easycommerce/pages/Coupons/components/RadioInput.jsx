import React from "react";

const RadioInput = ({ id, label, value, name, handleChange, checked }) => {
    return (
        <div className="flex justify-start items-center gap-2">
            <input
                type="radio"
                name={name}
                id={id}
                className="easycommerce-input-type-radio cursor-pointer"
                value={value}
                onChange={handleChange}
                checked={checked}
            />
            <label
                htmlFor={id}
                className="text-base leading-[26px] font-normal font-inter text-ec-body cursor-pointer"
            >
                {label}
            </label>
        </div>
    );
};

export default RadioInput;
