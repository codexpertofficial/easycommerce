import React from 'react'

const CardsSkeleton = ({ count = 4 }) => {
    return (
        <div className='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-6'>
            {Array.from({ length: count }).map((_, index) => (
                <div
                    key={index}
                    className="flex flex-col justify-between h-[144px] bg-white rounded-lg p-5 border border-[#F0EDFB]"
                >
                    {/* Top */}
                    <div className="flex justify-between">
                        <div className="flex gap-3">
                            {/* Icon */}
                            <div className="w-11 h-11 rounded-[10px] skeleton" />

                            {/* Title */}
                            <div className="h-5 w-24 rounded skeleton mt-2" />
                        </div>

                        {/* Menu dots */}
                        <div className="w-6 h-6 rounded skeleton" />
                    </div>

                    {/* Bottom */}
                    <div className="flex justify-between items-end">
                        {/* Value */}
                        <div className="h-6 w-20 rounded skeleton" />

                        {/* Comparison */}
                        <div className="flex items-center gap-2">
                            <div className="w-4 h-4 rounded skeleton" />
                            <div className="h-4 w-12 rounded skeleton" />
                            <div className="h-3 w-20 rounded skeleton" />
                        </div>
                    </div>
                </div>
            ))}
        </div>
    )
}

export default CardsSkeleton