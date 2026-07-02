import React, { useEffect, useState } from "react";
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import TableSkeleton from "../../../../../common/TableSkeleton";
import AbandonedCartModal from "./components/AbandonedCartModal";

const AbandonedCarts = ({ range = 'last-30' }) => {
    const [data, setData] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [selectedItem, setSelectedItem] = useState(null);

    const getDateRange = (rangeValue) => {
        const today = new Date();
        let fromDate, toDate;

        switch (rangeValue) {
            case 'last-7':
                fromDate = new Date(today);
                fromDate.setDate(fromDate.getDate() - 7);
                toDate = new Date(today);
                break;
            case 'last-30':
                fromDate = new Date(today);
                fromDate.setDate(fromDate.getDate() - 30);
                toDate = new Date(today);
                break;
            case 'this-week':
                fromDate = new Date(today);
                fromDate.setDate(fromDate.getDate() - fromDate.getDay());
                toDate = new Date(fromDate);
                toDate.setDate(toDate.getDate() + 6);
                break;
            case 'this-month':
                fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
            case 'this-year':
                fromDate = new Date(today.getFullYear(), 0, 1);
                toDate = new Date(today.getFullYear(), 11, 31);
                break;
            default:
                fromDate = new Date(today);
                fromDate.setDate(fromDate.getDate() - 30);
                toDate = new Date(today);
        }

        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        return {
            from: formatDate(fromDate),
            to: formatDate(toDate),
        };
    };

    const fetchUpdatedCartData = () => {
        setIsLoading(true);

        const dateRange = getDateRange(range);

        apiFetch({
            path: addQueryArgs('/easycommerce/v1/abandoned-carts', { 
                page: 1, 
                per_page: 5,
                from: dateRange.from,
                to: dateRange.to,
            }),
        }).then((data) => {
            setIsLoading(false);
            if (data.success) {
                setData(data.data.carts);
            }
        }).catch(() => {
            setIsLoading(false);
        });
    };

    useEffect(() => {
        fetchUpdatedCartData();
    }, [range]);

    return (
        <>
            {isLoading ? (
                <TableSkeleton numberOfRows={5} SkeletonHeight={35} />
            ) : data.length === 0 ? (
                <div className=" text-sm text-ec-body px-[30px] pb-[30px]">
                    No abandoned carts found.
                </div>
            ) : (
                <div className="h-fit">
                    <div className="flex items-center bg-ec-table-bg p-3 rounded-lg">
                        <div className="text-sm font-medium text-ec-body capitalize w-[45%]">Customer</div>
                        <div className="w-[20%] text-sm font-medium text-ec-body capitalize text-center">Products</div>
                        <div className="w-[20%] text-sm font-medium text-ec-body capitalize text-center">Total</div>
                        <div className="w-[15%]"></div>
                    </div>

                    {data.map((item, index) => (
                        <div key={index} className="flex items-center p-3 border-b border-[#F8F8F8] last:border-0">
                            <div className="flex flex-col gap-1 w-[45%]">
                                <span className="text-sm font-normal text-ec-body">{item.name}</span>
                                <span className="text-sm font-normal text-[#7A7A99]">{item.email}</span>
                            </div>
                            <div className="w-[20%] text-sm font-normal text-ec-body text-center">{item.items}</div>
                            <div className="w-[20%] text-sm font-normal text-ec-body text-center">{item.total}</div>
                            <div className="w-[15%] text-center">
                                <button
                                    onClick={() => setSelectedItem(item)}
                                    className="font-inter text-ec-primary hover:underline text-sm focus:text-ec-primary"
                                >
                                    View
                                </button>
                            </div>
                        </div>
                    ))}
                    { selectedItem && (
                        <AbandonedCartModal
                            item={selectedItem}
                            onClose={() => setSelectedItem(null)}
                            onDeleted={() => {
                                setSelectedItem(null);
                                fetchUpdatedCartData();
                            }}
                        />
                    )}
                </div>
            )}
        </>
    );
};

export default AbandonedCarts;
