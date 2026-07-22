import React, { useState } from "react";
import { __ } from "@wordpress/i18n";

// toastify
import { Bounce, toast } from "react-toastify";

const viewPassIcon = `${EASYCOMMERCE.assets}public/img/icons/view-password-icon.png`;
const hidePassIcon = `${EASYCOMMERCE.assets}public/img/icons/hide-password-icon.png`;

const Password = () => {
    const [newPassType, setNewPassType] = useState("password");
    const [confPassType, setConfPassType] = useState("password");

    const handleFormSubmit = async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const { newPassword, confirmNewPassword } =
            Object.fromEntries(formData);

        if (newPassword.length < 6 || confirmNewPassword.length < 6) {
            toast.error(__( "Password must be at least 6 characters long", "easycommerce" ), {
                position: "top-right",
                autoClose: 3000,
                hideProgressBar: false,
                closeOnClick: true,
                pauseOnHover: false,
                draggable: true,
                progress: undefined,
                theme: "colored",
                transition: Bounce,
            });
            return;
        }

        if (newPassword !== confirmNewPassword) {
            toast.error(__( "Passwords do not match", "easycommerce" ), {
                position: "top-right",
                autoClose: 3000,
                hideProgressBar: false,
                closeOnClick: true,
                pauseOnHover: false,
                draggable: true,
                progress: undefined,
                theme: "colored",
                transition: Bounce,
            });
        } else {
            try {
                const response = await fetch(`${EASYCOMMERCE.rest_base}/me`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-WP-Nonce": EASYCOMMERCE.nonce,
                    },
                    body: JSON.stringify({
                        fields: {
                            password: newPassword,
                        },
                    }),
                });

                if (!response.ok) {
                    throw new Error("Failed to change password");
                }
                toast.success(__( "Password updated successfully", "easycommerce" ), {
                    position: "top-right",
                    style: {
                        margin: "30px 0 0 0",
                        fontSize: "16px",
                        fontWeight: "500",
                        lineHeight: "26px",
                        color: "#fff",
                    },
                    autoClose: 3000,
                    hideProgressBar: false,
                    closeOnClick: true,
                    pauseOnHover: false,
                    draggable: true,
                    progress: undefined,
                    theme: "colored",
                    transition: Bounce,
                });
            } catch (error) {
                toast.error(__( "Error updating password", "easycommerce" ), {
                    position: "top-right",
                    style: {
                        margin: "30px 0 0 0",
                        fontSize: "16px",
                        fontWeight: "500",
                        lineHeight: "26px",
                        color: "#fff",
                    },
                    autoClose: 3000,
                    hideProgressBar: false,
                    closeOnClick: true,
                    pauseOnHover: false,
                    draggable: true,
                    progress: undefined,
                    theme: "colored",
                    transition: Bounce,
                });
            }
        }
    };

    return (
        <>
            <div>
                <h3 className="easycommerce-dashboard-section-title !text-lg sm:!text-2xl">
                    {__( "Set your password", "easycommerce" )}
                </h3>
                <div className="w-full sm:w-[620px] mt-4 sm:mt-[50px] py-8 sm:py-[60px] px-4 sm:px-10 mx-auto bg-white border border-ec-border rounded-2xl shadow-[0_2px_16px_-8px_rgba(18,3,80,0.10)]">
                    <div className="flex flex-col justify-center items-center gap-[6px]">
                        <h2 className="easycommerce-dashboard-password-heading">
                            {__( "Change your Password", "easycommerce" )}
                        </h2>
                        <p className="easycommerce-dashboard-password-subheading">
                            {__( "Enter a new password below to change your password", "easycommerce" )}
                        </p>
                    </div>

                    <form
                        className="mt-[52px] flex flex-col gap-8"
                        onSubmit={handleFormSubmit}
                    >
                        <div className="w-full flex flex-col gap-[10px]">
                            <div className="flex flex-col gap-2">
                                <label
                                    htmlFor="newPassword"
                                    className="font-inter font-medium text-base leading-[26px] text-black"
                                >
                                    {__( "New password", "easycommerce" )}
                                </label>

                                <div className="easycommerce-dashboard-form-password h-12 flex justify-between items-center gap-3 pl-4 pr-4 border border-[#DBDBDB] rounded-xl hover:!border-ec-secondary focus:!border-ec-primary">
                                    <input
                                        type={newPassType}
                                        id="newPassword"
                                        name="newPassword"
                                        placeholder={__( "Enter new password", "easycommerce" )}
                                        className="easycommerce-dashboard-form-password-input flex-1 bg-transparent focus:outline-none"
                                        required
                                    />

                                    <button
                                        className="easycommerce-dashboard-form-password-show-hide-btn"
                                        type="button"
                                        onClick={() =>
                                            setNewPassType(
                                                newPassType === "password"
                                                    ? "text"
                                                    : "password"
                                            )
                                        }
                                    >
                                        <img
                                            src={
                                                newPassType === "text"
                                                    ? hidePassIcon
                                                    : viewPassIcon
                                            }
                                            alt={__( "icon", "easycommerce" )}
                                            className="w-[18px] h-[14px]"
                                        />
                                    </button>
                                </div>
                            </div>

                            <div className="flex flex-col gap-2">
                                <label
                                    htmlFor="confirmNewPassword"
                                    className="font-inter font-medium text-base leading-[26px] text-black"
                                >
                                    {__( "Confirm new password", "easycommerce" )}
                                </label>

                                <div className="easycommerce-dashboard-form-password h-12 flex justify-between items-center gap-3 pl-4 pr-4 border border-[#DBDBDB] rounded-xl hover:border-ec-secondary focus:border-ec-primary">
                                    <input
                                        type={confPassType}
                                        id="confirmNewPassword"
                                        name="confirmNewPassword"
                                        placeholder={__( "Confirm new password", "easycommerce" )}
                                        className="easycommerce-dashboard-form-password-input flex-1 bg-transparent focus:outline-none"
                                        required
                                    />

                                    <button
                                        className="easycommerce-dashboard-form-password-show-hide-btn"
                                        type="button"
                                        onClick={() =>
                                            setConfPassType(
                                                confPassType === "password"
                                                    ? "text"
                                                    : "password"
                                            )
                                        }
                                    >
                                        <img
                                            src={
                                                confPassType === "text"
                                                    ? hidePassIcon
                                                    : viewPassIcon
                                            }
                                            alt={__( "icon", "easycommerce" )}
                                            className="w-[18px] h-[14px]"
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div className="flex justify-center">
                            <button
                                className="easycommerce-dashboard-form-btn save"
                                type="submit"
                            >
                                {__( "Change Password", "easycommerce" )}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
};

export default Password;
