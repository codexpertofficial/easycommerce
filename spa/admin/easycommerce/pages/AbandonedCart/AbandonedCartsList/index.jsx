import React, { useEffect, useState } from "react";
import { applyFilters } from '@wordpress/hooks';
import "./style.css";
import THead from "../components/Table/THead";
import TBody from "../components/Table/TBody";
import Pagination from "../../../../common/components/Pagination";
import TableSkeleton from "../../../../common/TableSkeleton";
import NotFound from "../components/NotFound";
import ActionBar from "../components/ActionBar";
import TableFilter from "../../Transactions/components/TableFilter";
import CleanAbandonedCartModal from "../components/CleanAbandonedCartModal";
import AbandonedCartModal from "../../Dashboard/components/AbandonedCarts/components/AbandonedCartModal";
import { toast } from "react-toastify";

const TAB_STORAGE_KEY = "easycommerce_abandoned_cart_active_tab";

const tabOptions = [
    { key: "all", label: "All", countStyle: "bg-ec-allBg" },
    { key: "not_contracted", label: "Not Contacted", countStyle: "bg-ec-notContractedBg text-ec-notContractedText" },
    { key: "contracted", label: "Contacted", countStyle: "bg-ec-contractedBg text-ec-contractedText" },
    { key: "recovered", label: "Recovered", countStyle: "bg-ec-recoveredBg text-ec-recoveredText" },
];

