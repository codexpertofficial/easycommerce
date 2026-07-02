import React, { useState, useEffect, useRef } from "react";
import { createPortal } from "react-dom";

import "./style.css";
import ListItem from "./elements/ListItem";

const MultiSelect = ({
    options,
    queryLength = 0,
    selectedValues = [],
    placeholder = '',
    handleSelect,
    handleRemove,
}) => {
    const [isFocused, setIsFocused] = useState(false);
    const [query, setQuery] = useState("");
    const [suggestions, setSuggestions] = useState(options || []);
    const inputWrapperRef = useRef(null);
    const [dropdownStyle, setDropdownStyle] = useState({});

    // Update suggestions when options change
    useEffect(() => {
        setSuggestions(options || []);
    }, [options]);

    // Filter suggestions based on query and selected values
    useEffect(() => {
        if (query.trim() === "") {
            // When query is empty, show all options except already selected ones
            const filteredOptions = (options || []).filter(
                (option) => !selectedValues.some((selected) => 
                    selected && selected.id === option.id
                )
            );
            setSuggestions(filteredOptions);
            return;
        }

        // When there's a query, filter by both query text and not being already selected
        const filtered = (options || []).filter(
            (option) =>
                option.name.toLowerCase().includes(query.toLowerCase()) &&
                !selectedValues.some((selected) => 
                    selected && selected.id === option.id
                )
        );
        setSuggestions(filtered);
    }, [query, selectedValues, options]);

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

    // Helper ref to track if component is mounted
    const isMountedRef = useRef(true);
    
    // Clean up on unmount
    useEffect(() => {
        return () => {
            isMountedRef.current = false;
        };
    }, []);

    const renderDropdown = () => {
        return (
            <div
                className="p-4 shadow-2xl bg-white max-h-[300px] overflow-y-auto rounded-md"
                style={dropdownStyle}
            >
                <ul className="w-full flex flex-col justify-start items-start gap-2 flex-wrap">
                    {suggestions
                        .filter((suggestion) => 
                            suggestion && 
                            !selectedValues.some((value) => 
                                value && value.id == suggestion.id
                            )
                        )
                        .map((suggestion, index) => (
                            <li
                                className="w-full p-2 m-0 font-inter text-base leading-[26px] text-ec-body hover:bg-[#F8F8F8] 
                                rounded-md cursor-pointer"
                                key={suggestion.id || index}
                                onClick={() => {
                                    handleSelect(suggestion);
                                    setQuery("");
                                }}
                            >
                                {suggestion.name}
                            </li>
                        ))}
                </ul>
            </div>
        );
    };

    return (
        <div className="relative flex flex-col gap-[6px] w-full h-max" ref={inputWrapperRef}>
            <div
                className="flex items-center justify-start gap-3 w-full h-max min-h-[50px] px-3 py-1 bg-white border border-ec-table-stock rounded-r-lg"
            >
                <ul className="w-full flex justify-start items-center gap-2 flex-wrap max-w-[700px] relative">
                    {selectedValues.length > 0 &&
                        selectedValues.map((value, index) => (
                            <ListItem
                                key={value.id || index}
                                text={value.name || value}
                                handleDelete={() => handleRemove(index)}
                            />
                        ))}

                    <li
                        className={`${
                            selectedValues.length > 0 ? "w-1/3" : "w-full"
                        } m-0`}
                    >
                        <input
                            type="text"
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder={placeholder}
                            className="easycommerce-multiselect-input"
                            onFocus={() => setIsFocused(true)}
                            onBlur={() => {
                                // Use a safer approach for handling blur
                                if (isMountedRef.current) {
                                    setTimeout(() => {
                                        if (isMountedRef.current) {
                                            setIsFocused(false);
                                        }
                                    }, 200);
                                }
                            }}
                            disabled={selectedValues.length >= options.length}
                        />
                    </li>
                </ul>
            </div>

            {isFocused && suggestions.filter(suggestion => 
                suggestion && !selectedValues.some(value => 
                    value && value.id == suggestion.id
                )
            ).length > 0 && createPortal(renderDropdown(), document.body)}
        </div>
    );
};

export default MultiSelect;
