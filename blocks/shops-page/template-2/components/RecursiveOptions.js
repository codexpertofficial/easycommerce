import React from "react";

const RecursiveOptions = ({
    data,
    labelKey,
    countKey,
    childrenKey,
    level = 0,
}) => {
    return (
        <div>
            <div className="flex justify-between items-center gap-5">
                <label className="flex items-center">
                    <input
                        type="checkbox"
                        className="easycommerce-input-checkoutbox"
                        name={data[labelKey]}
                    />
                    <span className="ml-2 text-[#111827] text-sm font-medium leading-5">
                        {data[labelKey]}
                    </span>
                </label>
                <div className="bg-[#F8F8F8] px-4 rounded-[15px]">
                    {data[countKey] !== undefined ? data[countKey] : 0}
                </div>
            </div>
            {data[childrenKey] && data[childrenKey].length > 0 && (
                <div className="pl-4 pt-5">
                    {data[childrenKey].map((child) => (
                        <RecursiveOptions
                            key={child.id}
                            data={child}
                            labelKey={labelKey}
                            countKey={countKey}
                            childrenKey={childrenKey}
                            level={level + 1}
                        />
                    ))}
                </div>
            )}
        </div>
    );
};

export default RecursiveOptions;
