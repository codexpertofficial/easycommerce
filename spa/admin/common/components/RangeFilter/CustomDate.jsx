import React, { useState } from "react";
import DatePicker from "react-datepicker";

import "react-datepicker/dist/react-datepicker.css";
import DatepickerComponent from "../DatepickerComponent";

const CustomDate = ({ customRange, handleCustomRange }) => {
    const [startDate, setStartDate] = useState(
        customRange.from
            ? new Date(customRange.from.split("/").reverse().join("-"))
            : null
    );
    const [endDate, setEndDate] = useState(
        customRange.to
            ? new Date(customRange.to.split("/").reverse().join("-"))
            : null
    );

    const formatDate = (date) => {
        if (!date) return "";
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };


    return (
        <div className="mt-3 pt-3 border-t border-ec-border">
            <h4 className="font-inter font-medium text-sm text-center text-ec-body mb-2">
                Custom Date
            </h4>
            <div className="flex items-center gap-2 justify-end">
                {/* <div>
                    <DatePicker
                        selected={startDate}
                        placeholderText="From"
                        onChange={(date) => {
                            setStartDate(date);
                            handleCustomRange("from", formatDate(date));
                        }}
                    />
                </div>

                <div>
                    <DatePicker
                        selected={endDate}
                        placeholderText="To"
                        onChange={(date) => {
                            setEndDate(date);
                            handleCustomRange("to", formatDate(date));
                        }}
                    />
                </div> */}

                <DatepickerComponent
                    dateRange={[startDate, endDate]}
                    onRangeChange={(dates) => {
                        const [start, end] = dates;
                        setStartDate(start);
                        setEndDate(end);

                        if (start) {
                            handleCustomRange("from", formatDate(start));
                        }
                        if (end) {
                            handleCustomRange("to", formatDate(end));
                        }
                    }}

                    placeholderText="Select custom range"
                    width="w-full"
                    minDate={new Date("1972-01-01")}
                />
               
            </div>
        </div>
    );
};

export default CustomDate;
