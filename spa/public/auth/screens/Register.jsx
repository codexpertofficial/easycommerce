import React, { useState } from "react";
import { authApi } from "../api";
import { __ } from '@wordpress/i18n';

/**
 * Verbatim port of views/shortcodes/register/template-1.php.
 */
const Register = ({ navigate, auth }) => {
    const [form, setForm] = useState({
        username: "",
        email: "",
        password: "",
        confirmPassword: "",
    });
    const [error, setError] = useState("");
    const [loading, setLoading] = useState(false);

    const update = (key) => (e) => setForm((f) => ({ ...f, [key]: e.target.value }));

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError("");

        if (form.password !== form.confirmPassword) {
            setError(__("Passwords do not match", 'easycommerce'));
            return;
        }

        setLoading(true);

        const res = await authApi.register({
            username: form.username,
            email: form.email,
            password: form.password,
            confirmPassword: form.confirmPassword,
        });

        if (res.ok) {
            navigate("login", { notice: __("Registration Complete please login", 'easycommerce') });
            return;
        }

        setError(res.message || __("Registration failed. Please try again.", 'easycommerce'));
        setLoading(false);
    };

    const terms = (auth && auth.terms_url) || "#";
    const privacy = (auth && auth.privacy_url) || "#";

    return (
        <form id="easycommerce-registration-form" onSubmit={handleSubmit}>
            <div className="w-[614px] bg-white rounded-xl py-16 px-9 mx-auto">
                <h3 className="!text-black !font-inter !text-2xl !font-semibold !leading-8 !mb-2">
                    {__("Registration", 'easycommerce')}
                </h3>
                <p className="font-inter text-base font-medium leading-[26px] !text-ec-placeholder">
                    {__("Welcome Back! Sign in and let the greenery spark your joy", 'easycommerce')}
                </p>

                <div className="w-full flex flex-col gap-[10px]">
                    <div className="flex flex-col gap-2 mb-6">
                        <label htmlFor="easycommerce-register-username" className="font-inter font-medium text-base leading-[26px] text-black">
                            {__("Username", 'easycommerce')}
                        </label>
                        <input
                            type="text"
                            id="easycommerce-register-username"
                            className="easycommerce-register-input"
                            name="easycommerce-register-username"
                            placeholder={__("User Name", "easycommerce")}
                            value={form.username}
                            onChange={update("username")}
                            required
                        />
                    </div>
                    <div className="flex flex-col gap-2 mb-6">
                        <label htmlFor="easycommerce-register-email" className="font-inter font-medium text-base leading-[26px] text-black">
                            {__("Email Address", 'easycommerce')}
                        </label>
                        <input
                            type="email"
                            id="easycommerce-register-email"
                            className="easycommerce-register-input"
                            name="easycommerce-register-email"
                            placeholder={__("Enter your Email", "easycommerce")}
                            value={form.email}
                            onChange={update("email")}
                            required
                        />
                    </div>
                    <div className="flex flex-col gap-2 mb-6">
                        <label htmlFor="easycommerce-register-new-password" className="font-inter font-medium text-base leading-[26px] text-black">
                            {__("New password", 'easycommerce')}
                        </label>
                        <input
                            type="password"
                            id="easycommerce-register-new-password"
                            className="easycommerce-register-input"
                            name="easycommerce-register-new-password"
                            placeholder={__("Enter new password", "easycommerce")}
                            value={form.password}
                            onChange={update("password")}
                            required
                        />
                    </div>

                    <div className="flex flex-col gap-2">
                        <label htmlFor="easycommerce-register-confirm-password" className="font-inter font-medium text-base leading-[26px] text-black">
                            {__("Confirm password", 'easycommerce')}
                        </label>
                        <input
                            type="password"
                            id="easycommerce-register-confirm-password"
                            className="easycommerce-register-input"
                            name="easycommerce-register-confirm-password"
                            placeholder={__("Confirm new password", "easycommerce")}
                            value={form.confirmPassword}
                            onChange={update("confirmPassword")}
                            required
                        />
                    </div>
                </div>
                <div>
                    <button
                        className="w-full h-12 bg-ec-primary text-white font-inter font-medium text-base leading-[26px] rounded-md mt-8 hover:!bg-ec-secondary hover:text-white transition duration-300 focus:!bg-ec-primary focus:text-white"
                        type="submit"
                        name="easycommerce-register-submit"
                        disabled={loading}
                    >
                        {loading ? __("Creating account...", 'easycommerce') : __("Sign up", 'easycommerce')}
                    </button>
                </div>
                {error ? (
                    <p className="text-red-600 mt-3 font-inter text-base" id="easycommerce-registration-error-message">
                        {error}
                    </p>
                ) : null}
                <div className="mt-4">
                    <span className="text-ec-placeholder text-normal font-inter text-base leading-[26px]">
                        {__("By creating an account, you agree to our", 'easycommerce')}{" "}
                        <a className="hover:text-ec-secondary" href={terms}>
                            {__("Terms of Service", 'easycommerce')}
                        </a>{" "}
                        {__("and", 'easycommerce')}{" "}
                        <a className="hover:text-ec-secondary" href={privacy}>
                            {__("Privacy Policy", 'easycommerce')}
                        </a>
                    </span>
                </div>
                <div className="mt-4">
                    <p className="text-center">
                        {__("Don't have an account?", 'easycommerce')}{" "}
                        <a
                            className="text-ec-primary font-inter font-normal text-base leading-[26px] hover:!text-ec-primary focus:text-ec-primary !no-underline"
                            href="#/login"
                            onClick={(e) => {
                                e.preventDefault();
                                navigate("login");
                            }}
                        >
                            {__("Log in", 'easycommerce')}
                        </a>
                    </p>
                </div>
            </div>
        </form>
    );
};

export default Register;
