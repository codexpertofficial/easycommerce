import React from "react";
import Skeleton from "react-loading-skeleton";
import { __ } from "@wordpress/i18n";

const ProfileSkeleton = () => {
    return (
        <>
            <div className="flex justify-center flex-col align-center gap-4 pb-8 border-b border-b-ec-border">
                <div className="h-12">
                    <h3 className="easycommerce-dashboard-section-title !text-lg sm:!text-2xl">
                        {__( "Your profile", "easycommerce" )}
                    </h3>
                </div>

                <div className="w-[110px] h-[110px] mx-auto">
                    <Skeleton
                        height={104}
                        width={104}
                        circle
                        style={{ margin: "0 auto" }}
                    />
                </div>
            </div>

            <div className="pt-[60px] flex flex-col gap-12">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {[...Array(8)].map((_, index) => (
                        <div
                            className="col-span-1 flex flex-col gap-2 items-start"
                            key={index}
                        >
                            <div className="w-full">
                                <Skeleton height={20} width={"100%"} />
                                <Skeleton height={40} width={"100%"} />
                            </div>
                        </div>
                    ))}
                </div>

                <div className="flex justify-end items-center gap-[17px]">
                    <Skeleton height={48} width={180} />
                    <Skeleton height={48} width={180} />
                </div>
            </div>
        </>
    );
};

export default ProfileSkeleton;
