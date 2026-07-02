import React from "react";

const RecursiveOptions = ({
    data,
    labelKey,
    countKey,
    childrenKey,
    level = 0,
}) => {
    return (
        <>
            <div>
                <div className="flex justify-between items-center gap-5">
                    <label className="flex items-center">
                        <input
                            type="checkbox"
                            name={data[labelKey]}
                            className="min-w-5 relative h-5 accent-ec-primary cursor-pointer mr-2 easycommerce-input-checkoutbox pl-2"
                        />
                        <span className="ml-2 text-[#111827] text-sm font-medium leading-5">
                            {data[labelKey]}
                        </span>
                    </label>
                </div>
                {data[childrenKey] && data[childrenKey].length > 0 && (
                    <div className="pl-4 pt-5">
                        {data[childrenKey].map((child) => (
                            <RecursiveOptions
                                key={child.id}
                                data={child}
                                labelKey={labelKey}
                                childrenKey={childrenKey}
                                level={level + 1}
                            />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
};

export default RecursiveOptions;
