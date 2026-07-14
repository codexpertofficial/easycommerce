import React from "react";

import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const CardSkeleton = () => {
    return (
        <>
            <div className="flex gap-6 border-b-2 border-[#F0EDFB] mb-8 pb-2">
                {[...Array(7)].map((_, index) => (
                    <Skeleton key={index} width={90} height={24} borderRadius={12} />
                ))}
            </div>

            <div className="grid grid-cols-4 gap-[30px]">
                {[...Array(10)].map((_, index) => (
                    <div key={index}>
                        <p>
                            <Skeleton
                                height={190}
                                style={{ marginBottom: "20px" }}
                            />
                        </p>
                        <p>
                            <Skeleton
                                height={20}
                                style={{ marginBottom: "20px" }}
                            />
                        </p>
                        <div className="flex items-center justify-between">
                            <Skeleton
                                width={100}
                                height={20}
                                style={{ marginBottom: "20px" }}
                            />
                            <Skeleton
                                width={100}
                                height={40}
                                style={{
                                    marginBottom: "20px",
                                    borderRadius: "10px",
                                }}
                            />
                        </div>
                    </div>
                ))}
            </div>
        </>
    );
};

export default CardSkeleton;
