import React from "react";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const LoadingSkeleton = () => {
    return (
        <div className="w-full h-full flex items-start justify-center px-8">
            {/* Accordian Area Loader  */}
            <div className="w-[350px] mr-12 mt-20 rtl:ml-12 rtl:mr-0">
                {Array(5)
                    .fill(0)
                    .map((_, index) => (
                        <p key={index}>
                            <Skeleton
                                width={350}
                                height={80}
                                style={{ marginBottom: 10 }}
                            />
                        </p>
                    ))}
            </div>
            {/* Contents Area Loader  */}
            <div className="w-[calc(100%_-_400px)] mt-20">
                <p>
                    <Skeleton height={20} style={{ marginBottom: 10 }} />
                </p>
                <p>
                    <Skeleton
                        width={800}
                        height={20}
                        style={{ marginBottom: 10 }}
                    />
                </p>
                <p>
                    <Skeleton
                        width={500}
                        height={20}
                        style={{ marginBottom: 20 }}
                    />
                </p>
                <p>
                    <Skeleton height={500} style={{ marginBottom: 50 }} />
                </p>
                <p>
                    <Skeleton height={20} style={{ marginBottom: 10 }} />
                </p>
                <p>
                    <Skeleton
                        width={800}
                        height={20}
                        style={{ marginBottom: 10 }}
                    />
                </p>
                <p>
                    <Skeleton
                        width={500}
                        height={20}
                        style={{ marginBottom: 20 }}
                    />
                </p>
                <p>
                    <Skeleton height={500} style={{ marginBottom: 50 }} />
                </p>
            </div>
        </div>
    );
};

export default LoadingSkeleton;
