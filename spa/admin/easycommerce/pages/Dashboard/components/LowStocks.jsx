import React, { useEffect, useState } from "react";
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import TableSkeleton from "../../../../common/TableSkeleton";

const statusColors = {
    low_stock: {
        color: "var(--color-ec-cancelledText)",
        background: "var(--color-ec-cancelledBg)",
    }
};

const LowStocks = () => {
    const [lowStock, setLowStock] = useState([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        apiFetch({
            path: '/easycommerce/v1/dashboard/lowstock',
        }).then((data) => {
            setIsLoading(false);
            if (data.success) {
                setLowStock(data.data.stock);
            }
        }).catch(() => {
            setIsLoading(false);
        });
    }, []);

    return (
        <>
            {isLoading ? (
                <TableSkeleton numberOfRows={5} SkeletonHeight={35} />
            ) : lowStock.length === 0 ? (
                <div className=" text-sm text-ec-body px-[30px] pb-[30px]">
                    {__( 'No low stock products found.', 'easycommerce' )}
                </div>
            ): (
                <div>
                    <div className="flex items-center bg-ec-table-bg py-3 px-3 rounded-lg">
                        <div className="w-[45%] text-sm font-medium text-ec-body capitalize">{__( 'Product Name', 'easycommerce' )}</div>
                        <div className="w-[25%] text-sm font-medium text-ec-body capitalize text-center">{__( 'Quantity', 'easycommerce' )}</div>
                        <div className="w-[30%] text-sm font-medium text-ec-body capitalize text-center">{__( 'Out of stock', 'easycommerce' )}</div>
                    </div>

                    {/* Rows */}
                    {lowStock.map((item, index) => (
                        <div key={index} className="flex items-center px-3 py-5 border-b border-[#F8F8F8] last:border-0">
                            <div className="w-[45%]">
                                <p
                                    title={item.title.length > 25 ? item.title : null}
                                    className="text-sm font-normal text-[#272435]"
                                >
                                    {item.title.length > 25 ? item.title.slice(0, 25) + "..." : item.title}
                                </p>
                            </div>
                            <div className="w-[25%] text-center">
                                <span className="text-sm font-medium text-[#272435]">{item.stock}</span>
                            </div>
                            <div className="w-[30%] text-center">
                                <span
                                    className="px-4 py-1 rounded-[100px] text-xs font-normal"
                                    style={{
                                        color: statusColors[item.status]?.color,
                                        background: statusColors[item.status]?.background,
                                    }}
                                >
                                    {item.days}
                                </span>
                            </div>
                        </div>
                    ))}
                </div>
               
            )}
        </>
    );
};

export default LowStocks;
