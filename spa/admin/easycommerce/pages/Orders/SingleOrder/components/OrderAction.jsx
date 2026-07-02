import React, { useEffect, useRef, useState } from "react";

// stat icons
const downArrow = `${EASYCOMMERCE.assets}admin/img/icons/arrow-down.png`;

const OrderAction = ({ options, selectModal }) => {
    // Ref for the select box
    const selectRef = useRef(null);

    const [isOpen, setIsOpen] = useState(false);

    const handleSelectClick = () => {
        isOpen ? setIsOpen(false) : setIsOpen(true);
    };

    // Effect to handle clicks outside of the select box
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (
                selectRef.current &&
                !selectRef.current.contains(event.target)
            ) {
                setIsOpen(false);
            }
        };

        document.addEventListener("mousedown", handleClickOutside);
        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, []);

    return (
        <div ref={selectRef}>
            <div
                onClick={handleSelectClick}
                className="easycommerce-select-box relative border border-ec-primary bg-ec-primary py-[12px] px-[15px] 
                rounded-lg w-[162px] cursor-pointer"
            >
                {/* SELECT LABEL  */}
                <span className="flex items-center justify-between">
                    <p className="text-white font-medium text-base font-inter leading-[26px]">
                        Action
                    </p>
                    <img src={downArrow} className="w-3 h-auto" />
                </span>

                {/* OPTIONS  */}
                {isOpen && (
                    <>
                        <div className="absolute right-0 top-[68px] bg-white w-[200px] p-5 rounded-xl shadow-settings-shadow z-50 border border-ec-border">
                            {options.map((option, optionsIndex) => (
                                <div
                                    key={optionsIndex}
                                    className="p-3 font-inter text-base leading-[26px] text-ec-body hover:bg-[#F8F8F8] 
                                    rounded-lg mb-[6px]"
                                    onClick={() => selectModal(option.value)}
                                >
                                    {option.label}
                                </div>
                            ))}
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

export default OrderAction;
