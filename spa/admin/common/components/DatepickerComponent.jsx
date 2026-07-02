import React from "react";
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";

const DatepickerComponent = ({
    dateRange,
    onRangeChange,
    placeholderText = "Select date range",
    width = "w-full",
    calendarIcon,
    minDate = new Date(),
}) => {
    const [startDate, endDate] = dateRange || [];

    return (
        <div className="easycommerce-datepicker-container h-ec-height">
            <div className={`flex items-center relative h-ec-input w-[150px] min-[1440px]:w-[220px] px-3 py-2 gap-2 rounded-lg ${width} 
                border border-ec-table-stock easycommerce-datepicker-shadow hover:border-ec-secondary transition-all duration-300 focus:border-ec-primary focus:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF]`}>
                {calendarIcon && (
                    <img
                        src={calendarIcon}
                        alt="Calendar"
                        className="w-[16px] h-[16px] pointer-events-none"
                    />
                )}
                <DatePicker
                    selected={startDate}
                    onChange={onRangeChange}
                    startDate={startDate}
                    endDate={endDate}
                    selectsRange
                    placeholderText={placeholderText}
                    className={`easycommerce-datepicker w-full font-normal text-sm leading-[26px]
                        font-inter text-ec-body `}
                    minDate={minDate}
                    dateFormat="dd MMM yyyy"
                />
            </div>
        </div>
    );
};

export default DatepickerComponent;
