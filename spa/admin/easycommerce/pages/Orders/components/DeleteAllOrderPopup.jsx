import React from "react";
import { __, sprintf } from "@wordpress/i18n";
import { useDispatch } from "react-redux";
import { addToastData } from "../../../redux-store/slices/toastSlice";

const deleteWarningIcon = `${EASYCOMMERCE.assets}admin/img/icons/delete-warning.png`;

const DeleteAllOrderPopup = ({
    discardAction,
    orderId,
    bulkOrderIds = [],
    isBulk = false,
    hideModal,
    forceDelete = false,
    afterInstatntDelete,
}) => {
    const dispatch = useDispatch();

    const deleteNowAll = () => {
        easycommerce_modal(true);
        hideModal();

        const url = `${EASYCOMMERCE.rest_base}/orders/bulk-delete${forceDelete ? "?force=true" : ""}`;

        const orderIds = isBulk
            ? bulkOrderIds.map(id => parseInt(id, 10))
            : [parseInt(orderId, 10)];

        fetch(url, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify({
                order_ids: orderIds,
                force: forceDelete,
            }),
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success) {
                    dispatch(
                        addToastData({
                            type: "success",
                            message: isBulk
                                ? __("Orders deleted permanently", "easycommerce")
                                : __("Order deleted permanently", "easycommerce"),
                        })
                    );
                    afterInstatntDelete();
                }
            });
    };

    return (
        <div className="fixed top-0 left-0 w-screen h-screen flex items-center justify-center bg-[#00000082] backdrop-blur-sm z-[999999]">
            <div className="flex flex-col justify-center items-center bg-white rounded-xl px-7 pt-[50px] py-9">
                <div className="flex flex-col justify-center items-center gap-[26px] mb-[50px]">
                    <div className="w-[86px] h-[86px] flex justify-center items-center rounded-full bg-[#FF3A521A]">
                        <img
                            src={deleteWarningIcon}
                            alt={__("delete-warning", "easycommerce")}
                            className="w-10 h-8"
                        />
                    </div>

                    <div className="flex flex-col justify-center items-center gap-1">
                        <h3 className="font-inter font-medium text-2xl text-ec-body">
                            {isBulk
                                ? __('Delete selected orders', 'easycommerce')
                                : __('Delete this order', 'easycommerce')}
                        </h3>
                        <p className="w-9/12 mx-auto text-center font-inter font-normal text-base text-ec-placeholder">
                            {isBulk
                                ? forceDelete
                                    ? sprintf(__("You're going to delete %d selected orders permanently — are you sure?", "easycommerce"), bulkOrderIds.length)
                                    : sprintf(__("You're going to delete %d selected orders — are you sure?", "easycommerce"), bulkOrderIds.length)
                                : forceDelete
                                    ? __("You're going to delete the “Order” permanently — are you sure?", "easycommerce")
                                    : __("You're going to delete the “Order” — are you sure?", "easycommerce")}
                        </p>
                    </div>
                </div>

                <div className="flex justify-between items-center gap-[14px] mb-5">
                    <button
                        className="font-inter font-medium text-base  border bg-ec-body text-white border-ec-body 
                        rounded-lg px-[50px] py-[10px]"
                        onClick={discardAction}
                    >
                        {__("No, Keep it", "easycommerce")}
                    </button>
                    <button
                        className="font-inter font-medium text-base text-ec-body border border-ec-body 
                        rounded-lg px-[50px] py-[10px] hover:bg-[#FF3A52] hover:border-[#FF3A52] hover:text-white"
                        onClick={deleteNowAll}
                    >
                        {__("Yes, Delete!", "easycommerce")}
                    </button>
                </div>
            </div>
        </div>
    );
};

export default DeleteAllOrderPopup;
