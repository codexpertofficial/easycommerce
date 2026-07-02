import React from "react";

const noAbandonedCartsIcon = `${EASYCOMMERCE.assets}admin/img/abandoned-cart.png`;

const NotFound = ({ title = "No Abandoned Carts found" }) => {
    return (
        <div className="w-full h-full pt-[130px]  bg-white flex justify-center items-center">
            <div className="min-w-[320px] flex flex-col justify-between items-center gap-[30px]">
                <img
                    src={noAbandonedCartsIcon}
                    className="w-[150px] h-[150px] pointer-events-none"
                />

                <div className="flex flex-col items-center gap-6">
                    <p className="font-inter font-medium text-2xl leading-8 text-ec-title">
                        {title}
                    </p>
                    <p className="font-inter text-base leading-[26px] text-ec-light-black w-[300px] text-center">
                        All Abandoned Carts will appear here once they occur.
                    </p>
                </div>
            </div>
        </div>
    );
};
export default NotFound;