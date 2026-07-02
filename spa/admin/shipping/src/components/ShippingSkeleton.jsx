import React from "react";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const ShippingSkeleton = () => {
    return (
        <div className="mt-14">
            {[...Array(10)].map((_, index) => (
                <Skeleton
                    width={"100%"}
                    height={30}
                    style={{ marginBottom: 30 }}
                    key={index}
                />
            ))}
        </div>
    );
};

export default ShippingSkeleton;
