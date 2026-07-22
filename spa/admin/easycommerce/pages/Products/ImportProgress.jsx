import React, { useEffect } from "react";
import { toast } from "react-toastify";
import { __ } from "@wordpress/i18n";

const ImportProgress = ({  }) => {

    useEffect(() => {
        fetch(`${EASYCOMMERCE.rest_base}/importer/samples`, {
            method: "POST",
            headers: {
                "X-WP-Nonce": EASYCOMMERCE.nonce,
                "Content-Type": "application/json",
            },
        })
            .then(res => res.json())
            .then(({ success, data }) => {
                success
                    ? toast.success(data?.message || __("Demo products imported!", "easycommerce"))
                    : toast.error(data || __("Failed to import", "easycommerce"));

                setTimeout(() => window.location.reload(), 10000);
            })
            .catch(() => {
                toast.error(__("Failed to import demo products", "easycommerce"));
                setTimeout(() => window.location.reload(), 10000);
            });
    }, []);

    return (
        <div className="h-full max-w-full bg-white pt-[140px] text-center rounded-2xl px-6">
            <div className="max-w-md mx-auto">
                <svg className="animate-spin h-12 w-12 mx-auto mb-4 text-ec-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <h3 className="text-xl font-semibold mb-4 text-ec-title">{__("Importing Sample Products", "easycommerce")}</h3>
                <p className="text-ec-light-black text-base">{__("We are generating some sample products into your store. You can refresh the page to see the progress..", "easycommerce")}</p>
            </div>
        </div>
    );
};

export default ImportProgress;