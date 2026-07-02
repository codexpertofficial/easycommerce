import React, { useState } from 'react';
import { Slot } from '@wordpress/components';
import Pagination from "../../../common/components/Pagination";
import DeletePopup from '../../../common/components/DeletePopup';

// Icons
const deleteIcon = `${EASYCOMMERCE.assets}admin/img/icons/delete.png`;
const deletehoverIcon = `${EASYCOMMERCE.assets}admin/img/icons/delete-hover.png`;
const editIcon = `${EASYCOMMERCE.assets}admin/img/icons/status-edit-icon.png`;

const ShippingPlans = ({
    shippingPlans,
    handleShippingEdit,
    handleDeleteShipping,
}) => {
    //const [showDeletePopup, setShowDeletePopup] = useState(false);
    // const [selectedPlanId, setSelectedPlanId] = useState(null);
    // const [selectedPlanName, setSelectedPlanName] = useState('');

    return (
        <>
            <div className="bg-white">
                <table className="w-full border-collapse overflow-hidden mt-10">
                    <thead>
                        <tr className="bg-ec-table-bg">
                            <th className="w-[20%] font-inter font-normal text-base text-ec-body text-left py-3 pl-5 first:rounded-l-lg">
                                Plan
                            </th>
                            <th className="w-[10%] font-inter font-normal text-base text-ec-body text-left py-3 pl-5">
                                Status
                            </th>
                            <th className="w-[35%] font-inter font-normal text-base text-ec-body text-left py-3 pl-5 ">
                                Regions
                            </th>
                            <th className="w-[25%] font-inter font-normal text-base text-ec-body text-left py-3 pl-5">
                                Methods
                            </th>
                            <th className="w-[10%] font-inter font-medium text-base text-left py-3 rounded-e-lg border-r-0">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {shippingPlans.length > 0 &&
                            shippingPlans.map((shippingMethod, index) => (
                                <tr
                                    key={index}
                                    className="h-[80px] border-b border-ec-table-stock transition-shadow hover:shadow-[0px_4px_40px_0px_#00000014]"
                                >
                                    <td className="text-sm text-ec-body font-inter font-normal lg:w-[17%] pl-5">
                                        {shippingMethod.name}
                                    </td>
                                    <td className="text-sm text-ec-body font-inter font-normal lg:w-[17%] pl-5">
                                        <span className="flex justify-start items-center gap-3">
                                            <span
                                                className={`w-3 h-3 rounded-full ${
                                                    shippingMethod.active
                                                        ? "bg-[#00E91B]"
                                                        : "bg-[#cdcdcd]"
                                                }`}
                                            ></span>

                                            <span>
                                                {shippingMethod.active
                                                    ? "Enabled"
                                                    : "Disabled"}
                                            </span>
                                        </span>
                                    </td>
                                    <td className="text-sm text-ec-body font-inter font-normal lg:w-[17%] pl-5">
                                        {shippingMethod.regions.map(
                                            (region, index) =>
                                                shippingMethod.regions.length -
                                                    1 ===
                                                index
                                                    ? region.region_code
                                                    : region.region_code + ", "
                                        )}
                                    </td>
                                    <td className="text-sm text-ec-body font-inter font-normal lg:w-[17%] pl-5">
                                        {shippingMethod.methods.map((method, index) =>
                                            index === shippingMethod.methods.length - 1
                                                ? method.name
                                                : method.name + ", "
                                        )}
                                    </td>
                                    <td className="flex w-[94px] py-6">
                                        <button
                                            className="w-[32px] h-[32px] bg-[#F8F8F8] mr-4 rounded-full flex justify-center"
                                            onClick={() =>
                                                handleShippingEdit(
                                                    shippingMethod.id
                                                )
                                            }
                                        >
                                            <img
                                                className="h-8 w-8 rounded-full"
                                                src={editIcon}
                                                alt=""
                                            />
                                        </button>
                                        <button
                                            className="w-[32px] h-[32px] bg-[#F8F8F8] mr-4 rounded-full 
                                            flex items-center justify-center group hover:bg-[#FF3A521A]"
                                            onClick={() =>
                                               handleDeleteShipping(
                                                    shippingMethod.id,
                                                    shippingMethod.name,
                                                )
                                            }

                                        >
                                            <img
                                                className="w-[14px] h-[16px] block group-hover:hidden"
                                                src={deleteIcon}
                                                alt="delete-pricing"
                                            />
                                            <img
                                                className="w-[14px] h-[16px] hidden group-hover:block"
                                                src={deletehoverIcon}
                                                alt="delete-pricing"
                                            />
                                        </button>
                                    </td>
                                </tr>
                            ))}
                    </tbody>
                </table>

                {/* Slot for additional shipping plans */}
                <Slot name="easycommerce.shipping.plans.extra" />
            </div>
        </>
    );
};

export default ShippingPlans;
