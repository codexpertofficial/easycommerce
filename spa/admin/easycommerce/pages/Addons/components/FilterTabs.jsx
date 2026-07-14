import React from "react";

/**
 * Category filter tabs for the Addons screen.
 *
 * Mirrors the tab design used on the Products/Orders pages (underline on the
 * active tab + a rounded count pill), with horizontal scroll for many tabs.
 *
 * @param {Object[]} tabs   Array of { key, label, count }.
 * @param {string}   active The active tab key.
 * @param {Function} onChange Called with the selected tab key.
 */
const FilterTabs = ({ tabs = [], active = "all", onChange }) => {
    if (!tabs.length) {
        return null;
    }

    return (
        <div className="flex overflow-x-auto overflow-y-hidden min-w-0 mb-8">
            {tabs.map((tab) => {
                const isActive = active === tab.key;

                return (
                    <button
                        key={tab.key}
                        type="button"
                        onClick={() => onChange(tab.key)}
                        className={`relative flex items-center px-2 gap-[3px] border-b-[2px] pb-1.5 font-inter text-sm text-ec-body transition-colors duration-300
                            ${isActive ? "border-ec-primary" : "border-[#F0EDFB]"}`}>
                        <span className="min-w-max">{tab.label}</span>
                        <span className="text-xs font-medium px-2 py-0.5 rounded-full bg-ec-allBg text-ec-allText">
                            {tab.count}
                        </span>
                    </button>
                );
            })}
        </div>
    );
};

export default FilterTabs;
