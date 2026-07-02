import React from "react";

const NotFound = ({ ImageUrl, title, description }) => {
    return (
        <div className="w-full h-[50vh] flex justify-center items-center">
            <div className="min-w-[320px] flex flex-col justify-between items-center gap-[30px]">
                <img
                    src={ImageUrl}
                    className="w-[200px] h-[200px] object-contain"
                    alt="No data"
                />
                <div className="flex flex-col items-center gap-2">
                    <h3 className="font-inter text-xl font-semibold text-ec-title leading-6">
                        {title}
                    </h3>
                    {description && (
                        <p className="font-inter text-base text-ec-body leading-6">
                            {description}
                        </p>
                    )}
                </div>
            </div>
        </div>
    );
};

export default NotFound;