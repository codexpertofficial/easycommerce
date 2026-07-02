import React, { useState } from "react";

const NotFound = ({
    ImageUrl = "",
    title = "",
    description = "",
    isBtn = false,
    btnText = "",
    btnCallBack = () => {},
    children,
}) => {

    return (
        <>
            <div className="h-full max-w-full bg-white pt-[140px] text-center rounded-2xl">
                <img
                    src={ImageUrl}
                    className="max-w-[115px] max-h-[137px] mx-auto mb-4 pointer-events-none"
                />
                <p className="text-ec-title text-center font-inter font-medium text-2xl leading-8">
                    {title}
                </p>
               <p className="text-ec-light-black text-center font-inter font-normal text-base mt-2 leading-6 mb-6">
                    {description.split('\n').map((line, idx) => (
                        <span key={idx}>
                            {line}
                            <br />
                        </span>
                    ))}
                </p>

                {isBtn && (
                    <>
                        <button
                            className="font-inter py-[8px] px-4 border border-ec-primary rounded-lg 
                            font-medium text-white text-base capitalize bg-ec-primary hover:text-white transition-all ease-in-out duration-500"
                            onClick={btnCallBack}
                        >
                            {btnText}
                        </button>
                        {children}
                    </>
                )}
            </div>
        </>
    );
};

export default NotFound;
