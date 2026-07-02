import React from "react";

import SummerySkeleton from "./SummerySkeleton";
import StatusesSkeleton from "./StatusesSkeleton";
import RecentOrdersSkeleton from "./RecentOrdersSkeleton";

const DashboardSkeleton = () => {
    return (
        <>
            <div className="grid xl:grid-cols-2 grid-cols-1 gap-5">
                <SummerySkeleton />

                <StatusesSkeleton />
            </div>
            <RecentOrdersSkeleton />
        </>
    );
};

export default DashboardSkeleton;
