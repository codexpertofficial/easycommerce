import React, { useState, useEffect } from "react";
import TableSkeleton from "../../../../../admin/common/TableSkeleton";
import Pagination from "../../../../../admin/common/components/Pagination";

const defaultDownloadIcon = `${EASYCOMMERCE.assets}public/img/icons/dashboard-default-download-icon.png`;
const hoverDownloadIcon = `${EASYCOMMERCE.assets}public/img/icons/dashboard-hover-download-icon.png`;

const Downloads = () => {
    const [ordersData, setOrdersData] = useState([]);
    const [downloads, setDownloads] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [totalPage, setTotalPage] = useState(1);
    const [page, setPage] = useState(1);
    const [postPerPage, setPostPerPage] = useState(10);

    useEffect(() => {
        const handleClick = (e) => {
            const link = e.target.closest('a[href^="#/downloads/page/"]');
            if (link) {
                e.preventDefault();
                e.stopPropagation();
                const href = link.getAttribute('href');
                const match = href.match(/\/page\/(\d+)$/);
                if (match) {
                    setPage(Number(match[1]));
                }
            }
        };

        document.addEventListener('click', handleClick, true);
        return () => document.removeEventListener('click', handleClick, true);
    }, []);

    useEffect(() => {
        if (ordersData.length === 0) {
            fetch(`${EASYCOMMERCE.rest_base}/me/orders?per_page=9999`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': EASYCOMMERCE.nonce,
                },
            })
                .then((res) => res.json())
                .then((data) => {
                    if (data.success && data.data?.orders) {
                        setOrdersData(data.data.orders);
                    }
                })
                .catch((error) => {
                    console.error("Error fetching orders:", error);
                });
        }
    }, [ordersData.length]);

    const getFileIcon = (type) => {
        const iconUrl = `${EASYCOMMERCE.assets}common/img/file-extension/`;
        const defaultIcon = `${iconUrl}default.png`;
        const fileTypes = [
            "archive",
            "audio",
            "bin",
            "code",
            "doc",
            "excel",
            "jpg",
            "mp3",
            "mp4",
            "pdf",
            "png",
            "rar",
            "txt",
            "video",
            "word",
            "xls",
            "xlsx",
            "zip",
        ];

        return fileTypes.includes(type) ? `${iconUrl}${type}.png` : defaultIcon;
    };

    useEffect(() => {
        setIsLoading(true);
        fetch(`${EASYCOMMERCE.rest_base}/me/downloads?page=${page}&per_page=${postPerPage}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then((resp) => resp.json())
            .then((data) => {
                setIsLoading(false);

                if (data.success && data.data?.downloads) {
                    setDownloads(data.data.downloads);
                    setTotalPage(data.data.total_pages);
                }
            });
    }, [page, postPerPage]);

    const getOrderStatus = (orderId) => {
        const order = ordersData.find(order => order.id === orderId);
        return order ? order.status : null;
    };

    const completedDownloads = downloads.filter((download) => {
        const orderStatus = getOrderStatus(download.order_id);
        return orderStatus === 'completed';
    });

    return (
        <>
            <div className="easycommerce-dashboard-section pb-[55px] flex flex-col gap-4">
                <h3 className="easycommerce-dashboard-section-title !text-lg sm:!text-2xl">
                    Downloads
                </h3>
                <div className="w-full">
                    {!isLoading ? (
                        <>
                            {completedDownloads.length > 0 ? (
                                <table className="w-full border-none m-0">
                                    <thead>
                                        <tr className="h-[42px]">
                                            <th className="font-inter font-medium text-left rtl:text-right rtl:pr-4 text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0 pl-0">
                                                File Name
                                            </th>
                                            <th className="font-inter font-medium text-left rtl:text-right text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0">
                                                Size
                                            </th>
                                            <th className="font-inter font-medium text-left rtl:text-right text-base leading-[26px] text-ec-placeholder border-b border-b-ec-border border-r-0">
                                                Order ID
                                            </th>
                                            <th className="border-b border-b-ec-border border-r-0"></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        {completedDownloads.map((download, index) => (
                                            <tr
                                                className={`h-[76px] ${
                                                    index === completedDownloads.length - 1
                                                        ? "last-row"
                                                        : ""
                                                }`}
                                            >
                                                <td className="font-inter font-normal text-left rtl:text-right rtl:pr-4 text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0 pl-0">
                                                    <span className="flex justify-start items-center gap-3">
                                                        <span>
                                                            <img
                                                                src={getFileIcon(
                                                                    download.type
                                                                )}
                                                                alt={`${download.type} icon`}
                                                                className="w-9 h-9 pointer-events-none"
                                                            />
                                                        </span>
                                                        <span>{download.name}</span>
                                                    </span>
                                                </td>
                                                <td className="font-inter font-normal text-left rtl:text-right text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0">
                                                    {download.size}
                                                </td>
                                                <td className="font-inter font-normal text-left rtl:text-right text-base leading-[26px] text-ec-body border-b border-b-ec-border border-r-0">
                                                    {download.order_id}
                                                </td>
                                                <td className="border-b border-b-ec-border border-r-0">
                                                    <a
                                                        href={download.url}
                                                        className="easycommerce-dashboard-download-btn flex justify-start items-center gap-[7px] group"
                                                    >
                                                        <span>
                                                            <img
                                                                src={
                                                                    defaultDownloadIcon
                                                                }
                                                                alt="download-icon"
                                                                className="!w-[14px] h-[14px] pointer-events-none block group-hover:hidden"
                                                            />
                                                            <img
                                                                src={
                                                                    hoverDownloadIcon
                                                                }
                                                                alt="download-icon"
                                                                className="!w-[14px] h-[14px] pointer-events-none hidden group-hover:block"
                                                            />
                                                        </span>

                                                        <span>Download</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            ) : (
                                <div>No downloads available</div>
                            )}
                        </>
                    ) : (
                        <TableSkeleton numberOfRows={10} SkeletonHeight={30} />
                    )}
                </div>
            </div>
            {totalPage > 1 && (
                <Pagination
                    baseSlug="downloads"
                    current={page}
                    total={totalPage}
                />
            )}
        </>
    );
};

export default Downloads;
