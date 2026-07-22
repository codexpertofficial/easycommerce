import React, { useEffect, useRef, useState } from "react";
import { __ } from "@wordpress/i18n";
import CustomDate from "./CustomDate";

// stat icons
const downArrow = `${EASYCOMMERCE.assets}admin/img/icons/select-arrow-down.png`;

const dataRange = EASYCOMMERCE.date_ranges;
const dataRangeKeys = Object.keys(dataRange);
const dataRangeValues = Object.values(dataRange);

const options = dataRangeKeys.map((_, index) => {
    return {
        value: dataRangeKeys[index],
        label: dataRangeValues[index],
    };
});

const RangeFilter = ({
    range,
    customRange,
    handleRange,
    handleCustomRange,
    filterLabel,
    handleFilterLabel,
}) => {
    const [isOpen, setIsOpen] = useState(false);

    // Ref for the select box
    const selectRef = useRef(null);

    const handleSelectValue = (option) => {
        handleRange(option.value);
        handleFilterLabel(option.label);

        setIsOpen(!isOpen);
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

    useEffect(() => {
        if (customRange?.from || customRange?.to) {
            handleFilterLabel(`${customRange.from} - ${customRange.to}`);
        }

        if (customRange?.from && customRange?.to) {
            setIsOpen(false);
        }
    }, [customRange]);

    return (
        <div ref={selectRef}>
            <div
                className="easycommerce-select-box relative border bg-white border-ec-table-stock hover:border-ec-secondary 
                focus:border-ec-primary rounded-lg w-[153px] max-[1600px]:w-[136px] max-[1200px]:w-[170px] cursor-pointer transition-all duration-300 ease-in-out"
                style={
                    isOpen
                        ? {
                              border: "1px solid var(--color-ec-secondary)",
                              boxShadow:
                                  "0px 2px 4.5px 0px var(--color-ec-tertiary)",
                          }
                        : null
                }
            >
                <span
                    onClick={() => setIsOpen(!isOpen)}
                    className="flex items-center justify-between py-2 px-[15px] h-ec-input"
                >
                    <p
                        className={`font-inter ${
                            filterLabel && filterLabel.includes(" - ")
                            ? "text-xs"
                            : "text-base"
                        }`}
                    >
                        {filterLabel || __( 'Select a range', 'easycommerce' )}
                    </p>
                    <img src={downArrow} className="w-3 h-auto" />
                </span>

                {isOpen && (
                    <>
                        <div
                            className="absolute right-0 top-10 bg-white w-[210px] border border-ec-table-stock p-3 
                            rounded-xl shadow-settings-shadow z-50"
                        >
                            {options.map((option, optionsIndex) => (
                                <div
                                    key={optionsIndex}
                                    className={
                                        range === option.value
                                            ? "p-2 font-inter text-sm text-ec-body hover:bg-[#F8F8F8] rounded-lg bg-[#F8F8F8] border border-ec-primary"
                                            : "p-2 font-inter text-sm text-ec-body hover:bg-[#F8F8F8] rounded-lg"
                                    }
                                    onClick={() => handleSelectValue(option)}
                                >
                                    {option.label}
                                </div>
                            ))}

                            <CustomDate
                                customRange={customRange}
                                handleCustomRange={handleCustomRange}
                            />
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

export default RangeFilter;
