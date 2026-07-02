import React, { useState, useEffect } from "react";
import { toast } from "react-toastify";
import ProductReviewsList from "./ProductReviewsList";
import Pagination from "../../../common/components/Pagination";
import NotFound from "../../../common/NotFound";
import TableSkeleton from "../../../common/TableSkeleton";
import TableFilter from "./components/TableFilter";
import DeletePopup from "../../../common/components/DeletePopup";

const noProductReviewsIcon = `${EASYCOMMERCE.assets}admin/img/nofound/no-reviews.png`;

const ProductReviews = ({ page }) => {
    const [isLoading, setIsLoading] = useState(true);
    const [productReviews, setProductReviews] = useState([]);
    const [tableColumns, setTableColumns] = useState([
        "customer",
        "product",
        "content",
        "rating",
        "date",
        "status",
    ]);
    const [formState, setFormState] = useState({
        search: "",
    });
    const [totalPage, setTotalPage] = useState(1);
    const [postPerPage, setPostPerPage] = useState(20);
    const [reviewsFiltered, setReviewsFiltered] = useState(false);
    const [searchTrigger, setSearchTrigger] = useState(0);
    const [filterLoader, setFilterLoader] = useState(false);
    
    // Delete functionality states
    const [isShowModal, setIsShowModal] = useState(false);
    const [reviewIdToDelete, setReviewIdToDelete] = useState(null);
    const [reviewNameToDelete, setReviewNameToDelete] = useState(null);

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormState((prevState) => ({
            ...prevState,
            [name]: value,
        }));
    };

    const handleSubmit = () => {
        setReviewsFiltered(true);
        setSearchTrigger(prev => prev + 1);
        window.location.hash = "#/reviews";
    };

    const resetFilter = () => {
        setFormState({
            search: "",
        });
        setReviewsFiltered(false);
        setSearchTrigger(prev => prev + 1);
        window.location.hash = "#/reviews";
    };

    const fetchProductReviews = () => {
        setIsLoading(true);
        reviewsFiltered && setFilterLoader(true);
        const searchValue = formState.search.trim();

        let url = `${EASYCOMMERCE.rest_base}/product-reviews?page=${page}&per_page=${postPerPage}`;
        
        if (reviewsFiltered && searchValue) {
            url += `&search=${searchValue}`;
        }

        fetch(url, {
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            }
        })
            .then((response) => response.json())
            .then((data) => {
                setIsLoading(false);
                setFilterLoader(false);
                if (data && Array.isArray(data.data.reviews)) {
                    setTotalPage(Math.ceil(data.data.total / postPerPage));
                    setProductReviews(data.data.reviews);
                } else {
                    setTotalPage(1);
                    setProductReviews([]);
                }
            })
            .catch((error) => {
                console.error('Error fetching reviews:', error);
                setIsLoading(false);
                setFilterLoader(false);
                setTotalPage(1);
                setProductReviews([]);
            });
    };

    useEffect(() => {
        fetchProductReviews();
    }, [page, postPerPage, searchTrigger]);

    // Delete review function
    const deleteReview = (reviewId) => {
        setIsShowModal(false);
        easycommerce_modal(true);
        fetch(`${EASYCOMMERCE.rest_base}/product-reviews/${reviewId}`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
        .then((res) => res.json())
        .then((data) => {
            easycommerce_modal(false);
            if (data.success) {
                toast.success(data.data?.message || "Review deleted successfully");
                setProductReviews((prevReviews) =>
                    prevReviews.filter((review) => review.id !== reviewId)
                );
                fetchProductReviews();
            } else {
                toast.error(data.data?.message || "Failed to delete review");
            }
        })
        .catch((error) => {
            easycommerce_modal(false);
            toast.error("An error occurred while deleting the review");
            console.error("Error deleting review:", error);
        });
    };

    return (
        <>
            <div className="product-panel-title mb-4">
                <h3>Product Reviews</h3>
            </div>
            <div className="2xl:max-w-full min-[1440px]:max-w-[1000px] min-[1300px]:max-w-[954px] xl:max-w-[830px] bg-white border border-solid border-ec-table-stock rounded-xl p-6 min-h-screen flex flex-col h-[94%]">
                    <div className="flex justify-between gap-5 mb-4">
                        <div className="flex gap-4 flex-wrap border-b-2 border-[#F0EDFB]">
                        </div>

                        <TableFilter
                            formState={formState}
                            handleSubmit={handleSubmit}
                            handleInputChange={handleInputChange}
                            reviewsFiltered={reviewsFiltered}
                            resetFilter={resetFilter}
                            setFormState={setFormState}
                        />
                    </div>
                {!isLoading ? (
                    <>
                        {productReviews && productReviews.length > 0 ? (
                            <>
                                <ProductReviewsList
                                    productReviews={productReviews}
                                    tableColumns={tableColumns}
                                    page={page}
                                    deleteReview={(reviewId, reviewName) => {
                                        setIsShowModal(true);
                                        setReviewIdToDelete(reviewId);
                                        setReviewNameToDelete(reviewName);
                                    }}
                                />
                                {totalPage > 1 && (
                                    <Pagination
                                        baseSlug="reviews"
                                        current={page}
                                        total={totalPage}
                                    />
                                )}
                            </>
                        ) : (
                            <NotFound
                                ImageUrl={noProductReviewsIcon}
                                title={
                                    reviewsFiltered 
                                        ? "No Reviews Yet for Your Search"
                                        : "No Reviews Yet"
                                }
                                description={
                                    reviewsFiltered
                                        ? "No reviews match your search criteria. Try a different search."
                                        : "All customer reviews and feedback will appear here once they’re submitted."
                                }
                            />
                        )}
                    </>
                ) : (
                    <TableSkeleton
                        numberOfRows={15}
                        SkeletonHeight={30}
                    />
                )}
                
                {/* Delete confirmation popup */}
                {isShowModal && (
                    <DeletePopup
                        onClose={() => {
                            setIsShowModal(false);
                            setReviewIdToDelete(null);
                            setReviewNameToDelete(null);
                        }}
                        onConfirm={() => deleteReview(reviewIdToDelete)}
                        itemName={reviewNameToDelete || ''}
                    />
                )}
            </div>
        </>
    );
};

export default ProductReviews;