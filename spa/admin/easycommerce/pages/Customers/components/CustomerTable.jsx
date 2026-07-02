import React from "react";

const View = `${EASYCOMMERCE.assets}admin/img/icons/View.png`;

const colors = ['#0B60FF', '#613DFF', '#FF7C1F', '#00BA00', '#FF0099', '#0086F3', '#D75EFF'];

const hashString = (str) => {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        const char = str.charCodeAt(i);
        hash = (hash << 5) - hash + char;
        hash = hash & hash;
    }
    return Math.abs(hash);
};

const getRandTxtBgColor = (customerName) => {
    const hash = hashString(customerName);
    const index = hash % colors.length;
    const backgroundColor = colors[index];
    return { backgroundColor };
};

const columnList = [
    {
        title: "Name",
        name: "title",
        width: "20",
    },
    {
        title: "Email",
        name: "email",
        width: "15",
    },
    {
        title: "Customer Since",
        name: "customer_since",
        width: "12",
    },
    {
        title: "Total Orders",
        name: "total_orders",
        width: "10",
    },
    {
        title: "Lifetime Value",
        name: "lifetime_value",
        width: "10",
    },
    {
        title: "Avg. Order Value",
        name: "avg_order_value",
        width: "12",
    },
    {
        title: "Last Order",
        name: "last_order",
        width: "10",
    },
 
];

const CustomerTable = ({
    tableColumns,
    customers,
    isLoading,
}) => {
    return (
        <>
            <div className="w-full overflow-y-hidden xl:overflow-x-auto">
                <table className="min-w-full xl:min-w-[1300px] w-full border-collapse border-spacing-0"> 
                    <thead>
                        {!isLoading && customers.length > 0 && (
                            <tr className="bg-ec-modal">
                                {columnList.map(
                                    (column) =>
                                        tableColumns.includes(column.name) && (
                                            <th
                                                key={column.name}
                                                className="font-inter font-normal text-sm text-ec-title text-left py-3 pl-5 first:rounded-l-lg last:rounded-r-lg rtl:text-right"
                                            >
                                                {column.name === "title" ? (
                                                    <>
                                                        <span className={`font-inter font-normal text-sm text-ec-title rtl:pr-4 lg:w-[${column.width}%]`}>
                                                            {column.title}
                                                        </span>
                                                        
                                                    </>
                                                    ) : (
                                                        <span className={`font-inter font-normal text-sm text-ec-title lg:w-[${column.width}%]`}>
                                                            {column.title}
                                                        </span>
                                                )}                        
                                            </th>
                                        )
                                        )}
                            </tr>
                        )}
                    </thead>
                    <tbody>
                        {!isLoading &&
                            customers.length > 0 &&
                            customers.map((customer) => (
                                <tr
                                    key={customer.id}
                                    className = "border-b border-ec-table-stock h-[80px] transition-shadow hover:shadow-[0px_4px_40px_0px_#00000014] group"
                                >
                                    {tableColumns.includes("title") && (
                                        <td className="leading-[26px] pl-5 w-[20%] rtl:pr-5">
                                            <div className="flex items-center gap-4 justify-start mt-1 w-full">
                                                <div className="flex items-center gap-4 focus:shadow-none grow">
                                                    <div className="flex items-center gap-4">
                                                        {customer.photo ? (
                                                            <div className="flex w-10 h-10">
                                                                <img
                                                                    src={customer.photo}
                                                                    alt={customer.name}
                                                                    className="border border-solid border-ec-table-stock rounded-md object-fill pointer-events-none"
                                                                />
                                                            </div>
                                                            
                                                        ) : (
                                                            (() => {
                                                                const {
                                                                    backgroundColor,
                                                                } = getRandTxtBgColor(
                                                                    customer.name
                                                                );
                                                                return (
                                                                    <div
                                                                        className="w-10 h-10 rounded-md flex items-center text-white text-base font-normal justify-center uppercase"
                                                                        style={{
                                                                            backgroundColor,
                                                                        }}
                                                                    >
                                                                        {
                                                                            customer
                                                                                .name[0]
                                                                        }
                                                                    </div>
                                                                );
                                                            })()
                                                        )}
                                                    </div>
                                                    <div className="block w-full h-10 relative">
                                                        <span className="text-sm text-ec-body font-inter font-normal absolute top-1/2 -translate-y-1/2 group-hover:top-0 group-hover:translate-y-0 duration-300 rtl:left-auto rtl:right-0">
                                                            {customer.name}
                                                        </span>
                                                        
                                                        <div className="invisible group-hover:visible opacity-0 group-hover:opacity-100 duration-300 absolute bottom-0"> 
                                                            <div className="flex items-center gap-1.5 font-inter font-normal text-xs text-ec-light-black">
                                                                <a className="hover:text-ec-primary duration-300 hover:cursor-pointer" href={`#/customers/${customer.id}`} >View</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    
                                        </td>
                                    )}
                                    {tableColumns.includes("email") && (
                                        <td className="text-sm text-ec-body font-inter font-normal lg:w-[15%] pl-5">
                                            {customer.email}
                                        </td>
                                    )}
                                    {tableColumns.includes(
                                        "customer_since"
                                    ) && (
                                        <td className="font-inter text-sm text-ec-body lg:w-[12%] pl-5">
                                            {customer.since}
                                        </td>
                                    )}
                                    {tableColumns.includes("total_orders") && (
                                        <td className="font-inter font-normal text-sm text-ec-body lg:w-[10%] pl-5">
                                            {customer.orders ?? "0"}
                                        </td>
                                    )}
                                    {tableColumns.includes(
                                        "lifetime_value"
                                    ) && (
                                        <td className="font-inter font-normal text-sm text-ec-body lg:w-[10%] pl-5">
                                            {customer.ltv}
                                        </td>
                                    )}
                                    {tableColumns.includes(
                                        "avg_order_value"
                                    ) && (
                                        <td className="ont-inter font-normal text-sm text-ec-body lg:w-[12%] pl-5">
                                            {customer.aov}
                                        </td>
                                    )}
                                    {tableColumns.includes("last_order") && (
                                        <td className="font-inter font-normal text-ec-body text-sm lg:w-[10%] pl-5">
                                            {customer.last_order ? customer.last_order : "N/A"}
                                        </td>
                                    )}
                                </tr>
                            ))}
                    </tbody>
                </table>
            </div>
            {!isLoading && customers.length === 0 && (
                <div className="p-5 mt-5 ">
                    <p className="text-center text-lg text-[#4a5568]">
                        No customers found
                    </p>
                </div>
            )}
        </>
    );
};

export default CustomerTable;
