import React from "react";

import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

import TableSkeleton from "../../../../../../admin/common/TableSkeleton";

const RecentOrdersSkeleton = () => {
    return (
        <div className="easycommerce-dashboard-section mt-8 flex flex-col gap-4">
            <div className="flex justify-between items-center">
                <Skeleton height={40} width={200} />

                <Skeleton height={20} width={100} />
            </div>

            <TableSkeleton numberOfRows={5} SkeletonHeight={30} />
        </div>
    );
};

export default RecentOrdersSkeleton;
