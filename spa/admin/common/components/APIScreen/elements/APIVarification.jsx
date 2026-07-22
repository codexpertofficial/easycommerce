import React, { useState } from "react";
import { Bounce, toast } from "react-toastify";
import Cookies from "universal-cookie";
import { __ } from "@wordpress/i18n";
import TextField from "../../inputs/TextField";

const APIVarification = ({
    onClose,
    switchModalTab,
    setUserAfterVarification,
}) => {
    const [errMsg, setErrMsg] = useState("");
    const [email, setEmail] = useState("");
    const [token, setToken] = useState("");

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
            autoClose: 2000,
            hideProgressBar: false,
            closeOnClick: true,
            pauseOnHover: true,
            draggable: false,
            progress: undefined,
            theme: "colored",
            transition: Bounce,
        });
    };

    const handleFormSubmit = (e) => {
        e.preventDefault();
        const source = EASYCOMMERCE.home_url;

        if (!token || !email) {
            showToast("error", __( 'Please fill all the fields', 'easycommerce' ));
            return;
        }

        easycommerce_modal(true);
        setErrMsg("");

        fetch(`${EASYCOMMERCE.rest_base}/connectivity/token/verify`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify({ email: email, token: token, source: source }),
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success && data.data.verified) {
                    const cookies = new Cookies(null, { path: "/" });

                    cookies.set("easycommerce-user", JSON.stringify(data.data.user), {
                        path: "/",
                        maxAge: 86400 * 30,
                    });

                    setUserAfterVarification(data.data.user);

                    showToast("success", data.data.message);

                    onClose();
                } else {
                    setErrMsg(data.data.message);
                }
            });
    };

    return (
        <div className="fixed z-20 top-0 left-0 w-screen h-screen flex justify-center items-center bg-[#0000003B] backdrop-blur-sm">
            <div className="relative w-[600px] py-[32px] rounded-xl bg-white">
                <button
                    className="absolute top-[-18px] right-[-23px] group w-6 h-6 rounded-full bg-white hover:bg-[#fa4109] transition-colors duration-200 flex items-center justify-center"
                    onClick={onClose}
                    aria-label={ __( 'Close', 'easycommerce' ) }
                    type="button"
                >
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        className="w-4 h-4"
                    >
                        <path
                            fillRule="evenodd"
                            clipRule="evenodd"
                            d="M7.26042 7.26042C7.60764 6.91319 8.17015 6.91319 8.51731 7.26042L12 10.7431L15.4827 7.26042C15.8299 6.91319 16.3924 6.91319 16.7396 7.26042C17.0868 7.60764 17.0868 8.17015 16.7396 8.51731L13.2569 12L16.7396 15.4827C17.0868 15.8299 17.0868 16.3924 16.7396 16.7396C16.3924 17.0868 15.8299 17.0868 15.4827 16.7396L12 13.2569L8.51731 16.7396C8.17009 17.0868 7.60759 17.0868 7.26042 16.7396C6.91325 16.3924 6.91319 15.8299 7.26042 15.4827L10.7431 12L7.26042 8.51731C6.91319 8.17009 6.91319 7.60759 7.26042 7.26042Z"
                            className="fill-[#3C3C42] group-hover:fill-white transition-colors duration-300"
                        />
                    </svg>
                </button>

                <div className="flex flex-col items-center gap-8">
                    <div className="flex flex-col items-center gap-[10px]">
                        <div className="flex flex-col items-center gap-3">
                            <h2 className="text-[#120350] text-2xl font-inter font-medium">
                                { __( 'Enter Your Connection Key', 'easycommerce' ) }
                            </h2>
                            <p className="w-[80%] mx-auto text-center text-ec-body font-inter font-normal text-base">
                                { __( 'The link in your email connects AI automatically. To do it manually, paste the key we emailed below.', 'easycommerce' ) }
                            </p>
                        </div>

                        <div className="flex justify-between items-center gap-2">
                            <form
                                onSubmit={handleFormSubmit}
                                className="flex flex-col gap-6"
                                id="easycommerce-api-verify-form"
                            >
                                <div className="flex flex-col gap-3">
                                    <div>
                                        <label
                                            htmlFor="easycommerce-api-email-input"
                                            className="text-base leading-[26px] font-inter text-ec-body font-medium"
                                        >
                                            { __( 'Your Email', 'easycommerce' ) }
                                        </label>
                                        <div className="pt-2 flex justify-between items-center gap-2 w-[452px]">
                                            <TextField
                                                type="email"
                                                name="email"
                                                id="easycommerce-api-email-input"
                                                placeholder={ __( 'Enter your Email', 'easycommerce' ) }
                                                className=""
                                                value={email}
                                                onChange={(e) => setEmail(e.target.value)}
                                                required
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="easycommerce-api-key-input"
                                            className="text-base leading-[26px] font-inter text-ec-body font-medium"
                                        >
                                            { __( 'Your Key', 'easycommerce' ) }
                                        </label>
                                        <div className="pt-2 flex justify-between items-center gap-2">
                                            <TextField
                                                type="text"
                                                name="token"
                                                id="easycommerce-api-key-input"
                                                form="easycommerce-api-verify-form"
                                                placeholder={ __( 'Paste your key', 'easycommerce' ) }
                                                className="w-[452px]"
                                                value={token}
                                                onChange={(e) => setToken(e.target.value)}
                                                required
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div className="flex flex-col items-center gap-3">
                                    <button
                                        type="button"
                                        className="w-full rounded-md bg-ec-primary hover:bg-ec-secondary text-white 
                                        py-3 font-inter font-normal text-center text-base leading-[26px] transition-all 
                                        ease-in-out duration-300"
                                        onClick={handleFormSubmit}
                                    >
                                        { __( 'Connect AI', 'easycommerce' ) }
                                    </button>
                                    <div className="flex justify-center items-center gap-[2px]">
                                        <button
                                            className="font-inter text-ec-primary text-base leading-[26px] underline"
                                            onClick={switchModalTab}
                                            type="button"
                                        >
                                            { __( 'I don\'t have a key', 'easycommerce' ) }
                                        </button>
                                    </div>
                                </div>

                                {errMsg && (
                                    <p
                                        className="w-full rounded-md bg-[#FF3A520D] text-[#FF3A52] 
                                        py-3 font-inter font-normal text-center text-sm"
                                    >
                                        {errMsg}
                                    </p>
                                )}
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default APIVarification;
