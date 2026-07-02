import React from "react";
import { useState } from "@wordpress/element";

const Quantity = () => {
    const [quantity, setQuantity] = useState(1);

    return (
        <div className="flex items-center border border-[#1203501A] rounded-md bg-white">
            <button
                type="button"
                className="w-[50px] h-[50px] border-r border-[#1203501A] font-inter leading-[26px] text-2xl bg-white rounded-l-md"
                onClick={() =>
                    setQuantity(quantity > 1 ? quantity - 1 : quantity)
                }
            >
                -
            </button>
            <input
                className="easycommerce-qunatity-input text-ec-body font-inter font-semibold text-base leading-[26px] text-center"
                type="text"
                value={quantity}
                onChange={(e) => setQuantity(e.target.value)}
            />
            <button
                type="button"
                className="w-[50px] h-[50px] border-l border-[#1203501A] font-inter leading-[26px] text-2xl bg-white rounded-r-md"
                onClick={() => setQuantity(quantity + 1)}
            >
                +
            </button>
        </div>
    );
};

export default Quantity;
