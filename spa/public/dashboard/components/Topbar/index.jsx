import React from "react";
import { __ } from "@wordpress/i18n";

const whiteSearchIcon = `${EASYCOMMERCE.assets}public/img/icons/white-search-icon.png`;

const Topbar = ({ sectionTitle, placeholderText, handleSubmit }) => {
    return (
        <div className="flex justify-between items-center">
            <h3
                className="easycommerce-dashboard-section-title"
                style={{ color: "var(--color-ec-body)" }}
            >
                {sectionTitle}
            </h3>

            <form
                onSubmit={handleSubmit}
                className="flex items-center justify-end gap-[10px]"
            >
                <input
                    type="text"
                    name="search"
                    className="easycommerce-dashboard-search-input"
                    placeholder={placeholderText}
                />

                <button
                    type="submit"
                    className="easycommerce-dashboard-search-btn h-12 px-[17px] rounded-md"
                >
                    <img
                        src={whiteSearchIcon}
                        alt={__( "search-icon", "easycommerce" )}
                        className="w-4 h-4 pointer-events-none"
                    />
                </button>
            </form>
        </div>
    );
};

export default Topbar;
