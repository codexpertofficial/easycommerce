import React, { useState } from "react";
import { useDispatch } from "react-redux";
import { addToastData } from "../../../redux-store/slices/toastSlice";

/**
 * Demo/sample products manager.
 *
 * One popup for both directions:
 *  - No demo products yet  -> explain + "Import demo products" (parent runs the import).
 *  - Demo products present  -> "Remove demo content" with a red confirm step.
 */
const SampleProductsModal = ({ demoCount = 0, hideModal, onImport, onDeleted }) => {
    const dispatch = useDispatch();
    const [busy, setBusy] = useState(false);

    const hasDemo = demoCount > 0;

    const handleDelete = () => {
        setBusy(true);
        if (typeof easycommerce_modal === "function") easycommerce_modal(true);

        fetch(`${EASYCOMMERCE.rest_base}/importer/demo`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then((res) => res.json())
            .then((data) => {
                if (typeof easycommerce_modal === "function") easycommerce_modal(false);
                const msg =
                    (data && data.data && data.data.message) ||
                    "Demo content removed.";
                dispatch(addToastData({ type: data?.success ? "success" : "error", message: msg }));
                hideModal();
                if (typeof onDeleted === "function") onDeleted();
            })
            .catch(() => {
                if (typeof easycommerce_modal === "function") easycommerce_modal(false);
                setBusy(false);
                dispatch(addToastData({ type: "error", message: "Something went wrong. Please try again." }));
            });
    };

    return (
        <div className="fixed top-0 left-0 w-screen h-screen flex items-center justify-center bg-[#00000082] backdrop-blur-sm z-[999999]">
            <div className="bg-white rounded-xl px-8 pt-10 pb-8 w-[520px] max-w-[92vw]">
                <h3 className="font-inter font-medium text-2xl text-ec-body text-center">
                    {hasDemo ? "Remove demo content" : "Demo products"}
                </h3>

                {hasDemo ? (
                    <p className="text-center font-inter text-base text-ec-placeholder mt-3">
                        {`You currently have ${demoCount} demo ${demoCount === 1 ? "product" : "products"}. Removing them deletes the demo products, their images, and any empty demo categories. Your own products are never touched.`}
                    </p>
                ) : (
                    <p className="text-center font-inter text-base text-ec-placeholder mt-3">
                        Demo products help your storefront look complete while you set things up.
                        We’ll add a small set of products across a few categories — including images —
                        so you can preview your shop and test features. You can remove them in one click
                        anytime, and they only import into an empty store.
                    </p>
                )}

                <div className="flex justify-center items-center gap-3 mt-8">
                    {hasDemo ? (
                        <>
                            <button
                                className="font-inter font-medium text-base text-ec-body border border-ec-body rounded-lg px-8 py-[10px]"
                                onClick={hideModal}
                                disabled={busy}
                            >
                                Cancel
                            </button>
                            <button
                                className="font-inter font-medium text-base text-white bg-[#FF3A52] border border-[#FF3A52] rounded-lg px-8 py-[10px] disabled:opacity-60"
                                onClick={handleDelete}
                                disabled={busy}
                            >
                                Delete demo products
                            </button>
                        </>
                    ) : (
                        <>
                            <button
                                className="font-inter font-medium text-base text-ec-body border border-ec-body rounded-lg px-8 py-[10px]"
                                onClick={hideModal}
                            >
                                Cancel
                            </button>
                            <button
                                className="font-inter font-medium text-base text-white bg-ec-primary border border-ec-primary rounded-lg px-8 py-[10px]"
                                onClick={() => {
                                    hideModal();
                                    if (typeof onImport === "function") onImport();
                                }}
                            >
                                Import demo products
                            </button>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
};

export default SampleProductsModal;
