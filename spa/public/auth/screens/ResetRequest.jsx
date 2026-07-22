import React, { useState } from "react";
import { authApi } from "../api";
import { __ } from '@wordpress/i18n';

/**
 * Verbatim port of the default (request) branch of
 * views/shortcodes/reset/template-1.php.
 */
const ResetRequest = ({ navigate }) => {
    const [userLogin, setUserLogin] = useState("");
    const [message, setMessage] = useState({ type: "", text: "" });
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setMessage({ type: "", text: "" });
        setLoading(true);

        const res = await authApi.resetRequest({ user_login: userLogin });

        if (res.ok) {
            setMessage({
                type: "success",
                text: res.message || __("Check your email for the confirmation link.", 'easycommerce'),
            });
            setUserLogin("");
        } else {
            setMessage({
                type: "error",
                text: res.message || __("An error occurred. Please try again.", 'easycommerce'),
            });
        }

        setLoading(false);
    };

    return (
        <form id="easycommerce-reset-form" onSubmit={handleSubmit}>
            <div className="w-[614px] bg-white rounded-xl py-16 px-9 mx-auto">
                <h3 className="!text-black !font-inter !text-2xl !font-semibold !leading-8 !mb-2 text-center">
                    {__("Reset your password", 'easycommerce')}
                </h3>
                <p className="font-inter text-base font-medium leading-[26px] !text-ec-placeholder text-center">
                    {__("Enter a valid e-mail to receive instruction on how to reset your password.", 'easycommerce')}
                </p>

                <div className="w-full flex flex-col gap-[10px]">
                    <div className="flex flex-col gap-2">
                        <label htmlFor="easycommerce-reset-email-username" className="font-inter font-medium text-base leading-[26px] text-black">
                            {__("Email or username", 'easycommerce')}
                        </label>
                        <input
                            type="text"
                            id="easycommerce-reset-email-username"
                            className="easycommerce-register-input"
                            name="easycommerce-reset-email-username"
                            placeholder={__("Email or username", "easycommerce")}
                            value={userLogin}
                            onChange={(e) => setUserLogin(e.target.value)}
                            required
                        />
                    </div>
                </div>
                <div>
                    <button
                        className="w-full h-12 bg-ec-primary text-white font-inter font-medium text-base leading-[26px] rounded-md mt-8 hover:!bg-ec-primary hover:text-white focus:!bg-ec-primary focus:text-white"
                        type="submit"
                        name="easycommerce-reset-password"
                        disabled={loading}
                    >
                        {loading ? __("Sending...", 'easycommerce') : __("Reset Password", 'easycommerce')}
                    </button>
                </div>
                {message.text ? (
                    <p
                        className={`mt-3 font-inter text-base ${message.type === "success" ? "text-green-600" : "text-red-600"}`}
                        id="easycommerce-reset-error-message"
                    >
                        {message.text}
                    </p>
                ) : null}
                <div className="mt-4">
                    <p className="text-center">
                        {__("Don't have an account?", 'easycommerce')}{" "}
                        <a
                            className="text-ec-primary font-inter font-medium text-base leading-[26px] hover:!text-ec-primary focus:text-ec-primary !no-underline"
                            href="#/register"
                            onClick={(e) => {
                                e.preventDefault();
                                navigate("register");
                            }}
                        >
                            {__("Sign Up", 'easycommerce')}
                        </a>
                    </p>
                </div>
            </div>
        </form>
    );
};

export default ResetRequest;
