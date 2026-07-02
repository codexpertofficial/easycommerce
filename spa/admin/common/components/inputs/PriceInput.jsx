import React from 'react'

const 
PriceInput = ({  label ,Icon, ...rest}) => {

    return (
        <div>
            <h5 className="text-base text-ec-title font-inter mb-[6px]">
                {label}
            </h5>
            <div className="h-[55px] rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder
             hover:border-ec-primary focus-within:border-ec-primary focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] 
             transition-colors duration-300 ease-in-out overflow-hidden flex
            ">
                <div className="h-full w-[50px] flex items-center justify-center border-r border-ec-table-stock bg-[#F9F9F9]">
                    {Icon}
                </div>
                <input
                    type="number"
                    className="w-[calc(100%_-_50px)] h-full border-none outline-none shadow-none"
                    {...rest}
                />
            </div>
        </div>
    )
}

export default PriceInput;