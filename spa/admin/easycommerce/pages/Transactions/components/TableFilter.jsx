import React from "react";
import { Slot } from '@wordpress/components';
import DropdownField from "../../Orders/components/DropdownField";
import DatepickerComponent from "../../../../common/components/DatepickerComponent";
import TextField from "../../../../common/components/inputs/TextField";

const CalendarIcon = `${EASYCOMMERCE.assets}admin/img/icons/Calendar-icon.png`;

// const contentOptions = [
//     { value: "customer_email", label: "Customer Email" },
//     { value: "order_id", label: "Order Id" },
// ];

const TableFilter = ({
    formState,
    handleSubmit,
    handleInputChange,
    transactionsFiltered,
    resetFilter,
    handleDropdownChange,
    setFormState,
}) => {
    const today = new Date();
    const past30 = new Date(today);
    past30.setDate(today.getDate() - 30);
    const fmt = (d) => d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
    const datePlaceholder = `eg. ${fmt(past30)} - ${fmt(today)} ${today.getFullYear()}`;

    return (
        <div className="flex justify-between items-center 2xl:order-2 xl:order-1">
            <div className="flex items-center justify-between gap-3">
                {transactionsFiltered && (
                    <button
                        className={`font-inter text-base leading-[26px] font-normal text-ec-secondary border-b border-[#737991]`}
                        onClick={resetFilter}
                    >
                        Reset
                    </button>
                )}

                <form
                    className="flex items-center gap-3"
                    onSubmit={(e) => {
                        e.preventDefault();
                        handleSubmit();
                    }}
                >
                <div className="easycommerce-search-container relative flex items-start">
                    <TextField
                        className="w-[190px] 2xl:w-[190px] xl:w-[514px] h-ec-input"
                        name="search"
                        value={formState.search}
                        onChange={handleInputChange}
                        placeholder="Search"
                    />
                </div>

                 <DatepickerComponent
                    //calendarIcon={CalendarIcon}
                    dateRange={[formState.dateForm, formState.dateTo]}
                    onRangeChange={(dates) => {
                        const [start, end] = dates;
                        setFormState((prev) => ({
                            ...prev,
                            dateForm: start,
                            dateTo: end,
                        }));
                    }}
                    placeholderText={datePlaceholder}
                    width="w-[220px] h-ec-input"
                    minDate={new Date("1972-01-01")}
                />
                {/*  <div className="easycommerce-select-container relative flex items-center ">
                    <DropdownField
                        options={contentOptions}
                        placeholder="Search By"
                        width="200px"
                        menuWidth="200px"
                        onChange={(value) =>
                            handleDropdownChange("searchBy", value)
                        }
                    />
                </div> */}
            
                <button
                    type="submit"
                    className="w-[38px] h-ec-input flex justify-center ease-in-out hover:bg-ec-secondary transition duration-300 items-center bg-ec-primary hover:bg-[#6440f2] rounded-lg"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="17"
                        height="13"
                        viewBox="0 0 17 16"
                        fill="none"
                    >
                        <path
                            d="M15.75 14.7188C16.0625 15.0729 16.0729 15.4271 15.7812 15.7812C15.6354 15.9271 15.4583 
                            16 15.25 16C15.0625 16 14.875 15.9271 14.6875 15.7812L10.5 11.5938C9.375 12.5104 8.03125 12.9792 
                            6.46875 13C4.63542 12.9583 3.11458 12.3229 1.90625 11.0938C0.677083 9.86458 0.0416667 8.33333 0 
                            6.5C0.0416667 4.66667 0.666667 3.13542 1.875 1.90625C3.10417 0.677083 4.63542 0.0416667 6.46875 
                            0C8.30208 0.0416667 9.83333 0.677083 11.0625 1.90625C12.2917 3.13542 12.9271 4.66667 12.9688 
                            6.5C12.9479 8.04167 12.4792 9.38542 11.5625 10.5312L15.75 14.7188ZM1.5 6.5C1.54167 7.91667 2.03125 
                            9.09375 2.96875 10.0312C3.90625 10.9688 5.08333 11.4583 6.5 11.5C7.91667 11.4583 9.09375 10.9688 
                            10.0312 10.0312C10.9688 9.09375 11.4583 7.91667 11.5 6.5C11.4583 5.08333 10.9688 3.90625 10.0312 
                            2.96875C9.09375 2.03125 7.91667 1.54167 6.5 1.5C5.08333 1.54167 3.90625 2.03125 2.96875 2.96875C2.03125 
                            3.90625 1.54167 5.08333 1.5 6.5Z"
                            fill="white"
                        />
                    </svg>
                </button>

                {/* Slot for additional table filters */}
                <Slot name="easycommerce.table.filters.extra" />
                </form>
            </div>
        </div>
    );
};

export default TableFilter;
