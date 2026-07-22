import React from "react";
import { __ } from "@wordpress/i18n";

// Components
import RegionsHeader from "./RegionsHeader";
import RegionActionButtons from "./RegionActionButtons";
import Dropdown from "../../../../../common/components/inputs/Dropdown";

const taxCountries = Object.keys(EASYCOMMERCE.tax.countries).map((key) => {
    return { value: key, label: EASYCOMMERCE.tax.countries[key] };
});

const Regions = ({
    rates,
    states,
    cities,
    handleAddRegion,
    handleRemoveRegion,
    handleRegionChange,
}) => {
    return (
        <div className="w-full">
            <div className="w-full border border-ec-border px-4 py-4 rounded-lg overflow-x-auto">
                <table className="w-full">
                    <RegionsHeader />

                    <tbody>
                        {rates.map((region, index) => (
                            <tr key={index}>
                                {/* Country */}
                                <td className="pb-[14px] px-[7px]">
                                    <div className="h-ec-input">
                                        <Dropdown
                                            options={[
                                                { label: __( "- Select -", "easycommerce" ), value: "" },
                                                ...taxCountries.map((country) => ({
                                                    label: country.label,
                                                    value: country.value,
                                                })),
                                            ]}
                                            placeholder={__( "- Select -", "easycommerce" )}
                                            value={region.country || ""}
                                            onChange={(option) =>
                                                handleRegionChange(index, "country", option.value)
                                            }
                                        />
                                    </div>
                                </td>

                                {/* State */}
                                <td className="pb-[14px] px-[7px]">
                                    <div className="h-ec-input">
                                        <Dropdown
                                            options={[
                                                { label: __( "- Select -", "easycommerce" ), value: "" },
                                                ...(Array.isArray(states[index])
                                                    ? states[index].map((state) => ({
                                                          label: state,
                                                          value: state,
                                                      }))
                                                    : []),
                                            ]}
                                            placeholder={__( "- Select -", "easycommerce" )}
                                            value={region.state || ""}
                                            onChange={(option) =>
                                                handleRegionChange(index, "state", option.value)
                                            }
                                        />
                                    </div>
                                </td>

                                {/* City dropdown */}
                                <td className="pb-[14px] px-[7px]">
                                    <div className="h-ec-input">
                                        <Dropdown
                                        options={[
                                            { label: __( "- Select -", "easycommerce" ), value: "" },
                                            ...(Array.isArray(cities[index])
                                                ? cities[index].map((city) => ({ label: city, value: city }))
                                                : []),
                                        ]}
                                        placeholder={__( "- Select -", "easycommerce" )}
                                        value={region.city || ""}
                                        onChange={(option) => handleRegionChange(index, "city", option.value)}
                                        />
                                    </div>
                                </td>


                                {/* Rate */}
                                <td className="pb-[14px] px-[7px]">
                                    <input
                                        type="number"
                                        min={0}
                                        max={100}
                                        step={1}
                                        className="h-ec-input p-3 rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none 
                                            focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out w-full disabled:cursor-not-allowed disabled:bg-ec-table-stock disabled:hover:border-ec-table-stock disabled:focus:border-ec-table-stock"
                                        value={region.rate}
                                        onChange={(e) =>
                                            handleRegionChange(
                                                index,
                                                "rate",
                                                e.target.value
                                            )
                                        }
                                    />
                                </td>

                                {/* Add/Remove Buttons */}
                                <RegionActionButtons
                                    add={() => handleAddRegion(index)}
                                    remove={() => handleRemoveRegion(index)}
                                />
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default Regions;
