import React from 'react'

const CardsSkeleton = ({ count = 4 }) => {
    return (
        <div className='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6'>
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
                        <div>
                            <svg width="24" height="5" viewBox="0 0 24 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.40002 4.80003C3.72551 4.80003 4.80003 3.72551 4.80003 2.40002C4.80003 1.07452 3.72551 0 2.40002 0C1.07452 0 0 1.07452 0 2.40002C0 3.72551 1.07452 4.80003 2.40002 4.80003Z" fill="#767676"/>
                                <path d="M12.0016 4.80003C13.3271 4.80003 14.4016 3.72551 14.4016 2.40002C14.4016 1.07452 13.3271 0 12.0016 0C10.6761 0 9.60156 1.07452 9.60156 2.40002C9.60156 3.72551 10.6761 4.80003 12.0016 4.80003Z" fill="#767676"/>
                                <path d="M21.5992 4.80003C22.9247 4.80003 23.9993 3.72551 23.9993 2.40002C23.9993 1.07452 22.9247 0 21.5992 0C20.2737 0 19.1992 1.07452 19.1992 2.40002C19.1992 3.72551 20.2737 4.80003 21.5992 4.80003Z" fill="#767676"/>
                            </svg>
                        </div>
                    </div>

                    {/* Bottom */}
                    <div className="flex justify-between items-end">
                        {/* Value */}
                        <div className="h-6 w-20 rounded skeleton" />

                        {/* Comparison */}
                        <div className="flex items-center gap-2">
                            <div className="w-4 h-4 rounded skeleton" />
                            <div className="h-4 w-12 rounded skeleton" />
                        </div>
                    </div>
                </div>
            ))}
        </div>
    )
}

export default CardsSkeleton