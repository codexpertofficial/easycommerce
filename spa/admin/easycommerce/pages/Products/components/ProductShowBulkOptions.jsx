import React, { useEffect, useState } from "react";
import { __ } from "@wordpress/i18n";
import globalToast from "../../../../common/components/globalToast";
const BulkDelete = `${EASYCOMMERCE.assets}admin/img/icons/BulkDelete.png`;
const ActionArrowActive = `${EASYCOMMERCE.assets}admin/img/icons/ActionArrowActive.png`;
const ActionArrowInactive = `${EASYCOMMERCE.assets}admin/img/icons/ActionArrowInactive.png`;

const productStatuses = {
    publish: __("Live", "easycommerce"),
    draft: __("Draft", "easycommerce"),
    trash: __("Trash", "easycommerce"),
};

const ProductShowBulkOptions = ({
    selectedProducts,
    setProductIdToDelete,
    setForceDelete,
    setProductAllShowModal,
    setIsBulkDelete,
    setBulkProductStatus,
    setProductBulkCountChange,
    setProducts,
    products,
    productSetStatusCounts
}) => {
    const [isOpenBulk, setIsOpenBulk] = useState(false);
    const [openAccordion, setOpenAccordion] = useState(null);
    const { addToastData } = globalToast();

    useEffect(() => {
        setIsOpenBulk(selectedProducts.length > 0);
    }, [selectedProducts]);

    const handleOptionClick = (option) => {
        if (option.value === "delete") {
            setProductIdToDelete(selectedProducts);
            setForceDelete(false);
            setIsBulkDelete(true);
            setProductAllShowModal(true);
            setIsOpenBulk(false);
            return;
        }
        if (option.value === "setProductStatus") {
            setOpenAccordion((prev) => (prev === "productStatus" ? null : "productStatus"));
        }
    };

    const handleStatusSelect = (statusKey) => {
        if (!selectedProducts.length) return;

        const productIdsString = selectedProducts.join(",");
        const url = `${EASYCOMMERCE.rest_base}/products/update-statuses/?product_ids=${productIdsString}&status=${statusKey}`;
        easycommerce_modal(true);

        fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);
                if (data.success) {
                    addToastData({
                        type: "success",
                        message: data.data.message || __("Product status updated successfully.", "easycommerce"),
                    });
                    setProducts((prevProducts) => {
                        const updatedProducts = prevProducts.map((product) => {
                            if (selectedProducts.includes(product.id)) {
                                return {
                                    ...product,
                                    status: statusKey,
                                };
                            }
                            return product;
                        });
                        productSetStatusCounts((prevCounts) => {
                            const newCounts = { ...prevCounts };

                            selectedProducts.forEach((id) => {
                                const oldProduct = prevProducts.find((p) => p.id === id);
                                const oldStatus = oldProduct?.status;
                                if (!oldStatus || oldStatus === statusKey) return;
                                if (newCounts[oldStatus] > 0) {
                                    newCounts[oldStatus] -= 1;
                                }
                                newCounts[statusKey] = (newCounts[statusKey] || 0) + 1;
                            });

                            return newCounts;
                        });

                        return updatedProducts;
                    });


                } else {
                    addToastData({
                        type: "error",
                        message: data.data || __("Failed to update product status", "easycommerce"),
                    });
                }
            })
            .catch(() => {
                easycommerce_modal(false);
                addToastData({
                    type: "error",
                    message: __("An error occurred while updating product status", "easycommerce"),
                });
            });

        setIsOpenBulk(false);
        setOpenAccordion(null);
    };

    return (
        <div className="relative z-[99]">
            <ul className="absolute right-[-60px] top-8 p-3 bg-white rounded-lg shadow-2xl min-w-[212px]">
                <li className="group">
                    <div
                        className={`flex items-center gap-2 px-3 py-2 text-sm font-normal leading-[26px] cursor-pointer rounded-[4px] hover:bg-ec-modal ${
                            openAccordion === "productStatus" ? "bg-[#7351FD08] text-[#7351FD]" : "text-ec-body"
                        }`}
                        onMouseDown={() => handleOptionClick({ value: "setProductStatus" })}
                    >
                        {__("Set Product Status", "easycommerce")}
                        <span className="ml-auto">
                            <img
                                src={openAccordion === "productStatus" ? ActionArrowActive : ActionArrowInactive}
                                alt={__("Action Arrow", "easycommerce")}
                                className="w-3"
                            />
                        </span>
                    </div>
                    {openAccordion === "productStatus" && (
                        <ul className="mt-1 text-start border-l border-[#ECE6FF] ml-[10px]">
                            {Object.entries(productStatuses).map(([key, label]) => (
                                <li
                                    key={key}
                                    className="px-3 py-2 text-sm text-ec-body font-normal hover:bg-ec-modal cursor-pointer rounded"
                                    onMouseDown={() => { 
                                        handleStatusSelect(key);
                                        setBulkProductStatus(key);
                                        // setProductBulkCountChange({
                                        //     [key]: selectedProducts.length
                                        // });
                                    }}
                                >
                                    {label}
                                </li>
                            ))}
                        </ul>
                    )}
                </li>

                <li
                    className="group flex items-center justify-between gap-2 px-3 py-2 text-sm text-ec-body font-normal leading-[26px] hover:bg-ec-modal cursor-pointer rounded-[4px]"
                    onMouseDown={() => handleOptionClick({ value: "delete" })}
                >
                    {__("Delete", "easycommerce")}
                    <img src={BulkDelete} alt={__("Delete", "easycommerce")} className="w-5 h-4 object-contain" />
                </li>
            </ul>
        </div>
    );
};

export default ProductShowBulkOptions;