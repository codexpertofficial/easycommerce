import React from "react";
import { useDispatch } from "react-redux";
import { addToastData } from "../../../redux-store/slices/toastSlice";

const deleteWarningIcon = `${EASYCOMMERCE.assets}admin/img/icons/delete-warning.png`;

const DeleteAllProductPopup = ({
    discardAction,
    productId,
    bulkProductIds = [],
    isBulk = false,
    hideModal,
    forceDelete = false,
    setProducts,
    products,
    setSelectedProducts,
}) => {
    const dispatch = useDispatch();

    const productIds = isBulk
        ? bulkProductIds.map((id) => parseInt(id, 10))
        : [parseInt(productId, 10)];

    const handleBulkDelete = (isPermanent = false) => {
        easycommerce_modal(true);
        hideModal();

        const url = `${EASYCOMMERCE.rest_base}/products/bulk-delete${
            isPermanent ? "?force=true" : ""
        }`;

        fetch(url, {
            method: "DELETE",
            body: JSON.stringify({
                product_ids: productIds,
                force: isPermanent,
            }),
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success) {
                    dispatch(
                        addToastData({
                            type: "success",
                            message: `Product${isBulk ? "s" : ""} ${
                                isPermanent ? "deleted permanently" : "trashed"
                            }`,
                        })
                    );

                    if (isPermanent) {
                        setProducts((prev) =>
                            prev.filter((product) => !productIds.includes(product.id))
                        );
                    } else {
                        setProducts((prev) =>
                            prev.map((product) =>
                                productIds.includes(product.id)
                                    ? { ...product, status: "trash" }
                                    : product
                            )
                        );
                    }

                    setSelectedProducts([]);
                }
            });
    };

    return (
        <div className="fixed top-0 left-0 w-screen h-screen flex items-center justify-center bg-[#00000082] backdrop-blur-sm z-[999999]">
            <div className="flex flex-col justify-center items-center bg-white rounded-xl px-7 pt-[50px] py-9">
                <div className="flex flex-col justify-center items-center gap-[26px] mb-[50px]">
                    <div className="w-[86px] h-[86px] flex justify-center items-center rounded-full bg-[#FF3A521A]">
                        <img src={deleteWarningIcon} alt="delete-warning" className="w-10 h-8" />
                    </div>

                    <div className="flex flex-col justify-center items-center gap-1">
                        <h3 className="font-inter font-medium text-2xl text-ec-body">
                            {isBulk ? "Delete selected products" : "Delete this product"}
                        </h3>
                        <p className="w-9/12 mx-auto text-center font-inter font-normal text-base text-ec-placeholder">
                            You're going to delete
                            {isBulk
                                ? ` ${bulkProductIds.length} selected products`
                                : ` the “Product”`}{" "}
                            {forceDelete && " permanently"} — are you sure?
                        </p>
                    </div>
                </div>

                <div className="flex justify-between items-center gap-[14px] mb-5">
                    <button
                        className="font-inter font-medium text-base border bg-ec-body text-white border-ec-body rounded-lg px-[50px] py-[10px]"
                        onClick={discardAction}
                    >
                        No, Keep it
                    </button>
                    <button
                        className="font-inter font-medium text-base text-ec-body border border-ec-body rounded-lg px-[50px] py-[10px] hover:bg-[#FF3A52] hover:border-[#FF3A52] hover:text-white"
                        onClick={() => handleBulkDelete(forceDelete)}
                    >
                        Yes, Delete!
                    </button>
                </div>

                {!forceDelete && (
                    <div className="flex items-center justify-center w-full">
                        <p
                            className="cursor-pointer font-inter font-normal text-base text-ec-placeholder underline hover:text-[#FF3A52]"
                            onClick={() => handleBulkDelete(true)}
                        >
                            Delete Permanently
                        </p>
                    </div>
                )}
            </div>
        </div>
    );
};
export default DeleteAllProductPopup;