import React from "react";

import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const StatusesSkeleton = () => {
    return (
        <div
            className="easycommerce-dashboard-section col-span-1 px-3 sm:px-6 py-2 sm:py-4 border border-ec-border 
            rounded-[10px] flex flex-col gap-[18px]"
        >
            <Skeleton height={40} width={200} />

            <div className="w-full grid grid-cols-3">
                <div className="col-span-1 h-40">
                    <Skeleton height={150} width={150} circle />
                </div>
                <div className="col-span-2 pl-12 flex">
                    <div className="flex flex-col justify-center items-start gap-1">
                        {[...Array(5)].map((_, index) => (
                            <div className="w-full" key={index}>
                                <Skeleton height={10} width={200} />
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default StatusesSkeleton;
