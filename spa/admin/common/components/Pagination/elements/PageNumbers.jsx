import React from "react";

const PageNumbers = ({ total = 1, current = 1, baseSlug = "products" }) => {
    const pageNumbers = [];

    // Always add the first page
    pageNumbers.push(1);

    if (total <= 5) {
        // If total pages are 5 or less, show all pages
        for (let i = 2; i <= total; i++) {
            pageNumbers.push(i);
        }
    } else {
        // Determine the middle pages based on current page
        if (current <= 3) {
            // Current page is among the first three
            for (let i = 2; i <= 4; i++) {
                pageNumbers.push(i);
            }

            pageNumbers.push("...");
        } else if (current >= total - 2) {
            // Current page is among the last three
            pageNumbers.push("...");

            for (let i = total - 3; i < total; i++) {
                pageNumbers.push(i);
            }
        } else {
            // Current page is somewhere in the middle
            pageNumbers.push("...");
            pageNumbers.push(current - 1);
            pageNumbers.push(current);
            pageNumbers.push(current + 1);
            pageNumbers.push("...");
        }
    }

    // Always add the last page if total is more than 5
    if (total > 5) {
        pageNumbers.push(total);
    }

    return (
        <div className="flex justify-center items-center gap-[6px]">
            {pageNumbers.map((number, index) => {
                return number === "..." ? (
                    <div
                        key={index}
                        className={`flex text-ec-title font-inter text-sm leading-5 items-center gap-2 py-2 px-3 
                            border border-ec-table-stock rounded-[4px] hover:text-ec-title cursor-not-allowed 
                            active:text-ec-title active:shadow-none focus:text-ec-title focus:shadow-none`}
                    >
                        {number}
                    </div>
                ) : (
                    <a
                        key={index}
                        href={`#/${baseSlug}/page/${number}`}
                        className={`w-9 h-[35px] flex items-center justify-center font-inter text-sm leading-5 gap-2 rounded-[4px] 
                            active:text-ec-title active:shadow-none focus:text-white focus:shadow-none ${
                                number === Number(current)
                                    ? `easycommerece-pagination-active hover:text-white text-white bg-ec-primary 
                                    border border-ec-primary active:text-white active:bg-ec-primary active:outline-none 
                                    focus:outline-none focus:shadow-none`
                                    : "border border-ec-table-stock hover:border-ec-primary hover:text-ec-title text-ec-title"
                            }`}
                    >
                        {number}
                    </a>
                );
            })}
        </div>
    );
};

export default PageNumbers;
