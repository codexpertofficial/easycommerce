import React, { useEffect, useState } from "react";
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import TaxSkeleton from "./TaxSkeleton";
import NoFound from "./NoFound";
import DeletePopup from '../../../common/components/DeletePopup';
import { Bounce, toast } from "react-toastify";

const deleteIcon = `${EASYCOMMERCE.assets}admin/img/icons/delete.png`;
const deletehoverIcon = `${EASYCOMMERCE.assets}admin/img/icons/delete-hover.png`;
const editIcon = `${EASYCOMMERCE.assets}admin/img/icons/status-edit-icon.png`;

const TaxList = ({ handleEdit, handleAddNew }) => {
    const [taxClasses, setTaxClasses] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [taxIdToDelete, setTaxIdToDelete] = useState(null);
    const [showDeletePopup, setShowDeletePopup] = useState(false);
    const [selectedPlanName, setSelectedPlanName] = useState('');

    const showToast = (type, message) => {
        toast[type](message, {
            position: "top-right",
            style: {
                margin: "30px 0 0 0",
                fontSize: "16px",
                fontWeight: "500",
                lineHeight: "26px",
                color: "#fff",
            },
            autoClose: 1500,
            hideProgressBar: false,
            closeOnClick: true,
            pauseOnHover: true,
            draggable: false,
            progress: undefined,
            theme: "colored",
            transition: Bounce,
        });
    };

    useEffect(() => {
        setIsLoading(true);

        fetch(`${EASYCOMMERCE.rest_base}/taxes`,{
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            }
        })
            .then((res) => res.json())
            .then((data) => {
                setIsLoading(false);
                setTaxClasses(data.data.classes);
            });
    }, []);

    /**
     * Filters the tax classes list.
     *
     * @since 1.0.0
     * @param {Array} taxClasses The tax classes array.
     */
    const filteredTaxClasses = applyFilters('easycommerce.tax.list', taxClasses);

    const handleDelete = () => {
        if (!taxIdToDelete) return;

        setShowDeletePopup(false);
        easycommerce_modal(true);

        fetch(`${EASYCOMMERCE.rest_base}/taxes/${taxIdToDelete}`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            }
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success) {
                    setTaxClasses((prevData) =>
                        prevData.filter((item) => item.id !== taxIdToDelete)
                    );

                    showToast("success", data.data);
                    setTaxIdToDelete(null);
                } else {
                    showToast("error", data.data);
                    setTaxIdToDelete(null);
                }
            });
    };

    return (
        <>
            {!isLoading ? (
                <div className="bg-white">
                    
                             {filteredTaxClasses.length > 0 ? (
                        <>
                             <div className="flex justify-start items-center gap-8 mb-6"> 
                                <p className="text-ec-body font-medium font-inter lg:text-xl md:text-lg leading-8">
                                    { __( "Tax Classes", "easycommerce" ) }
                                </p>
                                <button
                                    type="button"
                                    onClick={handleAddNew}
                                    className="w-[111px] h-[43px] flex justify-center items-center gap-[8px] 
                                    bg-white group border border-ec-primary rounded-lg 
                                    text-ec-primary hover:text-white hover:bg-ec-primary focus:shadow-none 
                                    focus:text-white focus:bg-ec-secondary lg:text-sm md:text-xs sm:text-sm 
                                    transition-all ease-in-out duration-500 font-inter font-medium text-base text-center leading-[26px]"
                                >
                                    { __( "Add Class", "easycommerce" ) }
                                </button>
                            </div>
                            <table className="w-full border-collapse overflow-hidden mt-10">
                                <thead>
                                    <tr className="bg-ec-table-bg">
                                        <th className="w-[20%] font-inter font-normal text-base text-ec-body text-left py-5 pl-5 first:rounded-l-lg">
                                            { __( "Class", "easycommerce" ) }
                                        </th>
                                        <th className="w-[15%] font-inter font-normal text-base text-ec-body text-left py-5 pl-5">
                                            { __( "Status", "easycommerce" ) }
                                        </th>
                                        <th className="w-[45%] font-inter font-normal text-base text-ec-body text-left py-5 pl-5">
                                            { __( "Regions", "easycommerce" ) }
                                        </th>
                                        <th className="w-[12%] font-inter font-medium text-base text-left py-6 rounded-e-lg border-r-0">
                                            { __( "Actions", "easycommerce" ) }
                                        </th>
                                    </tr>
                                </thead>
                                 <tbody>
                                     {filteredTaxClasses.map((taxClasses, index) => (
                                        <tr
                                            keys={index}
                                            className="h-[80px] border-b border-ec-table-stock transition-shadow hover:shadow-[0px_4px_40px_0px_#00000014]"
                                        >
                                            <td className="text-sm text-ec-body font-inter font-normal pl-5">
                                                {taxClasses.name}
                                            </td>
                                            <td className="text-sm text-ec-body font-inter font-normal pl-5">
                                                {taxClasses.status ? (
                                                    <span className="flex justify-start items-center gap-3">
                                                        <span
                                                            className={`w-3 h-3 rounded-full bg-[#00E91B]`}
                                                        ></span>

                                                        <span>{ __( "Active", "easycommerce" ) }</span>
                                                    </span>
                                                ) : (
                                                    <span className="flex justify-start items-center gap-3">
                                                        <span
                                                            className={`w-3 h-3 rounded-full bg-[#B8D1BB]`}
                                                        ></span>

                                                        <span>{ __( "Inactive", "easycommerce" ) }</span>
                                                    </span>
                                                )}
                                            </td>
                                            <td className="text-sm text-ec-body font-inter font-normal px-5">
                                                {taxClasses.regions}
                                            </td>
                                            <td className="flex w-[94px] py-6">
                                                <button
                                                    onClick={() => {
                                                        handleEdit(taxClasses.id);
                                                    }}
                                                    type="button"
                                                    className="w-10 h-8 bg-[#F8F8F8] mr-4 rounded-[4px] flex justify-center"
                                                >
                                                    <img
                                                        className="h-8 w-8"
                                                        src={editIcon}
                                                        alt=""
                                                    />
                                                </button>
                                                <button
                                                    onClick={() => {
                                                        setShowDeletePopup(true);
                                                        setTaxIdToDelete(taxClasses.id);
                                                        setSelectedPlanName(taxClasses.name); 
                                                    }}
                                                    className="w-10 h-8 bg-[#F8F8F8] mr-4 rounded-[4px] 
                                                flex items-center justify-center group hover:bg-[#FF3A521A]"
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
                        </>
                        
                    ) : (
                        <>
                            <NoFound handleAddNew={() => handleAddNew(true)} />
                        </>
                    )}
                </div>
            ) : (
                <TaxSkeleton />
            )}
           
            {showDeletePopup && (
                <DeletePopup
                    itemName={selectedPlanName}
                    onClose={() => {
                        setShowDeletePopup(false);
                        setTaxIdToDelete(null);
                    }}
                    onConfirm={() => {
                        handleDelete();
                        setShowDeletePopup(false);
                    }}
                />
            )}
        </>
    );
};

export default TaxList;
