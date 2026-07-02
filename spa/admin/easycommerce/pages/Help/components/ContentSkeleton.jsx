import React from "react";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const ContentSkeleton = () => {
    return (
        <div className="w-full h-full">
            <p>
                <Skeleton
                    height={20}
                    style={{
                        marginBottom: 10,
                    }}
                />
            </p>
            <p>
                <Skeleton
                    width={800}
                    height={20}
                    style={{
                        marginBottom: 10,
                    }}
                />
            </p>
            <p>
                <Skeleton
                    width={500}
                    height={20}
                    style={{
                        marginBottom: 20,
                    }}
                />
            </p>
            <p>
                <Skeleton
                    height={500}
                    style={{
                        marginBottom: 50,
                    }}
                />
            </p>
            <p>
                <Skeleton
                    height={20}
                    style={{
                        marginBottom: 10,
                    }}
                />
            </p>
            <p>
                <Skeleton
                    width={800}
                    height={20}
                    style={{
                        marginBottom: 10,
                    }}
                />
            </p>
            <p>
                <Skeleton
                    width={500}
                    height={20}
                    style={{
                        marginBottom: 20,
                    }}
                />
            </p>
            <p>
                <Skeleton
                    height={500}
                    style={{
                        marginBottom: 50,
                    }}
                />
            </p>
        </div>
    );
};

export default ContentSkeleton;
