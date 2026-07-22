import React, { useEffect, useState } from "react";
import { createRoot } from "react-dom/client";
import { Bounce, toast, ToastContainer } from "react-toastify";
import { __ } from "@wordpress/i18n";
import "react-toastify/dist/ReactToastify.css";

import EmptyShipping from "./components/EmptyShipping";
import AddShipping from "./components/AddShipping";
import ShippingPlans from "./components/ShippingPlans";

import ShippingSkeleton from "./components/ShippingSkeleton";

import "./assets/css/shipping.css";
import DeletePopup from "../../common/components/DeletePopup";

const App = () => {
    const [addNewShipping, setAddNewShipping] = useState(false);
    const [shippingPlans, setShippingPlans] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [showDeletePopup, setShowDeletePopup] = useState(false);
    const [shippingPlanIdToDelete, setShippingPlanIdToDelete] = useState(null);
    const [shippingPlanIdToEdit, setShippingPlanIdToEdit] = useState(null);
    const [selectedPlanName, setSelectedPlanName] = useState(null);


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

    const handleDeleteShipping = () => {
        if (!shippingPlanIdToDelete) return;

        setShowDeletePopup(false);
        easycommerce_modal(true);

        fetch(
            `${EASYCOMMERCE.rest_base}/shipping-plans/${shippingPlanIdToDelete}`,
            {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "X-WP-Nonce": EASYCOMMERCE.nonce,
                },
            }
        )
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success) {
                    setShippingPlans(
                        shippingPlans.filter(
                            (plan) => plan.id !== shippingPlanIdToDelete
                        )
                    );

                    showToast("success", data.data);
                    setShippingPlanIdToDelete(null);
                } else {
                    showToast("error", data.data);
                    setShippingPlanIdToDelete(null);
                }
            });
    };

    useEffect(() => {
        if (addNewShipping) return;

        setIsLoading(true);
        fetch(`${EASYCOMMERCE.rest_base}/shipping-plans`,{
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            }
        })
            .then((res) => res.json())
            .then((data) => {
                setIsLoading(false);
                if (data.success && data.data?.plans) {
                    setShippingPlans(data.data.plans);
                }
            });
    }, [addNewShipping]);

    return (
        <>
            <div className="flex justify-start items-center gap-8">
                <p className="text-ec-body font-medium font-inter lg:text-xl md:text-lg leading-8">
                    {addNewShipping ? __( "Add Shipping Plan", "easycommerce" ) : ""}
                </p>
            </div>

            {!addNewShipping && shippingPlans.length > 0 && (
            <div className="flex justify-start items-center gap-8 h-10 mb-6">
                <p className="text-ec-body font-medium font-inter lg:text-xl md:text-lg leading-8">
                    { __( "Shipping Plans", "easycommerce" ) }
                </p>
                <button
                    onClick={() => setAddNewShipping(true)}
                    className="w-[132px] h-[43px] flex justify-center items-center gap-[8px] font-inter 
                    bg-white group border border-ec-primary rounded-lg text-ec-primary 
                    hover:text-white hover:bg-ec-primary focus:shadow-none focus:text-white 
                    focus:bg-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all 
                    ease-in-out duration-500"
                >
                    { __( "+ New Plan", "easycommerce" ) }
                </button>
            </div>
        )}

            {!isLoading ? (
                <div className="easycommerce-shipping">
                    {shippingPlans.length === 0 ? (
                        <>
                            {addNewShipping ? (
                                <AddShipping
                                    handleCancel={() => {
                                        setAddNewShipping(false);
                                        setShippingPlanIdToEdit(null);
                                    }}
                                    showToast={showToast}
                                    ShippingPlanId={shippingPlanIdToEdit}
                                />
                            ) : (
                                <EmptyShipping
                                    handleAddNew={() => setAddNewShipping(true)}
                                />
                            )}
                        </>
                    ) : (
                        <>
                            {addNewShipping ? (
                                <AddShipping
                                    handleCancel={() => {
                                        setAddNewShipping(false);
                                        setShippingPlanIdToEdit(null);
                                    }}
                                    showToast={showToast}
                                    ShippingPlanId={shippingPlanIdToEdit}
                                />
                            ) : (
                                <ShippingPlans
                                    shippingPlans={shippingPlans}
                                    handleShippingEdit={(id) => {
                                        setAddNewShipping(true);
                                        setShippingPlanIdToEdit(id);
                                    }}
                                    handleDeleteShipping={(id , name ) => {
                                        setShowDeletePopup(true);
                                        setShippingPlanIdToDelete(id);
                                        setSelectedPlanName(name);
                                    }}
                                />
                            )}
                        </>
                    )}
                </div>
            ) : (
                <ShippingSkeleton />
            )}

            {showDeletePopup && (
                <DeletePopup
                    itemName={selectedPlanName}
                    onClose={() => {
                        setShowDeletePopup(false);
                        setShippingPlanIdToDelete(null);
                        setSelectedPlanName(null);
                    }}
                    onConfirm={() => {
                        handleDeleteShipping();
                        setShowDeletePopup(false);
                    }}
                />
            )}
            <ToastContainer />
        </>
    );
};

const container = document.getElementById("easycommerce-shipping-methods");
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}

export default App;
