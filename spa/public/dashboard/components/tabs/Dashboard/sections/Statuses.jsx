import React from "react";
import { ResponsivePie } from "@nivo/pie";

const Statuses = ({ isLoading, orders }) => {
    if (orders.length === 0) {
        return (
            <div className="easycommerce-dashboard-section mt-8">
                <p>You have no orders.</p>
            </div>
        );
    }

    return (
        <div
            className="easycommerce-dashboard-section col-span-1 px-3 sm:px-6 py-2 sm:py-4 border border-ec-border 
            rounded-[10px]"
        >
            <h3 className="easycommerce-dashboard-section-title !text-lg sm:!text-2xl">
                Order Statuses
            </h3>

            <div className="w-full grid grid-cols-3 gap-4">
                <div className="col-span-1 h-40">
                    <ResponsivePie
                        data={orders.map(item => ({
                            id: item.id,
                            label: item.label,
                            value: item.value,
                            color: item.color,
                        }))}
                        colors={{ datum: "data.color" }}
                        margin={{
                            top: 10,
                            right: 10,
                            bottom: 10,
                            left: 10,
                        }}
                        innerRadius={0.5}
                        activeOuterRadiusOffset={8}
                        borderWidth={1}
                        borderColor={{
                            from: "color",
                            modifiers: [["darker", 0.2]],
                        }}
                        enableArcLinkLabels={false}
                        arcLinkLabelsSkipAngle={10}
                        arcLinkLabelsTextColor="#333333"
                        arcLinkLabelsThickness={2}
                        arcLinkLabelsColor={{ from: "color" }}
                        enableArcLabels={false}
                        arcLabelsSkipAngle={10}
                        arcLabelsTextColor={{
                            from: "color",
                            modifiers: [["darker", 2]],
                        }}
                    />
                </div>
                <div className="col-span-1 flex">
                    <div className="flex flex-col justify-center items-start gap-5">
                        {orders.map((item, index) => {
                            return (
                                <div
                                    className="w-full flex justify-between items-center gap-10"
                                    key={index}
                                >
                                    <div className="flex justify-start items-center gap-3">
                                        <span
                                            className={`w-[10px] h-[10px] rounded-full`}
                                            style={{
                                                backgroundColor: item.color,
                                            }}
                                        ></span>
                                        <p className="text-xs font-inter font-medium text-ec-placeholder">
                                            {item.label}
                                        </p>
                                    </div>

                                    <p className="text-sm font-inter font-bold text-[#120350]">
                                        {item.value}
                                    </p>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Statuses;
