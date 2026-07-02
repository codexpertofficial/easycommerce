import React, { useEffect, useState } from "react";
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import TableSkeleton from "../../../../common/TableSkeleton";

const TopSelling = ({ range }) => {
    const [topSeller, setTopSeller] = useState([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        setIsLoading(true);
        apiFetch({
            path: addQueryArgs('/easycommerce/v1/dashboard/topsellers', { range }),
        }).then((data) => {
            setIsLoading(false);
            if (data.success) {
                const sellers = data?.data?.sellers;
                if (Array.isArray(sellers)) {
                    setTopSeller(sellers);
                } else if (sellers && typeof sellers === 'object') {
                    setTopSeller(Object.values(sellers));
                } else {
                    setTopSeller([]);
                }
            }
        }).catch((error) => {
            setIsLoading(false);
        });
    }, [range]);

    const handleProductClick = (productId) => {
        // Navigate to orders page with product filter using the new URL pattern
        window.location.hash = `#/orders/product/${productId}`;
    };

    return (
        <>
            {isLoading ? (
                <TableSkeleton numberOfRows={5} SkeletonHeight={35} />
            ) : !Array.isArray(topSeller) || topSeller.length === 0 ? (
                <div className=" text-sm text-ec-body px-[30px] pb-[30px]">
                    No top selling products found.
                </div>
            ) : (
                <div>
                    <div className="flex items-center bg-ec-table-bg py-3 px-3 rounded-lg">
                        <div className="w-[45%] text-sm font-medium text-ec-body capitalize">Product Name</div>
                        <div className="w-[35%] text-sm font-medium text-ec-body capitalize text-center">Items Sold</div>
                        <div className="w-[20%] text-sm font-medium text-ec-body capitalize text-center">Total Sales</div>
                    </div>

                    {topSeller.map((item, index) => (
                        <div key={index} className="flex items-center px-3 py-5 border-b border-[#F8F8F8] last:border-0">
                            <div className="w-[45%]">
                                <button
                                    onClick={() => handleProductClick(item.id)}
                                    title={item.title?.length > 35 ? item.title : null}
                                    className="font-inter text-sm text-ec-body hover:text-ec-primary text-left"
                                >
                                    {item.title && item.title.length > 35 ? item.title.slice(0, 35) + "..." : item.title}
                                </button>
                            </div>
                            <div className="w-[35%] text-sm font-inter text-ec-body text-center">
                                {item.items_sold}
                            </div>
                            <div className="w-[20%] text-sm font-inter text-ec-body text-center">
                                {item.sales}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </>
    );
};

export default TopSelling;
