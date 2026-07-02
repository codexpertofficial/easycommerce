import React from "react";

import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const CardSkeleton = () => {
    return (
        <>
            <div className="easycommerce-addons-heading text-center">
                <Skeleton
                    height={40}
                    width={600}
                    style={{ marginBottom: "15px" }}
                />
                <Skeleton
                    height={20}
                    width={700}
                    style={{ marginBottom: "40px" }}
                />
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
