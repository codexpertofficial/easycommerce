import React from "react";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const FormSkeleton = () => {
    return (
        <div className="m-[30px] mb-10 p-10 bg-white">
            <Skeleton height={40} width={300} />

            <div className="grid grid-cols-2 gap-6 pt-10">
                {[...Array(2)].map((_, index) => (
                    <div
                        className="col-span-1 px-6 pt-[17px] pb-14 border border-ec-border rounded-xl bg-white"
                        key={index}
                    >
                        <Skeleton height={35} width={200} />

                        <div className="f-full mt-6 flex flex-col gap-6">
                            {[...Array(5)].map((_, index) => (
                                <div
                                    className="w-full flex flex-col gap-[6px]"
                                    key={index}
                                >
                                    <Skeleton height={20} width={"100%"} />

                                    <Skeleton height={40} width={"100%"} />
                                </div>
                            ))}
                        </div>
                    </div>
                ))}
            </div>

            <div className="w-full flex justify-end items-center gap-3 pt-11">
                <Skeleton height={48} width={185} />
                <Skeleton height={48} width={185} />
            </div>
        </div>
    );
};

export default FormSkeleton;
