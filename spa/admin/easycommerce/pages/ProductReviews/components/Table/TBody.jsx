import React, { useState } from "react";
import { __, sprintf } from "@wordpress/i18n";
import { toast } from "react-toastify";

const TBody = ({ reviews, tableColumns, selectedReviews, toggleReview, deleteReview }) => {

    const [statusChanging, setStatusChanging] = useState({});

    const statusOptions = [
        { value: '1', label: __('Approved', 'easycommerce') },
        { value: '0', label: __('Not Approved', 'easycommerce') },
    ];

    const getStatusLabel = (status) => {
        switch (status) {
            case '1': return __('Approved', 'easycommerce');
            case '0': return __('Not Approved', 'easycommerce');
            default: return __('Unknown', 'easycommerce');
        }
    };

    const getStatusClass = (status) => {
        switch (status) {
            case '1': return 'text-ec-completedText bg-ec-completedBg';
            case '0': return 'text-ec-cancelledText bg-ec-cancelledBg';
            default: return 'text-gray-600 bg-gray-100';
        }
    };

    const truncateText = (text, maxLength = 50) => {
        if (!text) return __('N/A', 'easycommerce');
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    };

    return (
        <tbody>
            {reviews.map((review, index) => (
                <tr
                    key={review.id}
                    className="border-b border-ec-table-stock h-[80px] transition-shadow hover:shadow-[0px_4px_40px_0px_#00000014]"
                >
                    {tableColumns.map((column) => {
                        if (column === "customer") {
                            return (
                                <td 
                                    key={column} 
                                    className="relative pl-5 text-sm text-ec-body font-inter font-normal leading-[26px] lg:w-[15%] group/transaction rtl:pr-5"
                                >
                                    <div className="relative w-full h-ec-input">
                                        <div className="absolute left-0 rtl:right-0 top-1/2 -translate-y-1/2 group-hover/transaction:top-4 duration-300">
                                            <div className="font-medium text-ec-body">
                                                {review.customer_name || review.name || __('N/A', 'easycommerce')}
                                            </div>
                                        </div>
                                        <div className="invisible group-hover/transaction:visible opacity-0 
                                            group-hover/transaction:opacity-100 duration-300 absolute bottom-0 left-0 rtl:right-0"
                                        >
                                            <div className="flex items-center gap-1.5 font-inter font-normal text-xs text-ec-light-black">
                                                <button 
                                                    className="text-ec-red"
                                                    onClick={() => deleteReview(review.id, review.customer_name || review.name || sprintf(__('Review #%d', 'easycommerce'), review.id))}
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            );
                        }

                        if (column === "product") {
                            return (
                                <td
                                    key={column}
                                    className="relative text-sm text-ec-body font-inter font-normal leading-[26px] lg:w-[15%]"
                                >
                                    <div className="absolute pl-5 left-0 rtl:right-0 top-1/2 -translate-y-1/2 group-hover/transaction:top-4 duration-300 rtl:pr-5">
                                        <a
                                            href={`#/products/edit/${review.product_id}`}
                                            className="text-ec-primary hover:text-ec-secondary transition-colors"
                                            title={review.product_name || __('View Product', 'easycommerce')}
                                        >
                                            {truncateText(review.product_name, 30) || sprintf(__('Product #%d', 'easycommerce'), review.product_id)}
                                        </a>
                                    </div>
                                </td>
                            );
                        }

                        if (column === "content") {
                            return (
                                <td 
                                    key={column} 
                                    className="relative text-sm text-ec-body font-inter font-normal leading-[26px] lg:w-[25%]"
                                >
                                    <div 
                                        className="absolute pl-5 left-0 rtl:right-0 top-1/2 -translate-y-1/2 group-hover/transaction:top-4 duration-300 max-w-[300px] rtl:pr-5"
                                        title={review.content}
                                    >
                                        <p className="text-sm text-gray-600">
                                            {truncateText(review.content, 60)}
                                        </p>
                                    </div>
                                </td>
                            );
                        }

                        if (column === "rating") {
                            return (
                                <td 
                                    key={column} 
                                    className="relative text-sm text-ec-body font-inter font-normal leading-[26px] pl-5 lg:w-[12%] rtl:pr-5 rtl:pl-0"
                                >
                                    <div className="absolute left-0 rtl:right-0 top-1/2 -translate-y-1/2 group-hover/transaction:top-4 duration-300">
                                        <div className="flex items-center">
                                            {[...Array(5)].map((_, i) => (
                                                <svg
                                                    key={i}
                                                    className={`w-4 h-4 ${i < review.rating ? 'text-yellow-400' : 'text-gray-300'}`}
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            ))}
                                            <span className="ml-1 text-sm">({review.rating})</span>
                                        </div>
                                    </div>
                                </td>
                            );
                        }

                        if (column === "date") {
                            return (
                                <td 
                                    key={column} 
                                    className="relative text-sm text-ec-body font-inter font-normal leading-[26px] pl-5 lg:w-[10%] rtl:pr-5 rtl:pl-0"
                                >
                                    <span className="text-ec-body absolute left-0 rtl:right-0 top-1/2 -translate-y-1/2 group-hover/transaction:top-4 duration-300">
                                        {new Date(review.created_at).toLocaleDateString('en-US', {
                                            year: 'numeric',
                                            month: 'short',
                                            day: 'numeric'
                                        })}
                                    </span>
                                </td>
                            );
                        }

                        if (column === "status") {
                            return (
                                <td 
                                    key={column} 
                                    className="relative text-sm text-ec-body font-inter font-normal leading-[26px] pl-5 lg:w-[10%] rtl:pr-5 rtl:pl-0"
                                >
                                    <div className="absolute left-0 rtl:right-0 top-1/2 -translate-y-1/2 group-hover/transaction:top-4 duration-300 flex items-center gap-3">
                                        <span className={`px-3 py-1 rounded-full text-xs font-medium ${getStatusClass(review.status)}`}>
                                            {getStatusLabel(review.status)}
                                        </span>
                                    </div>
                                </td>
                            );
                        }

                        return null;
                    })}
                </tr>
            ))}
        </tbody>
    );
};

export default TBody;