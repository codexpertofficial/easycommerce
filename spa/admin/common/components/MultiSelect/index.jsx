import React, { useState, useEffect, useRef } from "react";
import { createPortal } from "react-dom";

import "./style.css";
import ListItem from "./elements/ListItem";

const MultiSelect = ({
    options,
    queryLength = 0,
    selectedValues = '',
    placeholder = '',
    handleSelect,
    handleRemove,
}) => {
    const [isFocused, setIsFocused] = useState(false);
    const [query, setQuery] = useState("");
    const [suggestions, setSuggestions] = useState(options || []);
    const inputWrapperRef = useRef(null);
    const [dropdownStyle, setDropdownStyle] = useState({});

    useEffect(() => {
        if (query.trim() === "") {
            setSuggestions(options || []);
            return;
        }

        const filtered = suggestions.filter(
            (item) =>
                item.toLowerCase().includes(query.toLowerCase()) &&
                !selectedValues?.includes(item)
        );
        setSuggestions(filtered);
    }, [query, selectedValues]);

    // Position the portal dropdown
    useEffect(() => {
        if (isFocused && inputWrapperRef.current) {
            const rect = inputWrapperRef.current.getBoundingClientRect();
            setDropdownStyle({
                position: "absolute",
                top: `${rect.bottom + window.scrollY + 10}px`,
                left: `${rect.left + window.scrollX}px`,
                width: `${rect.width}px`,
                zIndex: 1000,
            });
        }
    }, [isFocused]);

    const renderDropdown = () => {
        return (
            <div
                className="p-4 shadow-2xl bg-white max-h-[300px] overflow-y-auto rounded-md"
                style={dropdownStyle}
            >
                <ul className="w-full flex flex-col justify-start items-start gap-2 flex-wrap">
                    {suggestions
                        .filter((suggestion) => !selectedValues?.includes(suggestion))
                        .map((suggestion, index) => (
                            <li
                                className="w-full p-2 m-0 font-inter text-base leading-[26px] text-ec-body hover:bg-[#F8F8F8] 
                                rounded-md cursor-pointer"
                                key={index}
                                onClick={() => {
                                    handleSelect(suggestion);
                                    setQuery("");
                                }}
                            >
                                {suggestion}
                            </li>
                        ))}
                </ul>
            </div>
        );
    };

    return (
        <div className="relative flex flex-col gap-[6px] w-full h-max border border-ec-table-stock border-l-0 rounded-r-lg" ref={inputWrapperRef}>
            <div
                className="flex items-center justify-start gap-3 w-full h-max min-h-[50px] px-3 py-1 bg-white border border-ec-table-stock rounded-r-lg"
            >
                <ul className="w-full flex justify-start items-center gap-2 flex-wrap max-w-[700px] relative">
                    {selectedValues.length > 0 &&
                        selectedValues.map((value, index) => (
                            <ListItem
                                key={index}
                                text={value}
                                handleDelete={() => handleRemove(index)}
                            />
                        ))}

                    <li
                        className={`${
                            selectedValues.length > 0 ? "w-[125px]" : "w-full"
                        } m-0`}
                    >
                        <input
                            type="text"
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={placeholder}
                            className="easycommerce-multiselect-input"
                            onFocus={() => setIsFocused(true)}
                            onBlur={() => setTimeout(() => setIsFocused(false), 200)}
                        />
                    </li>
                </ul>
            </div>

            {isFocused && suggestions.length > 0 &&
                createPortal(renderDropdown(), document.body)}
        </div>
    );
};

export default MultiSelect;