const AbandonedCartsList = ({
    page,
    perPage,
    columnList,
    deleteAbandonedCart,
    refreshList,
    setRefreshList,
    abandonedCarts,
    setAbandonedCarts,
    formState,
    setFormState,
    cartsFiltered,
    searchTrigger,
    handleInputChange,
    handleDropdownChange,
    handleSubmit,
    resetFilter
}) => {
    const [activeTab, setActiveTab] = useState(() => {
        const saved = localStorage.getItem(TAB_STORAGE_KEY);
        return saved && tabOptions.some(tab => tab.key === saved) ? saved : "all";
    });

    const [statusCounts, setStatusCounts] = useState({});
    const [isLoading, setIsLoading] = useState(true);
    const [totalPage, setTotalPage] = useState(1);
    const [selectedCarts, setSelectedCarts] = useState([]);
    const [isStatusLoaded, setIsStatusLoaded] = useState(false);
    const [isModalVisible, setIsModalVisible] = useState(false);
    const [reminderCart, setReminderCart] = useState(null);

    // Fetch status counts (used for tabs)
    const fetchStatusCounts = () => {
        fetch(`${EASYCOMMERCE.rest_base}/abandoned-carts?page=${page}&per_page=${perPage}`, {
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then(res => res.json())
            .then(data => {
                setStatusCounts(data.data?.statuses || {});
                setIsStatusLoaded(true);
            })
            .catch(() => setIsStatusLoaded(true));
    };

    useEffect(() => {
        fetchStatusCounts();
    }, []);

    // Date formatting helper
    const convertDateFormat = (dateString) => {
        if (!dateString) return "";
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, "0");
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const year = date.getFullYear();
        return `${day}-${month}-${year}`;
    };

    // Main data fetcher
    const fetchAbandonedCarts = () => {
        setIsLoading(true);

        const searchValue = formState?.search?.trim() || "";
        const fromDate = convertDateFormat(formState?.dateForm);
        const toDate = convertDateFormat(formState?.dateTo);

        let url = `${EASYCOMMERCE.rest_base}/abandoned-carts?page=${page}&per_page=${perPage}`;

        if (activeTab !== "all") {
            url += `&type=${activeTab}`;
        }

        if (cartsFiltered) {
            if (searchValue) url += `&search=${encodeURIComponent(searchValue)}`;
            if (fromDate) url += `&from=${fromDate}`;
            if (toDate) url += `&to=${toDate}`;
        }

        fetch(url, {
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then(res => res.json())
            .then(data => {
                if (data.success && Array.isArray(data.data?.carts)) {
                    setAbandonedCarts(data.data.carts);
                    setTotalPage(data.data.total_pages || 1);
                    if (data.data.statuses) {
                        setStatusCounts(data.data.statuses);
                    }
                } else {
                    setAbandonedCarts([]);
                    setTotalPage(1);
                }
            })
            .finally(() => setIsLoading(false));
    };

    useEffect(() => {
        if (isStatusLoaded) {
            fetchAbandonedCarts();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [activeTab, isStatusLoaded, refreshList, searchTrigger]);

    // Allow external filtering via WordPress hooks
    const filteredCarts = applyFilters('easycommerce.abandoned.carts.list', abandonedCarts);

    // Modal handlers
    const openCleanModal = () => setIsModalVisible(true);
    const closeModal = () => setIsModalVisible(false);

    const cleanInvalidAbandonedCarts = () => {
        closeModal();
        fetch(`${EASYCOMMERCE.rest_base}/abandoned-carts/clean-invalid`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then(res => res.json())
            .then(response => {
                if (response.success && response.data.status === "cleaned") {
                    setAbandonedCarts([]);
                    setRefreshList(prev => !prev);
                    toast.success(response.data.message);
                } else if (response.data.status === "empty") {
                    toast.info(response.data.message);
                } else {
                    toast.error(response.data.message);
                }
            });
    };

    const cleanAllAbandonedCarts = () => {
        closeModal();
        fetch(`${EASYCOMMERCE.rest_base}/abandoned-carts/clean-all`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then(res => res.json())
            .then(response => {
                if (response.success && response.data.status === "cleaned") {
                    setAbandonedCarts([]);
                    setRefreshList(prev => !prev);
                    toast.success(response.data.message);
                } else if (response.data.status === "empty") {
                    toast.info(response.data.message);
                } else {
                    toast.error(response.data.message);
                }
            });
    };

    const abandonedBg = EASYCOMMERCE.assets + 'common/img/clean_abandoned.png';

    return (
        <div className="relative min-h-[calc(100vh-220px)] flex flex-col pb-16">
            {/* Blur background when modal is open */}
            <div className={`${isModalVisible ? "blur-sm pointer-events-none" : ""}`}>
                {isStatusLoaded && (
                    <div className="flex xl:flex-wrap flex-row justify-between items-center mb-4 gap-4">
                        <div className="w-max flex gap-4 flex-wrap 2xl:order-1 xl:order-2">
                            {selectedCarts.length > 0 ? (
                                <ActionBar
                                    selectedCarts={selectedCarts}
                                    setSelectedCarts={setSelectedCarts}
                                    setRefreshList={setRefreshList}
                                    statusCounts={fetchStatusCounts}
                                />
                            ) : (
                                <>
                                    <div className="flex gap-4 flex-wrap border-b-2 border-[#F0EDFB] xl:mt-4">
                                        {tabOptions.map((tab) => {
                                            const isActive = activeTab === tab.key;
                                            const count = statusCounts?.[tab.key] ?? 0;

                                            return (
                                                <button
                                                    key={tab.key}
                                                    onClick={() => {
                                                        setActiveTab(tab.key);
                                                        localStorage.setItem(TAB_STORAGE_KEY, tab.key);
                                                        window.location.hash = '#/abandoned-cart';
                                                    }}
                                                    className={`h-9 relative flex items-center gap-[3px] font-inter text-sm text-ec-body transition-colors duration-300
                                                        ${isActive ? "after:absolute after:bottom-[-2px] after:left-0 after:right-0 after:h-[2px] after:bg-ec-primary" : ""}`}
                                                >
                                                    <span>{tab.label}</span>
                                                    <span className={`text-xs font-medium px-2 py-0.5 rounded-full ${tab.countStyle}`}>
                                                        {count}
                                                    </span>
                                                </button>
                                            );
                                        })}
                                    </div>

                                   <div className="flex gap-4 flex-wrap xl:mt-4">
                                        <button
                                            type="button"
                                            onClick={openCleanModal}
                                            className="text-ec-primary font-medium text-base underline ml-2"
                                        >
                                        Clean
                                        </button>
                                    </div>

                                </>
                            )}
                        </div>

                        {selectedCarts.length === 0 && (
                            <div className="flex items-center gap-6 2xl:order-2 xl:order-1">
                                <TableFilter
                                    formState={formState}
                                    handleSubmit={handleSubmit}
                                    handleInputChange={handleInputChange}
                                    transactionsFiltered={cartsFiltered}
                                    resetFilter={resetFilter}
                                    handleDropdownChange={handleDropdownChange}
                                    setFormState={setFormState}
                                />
                            </div>
                        )}
                    </div>
                )}

                {isLoading ? (
                    <TableSkeleton numberOfRows={10} SkeletonHeight={30} />
                ) : (
                    <>
                        {filteredCarts.length > 0 ? (
                            <>
                                <div className="w-full overflow-y-hidden xl:overflow-x-auto">
                                    <table className="min-w-full xl:min-w-[1300px] w-full border-collapse border-spacing-0">
                                        <THead
                                            columnList={columnList}
                                            allChecked={filteredCarts.length > 0 && selectedCarts.length === filteredCarts.length}
                                            toggleAll={() => {
                                                if (selectedCarts.length === filteredCarts.length) {
                                                    setSelectedCarts([]);
                                                } else {
                                                    setSelectedCarts(filteredCarts.map(cart => cart.hash));
                                                }
                                            }}
                                        />
                                        <TBody
                                            abandonedCarts={filteredCarts}
                                            columnList={columnList}
                                            deleteAbandonedCart={deleteAbandonedCart}
                                            selectedCarts={selectedCarts}
                                            toggleCart={(hash) => {
                                                setSelectedCarts((prev) =>
                                                    prev.includes(hash)
                                                        ? prev.filter((item) => item !== hash)
                                                        : [...prev, hash]
                                                );
                                            }}
                                            onRemindClick={(cart) => setReminderCart(cart)}
                                        />
                                    </table>
                                </div>

                                {totalPage > 1 && (
                                    <Pagination baseSlug="abandoned-cart" current={page} total={totalPage} />
                                )}
                            </>
                        ) : (
                            <NotFound
                                {...(activeTab !== 'all' && {
                                    title: `No ${tabOptions.find(t => t.key === activeTab)?.label || ''} Carts Found`,
                                })}
                            />
                        )}
                    </>
                )}
            </div>

            {/* Clean Abandoned Carts Modal */}
            <CleanAbandonedCartModal
                isVisible={isModalVisible}
                closePopup={closeModal}
                cleanInvalidAbandonedCarts={cleanInvalidAbandonedCarts}
                cleanAbandonedCarts={cleanAllAbandonedCarts}
                abandonedBg={abandonedBg}
            />

            {/* Reminder Email Popup */}
            {reminderCart && (
                <AbandonedCartModal
                    item={reminderCart}
                    onClose={() => setReminderCart(null)}
                    onSent={() => setRefreshList((prev) => !prev)}
                />
            )}
        </div>
    );
};

export default AbandonedCartsList;
