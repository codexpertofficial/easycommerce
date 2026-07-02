import React, { useState } from "react";

import "./style.css";
import ListItem from "./elements/ListItem";

const MultiSelect = ({
    id,
    label,
    selectedValues,
    placeholder,
    handleSelect,
    handleRemove,
}) => {
    const [isFocused, setIsFocused] = useState(false);
    const [query, setQuery] = useState("");
    const [suggestions, setSuggestions] = useState([]);
    const safeSelectedValues = Array.isArray(selectedValues) ? selectedValues : [];

    const handleQueryChange = (e) => {
        const query = e.target.value;
        setQuery(query);

        if (query.length < 3) {
            setSuggestions([]);
            return;
        }

        fetch(`${EASYCOMMERCE.rest_base}/products?s=${query}`,{
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            }
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success && data.data?.products) {
                    setSuggestions(data.data.products);
                }
            });
    };

    return (
        <div className="relative flex flex-col gap-[6px]">
            <label
                htmlFor={id}
                className="font-inter font-normal text-ec-body text-base leading-[26px]"
            >
                {label}
            </label>

            <div
               className="h-ec-input p-4 flex items-center justify-start gap-3 rounded-lg border hover:border-ec-primary focus-within:border-ec-primary
                focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out overflow-hidden"
            >
                <ul className="w-full flex justify-start items-center gap-2 flex-wrap">
                    {safeSelectedValues.map((value, index) => (
                        <ListItem
                            key={index}
                            text={value.title}
                            handleDelete={() => handleRemove(index)}
                        />
                    ))}

                    <li
                        className={`${
                            safeSelectedValues.length > 0 ? "w-1/3" : "w-full"
                        } m-0`}
                    >
                        <input
                            type="text"
                            id={id}
                            name={id}
                            value={query}
                            onChange={handleQueryChange}
                            placeholder={placeholder}
                            className="easycommerce-multiselect-input text-sm min-h-0 leading-[20px]"
                            onFocus={() => setIsFocused(true)}
                            onBlur={() =>
                                setTimeout(() => setIsFocused(false), 200)
                            }
                        />
                    </li>
                </ul>
            </div>

            {isFocused && suggestions.length > 0 && (
                <div
                    className="absolute left-0 bottom-0 transform translate-y-[calc(100%+10px)] z-10 w-full p-4 
                    shadow-2xl bg-white max-h-[300px] overflow-y-auto rounded-md"
                >
                    <ul className="w-full flex flex-col justify-start items-start gap-2 flex-wrap">
                        {suggestions.map((suggestion, index) => (
                            <li
                                className="w-full p-2 m-0 font-inter text-base leading-[26px] text-ec-body hover:bg-[#F8F8F8] 
                                rounded-md cursor-pointer"
                                key={index}
                                onClick={() => {
                                    handleSelect({
                                        id: suggestion.id,
                                        title: suggestion.title,
                                    });

                                    setQuery("");
                                    setSuggestions([]);
                                }}
                            >
                                {suggestion.title}
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
};

export default MultiSelect;
