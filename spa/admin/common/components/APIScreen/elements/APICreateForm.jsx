import React, { useState } from "react";
import { Bounce, toast } from "react-toastify";
import { __ } from "@wordpress/i18n";
import TextField from "../../inputs/TextField";

const APICreateForm = ({ onClose, switchModalTab }) => {
    const [name, setName] = useState(EASYCOMMERCE.user?.name ?? '');
    const [email, setEmail] = useState(EASYCOMMERCE.user?.email ?? '');

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

    const handleFormSubmit = (event) => {
        event.preventDefault();

        if (!name || !email) {
            showToast("error", __( 'Please fill all the fields', 'easycommerce' ));
            return;
        }

        easycommerce_modal(true);

        fetch(`${EASYCOMMERCE.rest_base}/connectivity/token`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify({ name: name, email: email, source: EASYCOMMERCE.home_url }),
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success) {
                    // Magic link is the primary path: close the popup and point the
                    // user to their inbox. Code entry stays available via "I have an
                    // API key" if they reopen the modal.
                    showToast("success", __( 'Check your email and click the link to connect AI.', 'easycommerce' ));

                    onClose();
                } else {
                    showToast("error", data.data.message);
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
                    <div className="flex flex-col items-center gap-6">
                        <div className="flex flex-col items-center gap-3">
                            <h2 className="text-ec-title text-2xl font-inter font-medium">
                                { __( 'Connect EasyCommerce AI', 'easycommerce' ) }
                            </h2>

                            <p className="w-[80%] mx-auto text-center text-ec-body font-inter font-normal text-base">
                                { __( 'Enter your name and email - we\'ll send a one-click link to connect AI, plus a backup key.', 'easycommerce' ) }
                            </p>
                        </div>

                        <form
                            onSubmit={handleFormSubmit}
                            className="flex flex-col gap-6"
                        >
                            <div className="flex flex-col gap-3">
                                <div>
                                    <label
                                        htmlFor="easycommerce-api-name-input"
                                        className="text-base leading-[26px] font-inter text-ec-body font-medium"
                                    >
                                        { __( 'Your Name', 'easycommerce' ) }
                                    </label>
                                    <div className="pt-2 flex justify-between items-center gap-2">
                                        <TextField
                                            className="w-[160px] min-[1440px]:w-[452px]"
                                            name="name"
                                            value={name}
                                            onChange={(e) => setName(e.target.value)}
                                            placeholder={ __( 'Enter your Name', 'easycommerce' ) }
                                            id="easycommerce-api-name-input"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label
                                        htmlFor="easycommerce-api-email-input"
                                        className="text-base leading-[26px] font-inter text-ec-body font-medium"
                                    >
                                        { __( 'Your Email', 'easycommerce' ) }
                                    </label>
                                    <div className="pt-2 flex justify-between items-center gap-2">
                                        <TextField
                                            type="email"
                                            name="email"
                                            id="easycommerce-api-email-input"
                                            placeholder={ __( 'Enter your Email', 'easycommerce' ) }
                                            value={email}
                                            onChange={(e) =>
                                                setEmail(e.target.value)
                                            }
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="flex flex-col items-center gap-3">
                                <button
                                    type="button"
                                    className="w-full rounded-md bg-ec-primary hover:bg-ec-secondary text-white py-3 
									text-center text-base leading-[26px] transition-all ease-in-out duration-300"
                                    onClick={handleFormSubmit}
                                >
                                    { __( 'Email My Link', 'easycommerce' ) }
                                </button>
                                <div className="flex justify-center items-center gap-[2px]">
                                    <button
                                        className="font-inter text-ec-primary text-base leading-[26px] underline"
                                        onClick={switchModalTab}
                                    >
                                        { __( 'I already have a key', 'easycommerce' ) }
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default APICreateForm;
