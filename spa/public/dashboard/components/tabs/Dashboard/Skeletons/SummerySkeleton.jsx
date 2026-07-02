import React from "react";

import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const SummerySkeleton = () => {
    return (
        <div
            className="easycommerce-dashboard-section col-span-1 px-3 sm:px-6 py-3 sm:py-4 border border-ec-border 
            rounded-[10px] flex flex-col gap-[18px]"
        >
            <Skeleton height={40} width={200} />

            <div className="easycommerce-dashboard-summery-list grid grid-cols-1 sm:grid-cols-2 gap-3">
                {[...Array(4)].map((_, index) => (
                    <div
                        key={index}
                        className="col-span-1 flex justify-start items-center gap-4 px-4 py-[14px] border 
                        border-ec-border rounded-[10px]"
                    >
                        <Skeleton height={40} width={40} />

                        <div className="w-full">
                            <Skeleton height={20} />
                            <Skeleton height={20} />
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default SummerySkeleton;
