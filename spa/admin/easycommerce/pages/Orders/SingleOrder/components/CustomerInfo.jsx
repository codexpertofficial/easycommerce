import React from "react";
import { __ } from "@wordpress/i18n";

// Icons
const dateIcon = `${EASYCOMMERCE.assets}admin/img/icons/order/date.png`;
const totalOrderIcon = `${EASYCOMMERCE.assets}admin/img/icons/order/total.png`;
const avrageOrderIcon = `${EASYCOMMERCE.assets}admin/img/icons/order/average.png`;
const lastOrderIcon = `${EASYCOMMERCE.assets}admin/img/icons/order/last.png`;
const dummyUser = `${EASYCOMMERCE.assets}admin/img/preloader-Image.png`;

const CustomerInfo = ({ customer }) => {
    return (
        <div className="bg-white text-center rounded-2xl">
            <h3
                className="text-left text-ec-title text-xl font-medium font-inter leading-8 pb-4 
                border-b border--ec-table-stock pt-4 px-6"
            >
                {__("Customer", "easycommerce")}
            </h3>
            <div className="pt-8 px-6 py-6">
                <div className='flex gap-5'>
                    <div className="border border-[#E9E4FF] p-1 rounded-lg inline-block mb-[10px]">
                        {customer.photo ? (
                            <img
                                src={customer.photo}
                                className="w-[100px] h-[100px] pointer-events-none"
                            />
                        ) : (
                            <img
                                src={dummyUser}
                                className="w-[100px] h-[100px] object-contain pointer-events-none"
                            />
                        )}
                    </div>
                    <div className="flex flex-col gap-3 items-start">
                        <h2 className=" text-ec-title text-xl font-inter leading-[30px] font-medium">
                            {customer.name}
                        </h2>
                        <p className="text-ec-light-black text-sm font-inter font-normal">
                            {customer.email}
                        </p>
                        <a
                            href={`#/customers/${customer.id}`}
                            className="w-[73px] h-[26px] flex justify-center items-center gap-2 text-ec-primary border border-ec-primary p-2 text-sm font-normal font-inter 
                            leading-4 rounded-[4px] hover:text-white hover:bg-ec-accent hover:bg-ec-primary focus:text-white focus-bg-ec-primary
                            focus:shadow-none active:text-white active-bg-ec-primary active:shadow-none focus:outline-none"
                        >
                            {__("View", "easycommerce")}
                            <svg
                                width="15"
                                height="11"
                                viewBox="0 0 15 11"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                className="fill-current"
                            >
                                <path d="M7.50053 10.4996C5.93645 10.5156 4.4008 10.0732 3.07798 9.22418C1.75584 8.37583 0.703375 7.15747 0.0471403 5.71603C-0.0160284 5.5788 -0.0153977 5.42042 0.0471403 5.2832C0.704733 3.84301 1.75775 2.62586 3.07992 1.77768C4.40143 0.929326 5.93643 0.485599 7.49988 0.500356C9.06333 0.485608 10.5983 0.929342 11.9205 1.77704C13.242 2.62539 14.2951 3.84309 14.9526 5.28322C15.0158 5.42044 15.0158 5.57883 14.9526 5.71668C14.2963 7.15752 13.244 8.37517 11.9218 9.22418C10.5996 10.0725 9.06462 10.5156 7.50053 10.4996ZM1.06675 5.50058C3.6105 10.774 11.3893 10.7727 13.9325 5.50058C11.3779 0.225792 3.62134 0.227105 1.06675 5.50058ZM7.49988 8.69585C6.66478 8.70162 5.86253 8.36626 5.27373 7.76478C4.68499 7.16394 4.35968 6.34702 4.37104 5.49928C4.41147 1.41396 10.429 1.12669 10.6481 5.49992C10.6469 6.34699 10.3146 7.15936 9.72459 7.75836C9.1346 8.35726 8.33437 8.69457 7.49988 8.69585ZM7.50809 3.32997C6.94146 3.33125 6.3982 3.56146 5.99772 3.96863C5.59785 4.37646 5.3736 4.9292 5.37486 5.50438C5.37739 6.70284 6.3363 7.67237 7.51692 7.66975C10.3172 7.571 10.3223 3.42879 7.50809 3.32997Z" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div>
                    <ul>
                        <li className="h-[60px] mb-0 p-4 border-b border-dashed border-ec-table-stock text-left flex items-center justify-between">
                            <div className="flex items-center" >
                                <img src={dateIcon} alt={__("date", "easycommerce")} className="h-5 w-5 rounded-md mr-2 flex" />
                                <p className="text-ec-title font-normal text-base font-inter leading-[26px]">
                                    {__("Customer since", "easycommerce")}
                                </p>
                            </div>

                            <p className="text-ec-body font-normal text-sm font-inter leading-[26px]">
                                {customer.since}
                            </p>
                        </li>
                        <li className="h-[60px] mb-0 p-4 border-b border-dashed border-ec-table-stock text-left flex items-center justify-between">
                            <div className="flex items-center" >
                                <img src={totalOrderIcon} alt={__("date", "easycommerce")} className="h-5 w-5 rounded-md mr-2 flex" />
                                <p className="text-ec-title font-normal text-base font-inter leading-[26px]">
                                    {__("Total Orders", "easycommerce")}
                                </p>
                            </div>

                            <p className="text-ec-body font-normal text-sm font-inter leading-[26px]">
                                {customer.order_count}
                            </p>
                        </li>
                        <li className="h-[60px] mb-0 p-4 border-b border-dashed border-ec-table-stock text-left flex items-center justify-between">
                            <div className="flex items-center" >
                                <img src={avrageOrderIcon} alt={__("date", "easycommerce")} className="h-5 w-5 rounded-md mr-2 flex" />
                                <p className="text-ec-title font-normal text-base font-inter leading-[26px]">
                                    {__("Avg. Order Value", "easycommerce")}
                                </p>
                            </div>

                            <p className="text-ec-body font-normal text-sm font-inter leading-[26px]">
                                {customer.aov}
                            </p>
                        </li>

                        <li className="h-[60px] mb-0 p-4 text-left flex items-center justify-between">
                            <div className="flex items-center" >
                                <img src={lastOrderIcon} alt={__("date", "easycommerce")} className="h-5 w-5 rounded-md mr-2 flex" />
                                <p className="text-ec-title font-normal text-base font-inter leading-[26px]">
                                   {__("Last Order", "easycommerce")}
                                </p>
                            </div>

                            <p className="text-ec-body text-sm font-normal font-inter leading-[26px]">
                                {customer.last_order || __("N/A", "easycommerce")}
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    );
};

export default CustomerInfo;
