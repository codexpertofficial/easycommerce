import React from "react";

const backIcon = `${EASYCOMMERCE.assets}admin/img/icons/Back.png`;

export default function Previous({ baseSlug = "products", current = 1 }) {
    return (
        <>
            {current > 1 ? (
                <a
                    href={`#/${baseSlug}/page/${parseInt(current) - 1}`}
                    className="flex text-ec-title font-inter text-sm leading-5 items-center gap-2 py-2 px-3 border border-ec-table-stock rounded-[4px] hover:border-ec-primary hover:text-ec-title active:text-ec-title active:shadow-none focus:text-ec-title focus:shadow-none"
                >
                    <div>
                        <img
                            src={backIcon}
                            width={8}
                            height={5}
                            alt="arrow"
                            className="pointer-events-none"
                        />
                    </div>
                    <span>Back</span>
                </a>
            ) : (
                <div className="flex text-ec-title font-inter text-sm leading-5 items-center gap-2 py-2 px-3 border border-ec-table-stock rounded-[4px] hover:text-ec-title cursor-not-allowed active:text-ec-title active:shadow-none focus:text-ec-title focus:shadow-none">
                    <div>
                        <img
                            src={backIcon}
                            width={8}
                            height={5}
                            alt="arrow"
                            className="pointer-events-none"
                        />
                    </div>
                    <span>Back</span>
                </div>
            )}
        </>
    );
}
