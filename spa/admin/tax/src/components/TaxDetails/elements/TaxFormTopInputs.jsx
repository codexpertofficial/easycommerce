import React from "react";
import { __ } from "@wordpress/i18n";

const TaxFormTopInputs = ({ name, description, status, onChange }) => {
    return (
        <>
            <p className="flex ec-db-lg:items-center items-start py-4 ec-db-lg:flex-row flex-col ec-db-lg:gap-0 gap-4">
                <label
                    className="text-ec-body font-inter font-normal text-base leading-4 w-[180px]"
                    htmlFor="easycommerce-tax-name"
                >
                    { __( "Name", "easycommerce" ) }
                </label>
                <div className="w-full">
                    <div className="flex">
                        <input
                            id="easycommerce-tax-name"
                            type="text"
                            className="h-ec-input p-3 rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out w-full disabled:cursor-not-allowed disabled:bg-ec-table-stock disabled:hover:border-ec-table-stock disabled:focus:border-ec-table-stock"
                            placeholder={__( "Tax class name", "easycommerce" )}
                            value={name}
                            onChange={(e) => onChange("name", e.target.value)}
                        />
                    </div>
                </div>
            </p>
            <p className="flex ec-db-lg:items-center items-start py-4 ec-db-lg:flex-row flex-col ec-db-lg:gap-0 gap-4">
                <label
                    className="text-ec-body font-inter font-normal text-base leading-4 w-[180px]"
                    htmlFor="easycommerce-tax-description"
                >
                    { __( "Description", "easycommerce" ) }
                </label>
                <div className="w-full">
                    <div className="flex">
                        <input
                            id="easycommerce-tax-description"
                            type="text"
                            className="h-ec-input p-3 rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out w-full disabled:cursor-not-allowed disabled:bg-ec-table-stock disabled:hover:border-ec-table-stock disabled:focus:border-ec-table-stock"
                            placeholder={__( "Write description here", "easycommerce" )}
                            value={description}
                            onChange={(e) =>
                                onChange("description", e.target.value)
                            }
                        />
                    </div>
                </div>
            </p>
            <p className="flex items-center py-4">
                <label
                    className="text-ec-body font-inter font-normal text-base leading-4 w-[180px]"
                    htmlFor="easycommerce-tax-status"
                >
                    { __( "Enable/Disable", "easycommerce" ) }
                </label>
                <div className="w-full">
                    <div className="flex">
                        <label className="easycommerce-switch">
                            <input
                                id="easycommerce-tax-status"
                                type="checkbox"
                                checked={status}
                                onChange={(e) =>
                                    onChange("status", e.target.checked)
                                }
                            />
                            <span className="easycommerce-slider easycommerce-round"></span>
                        </label>
                    </div>
                </div>
            </p>
        </>
    );
};

export default TaxFormTopInputs;
