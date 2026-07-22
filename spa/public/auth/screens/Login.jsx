import React, { useState } from "react";
import { authApi } from "../api";
import { __ } from '@wordpress/i18n';

/**
 * Verbatim port of the original [easycommerce-login] markup: the same
 * #easycommerce-login-form structure (login-username/password/remember/submit)
 * so the existing assets/public/css/style.css rules render it pixel-identically.
 */
const Login = ({ navigate, notice }) => {
    const [userLogin, setUserLogin] = useState("");
    const [password, setPassword] = useState("");
    const [remember, setRemember] = useState(true);
    const [error, setError] = useState("");
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError("");
        setLoading(true);

        const res = await authApi.login({
            user_login: userLogin,
            password,
            remember,
        });

        if (res.ok && res.data.redirect_url) {
            window.location.href = res.data.redirect_url;
            return;
        }

        setError(res.message || __("Invalid email/username or password.", 'easycommerce'));
        setLoading(false);
    };

    return (
        <div className="easycommerce-login-form-wrapper !max-w-[620px] mx-auto !my-20 bg-white py-[77px] px-8 rounded-xl">
            <div className="easycommerce-login-form-header mb-8 flex flex-col gap-2">
                <h3 className="!m-0 !font-inter !font-semibold text-2xl text-ec-body">
                    {__("Sign In", 'easycommerce')}
                </h3>
                <p className="!m-0 font-inter font-medium text-base leading-[26px] text-ec-placeholder">
                    {__("Welcome back! sign in and let the greenery spark your joy", 'easycommerce')}
                </p>
            </div>

            {notice ? (
                <p className="text-green-600 mb-4 font-inter text-base">{notice}</p>
            ) : null}

            <div className="easycommerce-login-form-body">
                <form name="loginform" id="easycommerce-login-form" onSubmit={handleSubmit}>
                    <p className="login-username">
                        <label htmlFor="user_login">{__("Email Address", 'easycommerce')}</label>
                        <input
                            type="text"
                            name="log"
                            id="user_login"
                            className="input"
                            value={userLogin}
                            onChange={(e) => setUserLogin(e.target.value)}
                            autoComplete="username"
                            required
                        />
                    </p>
                    <p className="login-password">
                        <label htmlFor="user_pass">{__("Password", 'easycommerce')}</label>
                        <input
                            type="password"
                            name="pwd"
                            id="user_pass"
                            className="input"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            autoComplete="current-password"
                            required
                        />
                    </p>
                    <p className="login-remember">
                        <label htmlFor="rememberme">
                            <input
                                name="rememberme"
                                type="checkbox"
                                id="rememberme"
                                className="easycommerce-auth-remember"
                                value="forever"
                                checked={remember}
                                onChange={(e) => setRemember(e.target.checked)}
                            />
                            {" "}
                            {__("Remember Me", 'easycommerce')}
                        </label>
                    </p>
                    <p className="login-submit">
                        <input
                            type="submit"
                            name="wp-submit"
                            id="wp-submit"
                            className="button button-primary"
                            value={loading ? __("Signing in...", 'easycommerce') : __("Sign In", 'easycommerce')}
                            disabled={loading}
                        />
                    </p>

                    {error ? (
                        <p className="text-red-600 mt-3 font-inter text-base">{error}</p>
                    ) : null}
                </form>

                <p className="forgot-password">
                    <a
                        className="text-ec-placeholder font-inter font-normal text-base leading-[26px] hover:!text-royal-purple focus:text-royal-purple !no-underline"
                        href="#/reset"
                        onClick={(e) => {
                            e.preventDefault();
                            navigate("reset");
                        }}
                    >
                        {__("Forgot Password?", 'easycommerce')}
                    </a>
                </p>
            </div>

            <div className="easycommerce-login-form-footer flex justify-center items-center gap-2">
                <div className="easycommerce-login-form-footer">
                    {__("Don't have an account?", 'easycommerce')}{" "}
                    <a
                        href="#/register"
                        onClick={(e) => {
                            e.preventDefault();
                            navigate("register");
                        }}
                        className="font-inter text-royal-purple font-normal text-base leading-[26px] hover:!text-royal-purple focus:text-royal-purple !no-underline"
                    >
                        {__("Sign Up", 'easycommerce')}
                    </a>
                </div>
            </div>
        </div>
    );
};

export default Login;
