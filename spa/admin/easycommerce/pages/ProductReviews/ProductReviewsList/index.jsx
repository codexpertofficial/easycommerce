import React from "react";
import "./style.css";
import TBody from "../components/Table/TBody";
import THead from "../components/Table/THead";

const ProductReviewsList = ({ productReviews, tableColumns, deleteReview }) => {
    const [selectedReviews, setSelectedReviews] = React.useState([]);


    // Handle individual selection
    const handleSelectReview = (reviewId, checked) => {
        if (checked) {
            setSelectedReviews([...selectedReviews, reviewId]);
        } else {
            setSelectedReviews(selectedReviews.filter(id => id !== reviewId));
        }
    };

    return (
        <div className="w-full">
            <div className="bg-white rounded-xl">
                <div className="w-full">
                    <table className="w-full border-collapse border-spacing-0"> 
                        <THead
                            tableColumns={tableColumns}
                        />
                        <TBody
                            reviews={productReviews}
                            tableColumns={tableColumns}
                            selectedReviews={selectedReviews}
                            toggleReview={handleSelectReview}
                            deleteReview={deleteReview}
                        />
                    </table>
                </div>
            </div>
        </div>
    );
};

export default ProductReviewsList;