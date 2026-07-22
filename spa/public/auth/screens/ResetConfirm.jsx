import React, { useState } from "react";
import { authApi } from "../api";
import { __ } from '@wordpress/i18n';

/**
 * Verbatim port of the set-new-password branch of
 * views/shortcodes/reset/template-1.php (action=ecrp). Reached from the reset
 * email link which carries ?action=ecrp&key=...&login=... on the page URL.
 */
const ResetConfirm = ({ navigate, resetKey, login }) => {
    const [password, setPassword] = useState("");
    const [confirmPassword, setConfirmPassword] = useState("");
    const [error, setError] = useState("");
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError("");
        setLoading(true);

        const res = await authApi.resetConfirm({
            key: resetKey,
            login,
            password,
            confirm_password: confirmPassword,
        });

        if (res.ok) {
            navigate("login", {
                notice: res.message || __("Password has been reset successfully.", 'easycommerce'),
            });
            return;
        }

        setError(res.message || __("An error occurred. Please try again.", 'easycommerce'));
        setLoading(false);
    };

    return (
        <form id="easycommerce-reset-password-form" onSubmit={handleSubmit}>
            <div className="w-[614px] bg-white rounded-xl py-16 px-9 mx-auto">
                <h3 className="!text-black !font-inter !text-2xl !font-semibold !leading-8 !mb-2 text-center">
                    {__("Set New Password", 'easycommerce')}
                </h3>
                <p className="font-inter text-base font-medium leading-[26px] !text-ec-placeholder text-center mb-6">
                    {__("Enter your new password below.", 'easycommerce')}
                </p>

                <div className="w-full flex flex-col gap-4">
                    <div className="flex flex-col gap-2">
                        <label htmlFor="new-password" className="font-inter font-medium text-base leading-[26px] text-black">
                            {__("New Password", 'easycommerce')}
                        </label>
                        <input
                            type="password"
                            id="new-password"
                            className="easycommerce-register-input"
                            name="new-password"
                            placeholder={__("Enter new password", "easycommerce")}
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            required
                        />
                    </div>
                    <div className="flex flex-col gap-2">
                        <label htmlFor="confirm-password" className="font-inter font-medium text-base leading-[26px] text-black">
                            {__("Confirm Password", 'easycommerce')}
                        </label>
                        <input
                            type="password"
                            id="confirm-password"
                            className="easycommerce-register-input"
                            name="confirm-password"
                            placeholder={__("Confirm new password", "easycommerce")}
                            value={confirmPassword}
                            onChange={(e) => setConfirmPassword(e.target.value)}
                            required
                        />
                    </div>
                </div>

                <div>
                    <button
                        className="w-full h-12 bg-ec-primary text-white font-inter font-medium text-base leading-[26px] rounded-md mt-8 hover:!bg-ec-primary hover:text-white focus:!bg-ec-primary focus:text-white"
                        type="submit"
                        disabled={loading}
                    >
                        {loading ? __("Resetting...", 'easycommerce') : __("Reset Password", 'easycommerce')}
                    </button>
                </div>
                {error ? (
                    <p className="text-red-600 mt-3 font-inter text-base" id="easycommerce-reset-password-error-message">
                        {error}
                    </p>
                ) : null}
            </div>
        </form>
    );
};

export default ResetConfirm;
