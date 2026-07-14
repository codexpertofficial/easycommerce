import React, { useState, useEffect } from "react";
import TableSkeleton from "../../../../../admin/common/TableSkeleton";
import Pagination from "../../../../../admin/common/components/Pagination";
import EmptyState from "../../common/EmptyState";

const Downloads = () => {
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

    // `/me/downloads` already returns only entitled (paid) downloads; render as-is.
    const completedDownloads = downloads;

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
                                <div className="w-full border border-ec-border rounded-2xl overflow-x-auto bg-white">
                                    <table className="w-full min-w-[520px] border-none m-0">
                                        <thead className="bg-ec-table-bg">
                                            <tr>
                                                <th className="font-inter font-semibold text-xs uppercase tracking-wide text-left rtl:text-right text-ec-light-black py-3.5 px-5 border-0">
                                                    File Name
                                                </th>
                                                <th className="font-inter font-semibold text-xs uppercase tracking-wide text-left rtl:text-right text-ec-light-black py-3.5 px-5 border-0">
                                                    Size
                                                </th>
                                                <th className="font-inter font-semibold text-xs uppercase tracking-wide text-left rtl:text-right text-ec-light-black py-3.5 px-5 border-0">
                                                    Order ID
                                                </th>
                                                <th className="py-3.5 px-5 border-0"></th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            {completedDownloads.map((download, index) => (
                                                <tr
                                                    key={index}
                                                    className={`transition-colors duration-150 hover:bg-ec-active ${
                                                        index === completedDownloads.length - 1
                                                            ? "last-row"
                                                            : ""
                                                    }`}
                                                >
                                                    <td className="font-inter text-sm text-left rtl:text-right text-ec-body py-4 px-5 border-0 border-b border-b-ec-border/70">
                                                        <span className="flex justify-start items-center gap-3">
                                                            <span className="shrink-0 w-10 h-10 flex items-center justify-center rounded-xl bg-ec-accent">
                                                                <img
                                                                    src={getFileIcon(
                                                                        download.type
                                                                    )}
                                                                    alt={`${download.type} icon`}
                                                                    className="w-6 h-6 pointer-events-none"
                                                                />
                                                            </span>
                                                            <span className="font-medium text-ec-title break-all">{download.name}</span>
                                                        </span>
                                                    </td>
                                                    <td className="font-inter text-sm text-left rtl:text-right text-ec-body py-4 px-5 border-0 border-b border-b-ec-border/70">
                                                        {download.size}
                                                    </td>
                                                    <td className="font-inter text-sm text-left rtl:text-right text-ec-body py-4 px-5 border-0 border-b border-b-ec-border/70">
                                                        #{download.order_id}
                                                    </td>
                                                    <td className="py-4 px-5 border-0 border-b border-b-ec-border/70 text-right rtl:text-left">
                                                        <a
                                                            href={download.url}
                                                            className="inline-flex justify-center items-center gap-2 px-4 py-2 rounded-xl bg-ec-primary text-white font-inter font-medium text-sm no-underline hover:bg-ec-secondary transition-colors duration-200"
                                                        >
                                                            <svg className="w-4 h-4" data-slot="icon" fill="none" stroke="currentColor" strokeWidth="1.8" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                            </svg>
                                                            Download
                                                        </a>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <div className="w-full border border-ec-border rounded-2xl bg-white">
                                    <EmptyState
                                        title="No downloads available"
                                        message="Files from your purchases will appear here."
                                        icon={
                                            <svg className="w-8 h-8" data-slot="icon" fill="none" stroke="currentColor" strokeWidth="1.5" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                            </svg>
                                        }
                                    />
                                </div>
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
