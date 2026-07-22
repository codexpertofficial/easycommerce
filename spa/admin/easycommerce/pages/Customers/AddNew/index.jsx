import React, { useState } from "react";
import { __ } from "@wordpress/i18n";


//Images and icons
const PreDefinedImage = `${EASYCOMMERCE.assets}admin/img/customer-preloader-image.png`;
const cameraIcon = `${EASYCOMMERCE.assets}admin/img/icons/customer-camera-icon.png`;
const deletePopUpClose = `${EASYCOMMERCE.assets}admin/img/icons/delete-popup-close.png`;

const AddNewCustomer = ({ setIsAddNew }) => {
    const [photo, setPhoto] = useState(null);
    const [passwordError, setPasswordError] = useState("");

    const handleSubmit = async (event) => {
        event.preventDefault();
        const {
            first_name,
            last_name,
            email,
            password,
            password_again,
            photo,
            meta,
        } = event.target;
        if (password.value !== password_again.value) {
            setPasswordError(__("Passwords do not match.", "easycommerce"));
            return;
        }
        if (password.value.length < 6) {
            setPasswordError(__("Password must be at least 6 characters long.", "easycommerce"));
            return;
        }
        easycommerce_modal(true);

        const url = EASYCOMMERCE.rest_base + "/customers";
        const requestOptions = {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify({
                first_name: first_name.value,
                last_name: last_name.value,
                email: email.value,
                password: password.value,
                password_again: password_again.value,
                photo: photo.value,
                meta: [],
            }),
        };

        try {
            const response = await fetch(url, requestOptions);
            const data = await response.json();
            if (data.success) {
                setIsAddNew(false);
                window.location.reload();
            }
        } catch (error) {
        } finally {
            easycommerce_modal(false);
        }
    };

    const openMediaLibrary = () => {
        // Create the media frame.
        const frame = wp.media({
            title: __("Select a Image", "easycommerce"),
            button: {
                text: __("Use selected image", "easycommerce"),
            },
            multiple: false,
        });

        // When an image is selected in the media frame...
        frame.on("select", () => {
            const attachments = frame.state().get("selection").toJSON();
            setPhoto(attachments[0].url);
        });
        // Finally, open the modal on click
        frame.open();
    };

    return (
        <>
            <div className="fixed w-full h-full bg-[#00000082] top-0 left-0 z-[10000]">
                <div className="w-[537px] absolute top-14 left-1/2 transform -translate-x-1/2">
                    <div className="absolute left-[90px]">
                        <button className=" w-[24px] h-[24px] flex justify-center items-center rounded-full bg-[#FFFFFF] border border-[#FAF9FF] ">
                        <img
                            src={deletePopUpClose}
                            alt="delete-popUp-close"
                            className="absolute w-[24px] h-[24px] top-[-18px] left-[445px]"
                            onClick={() => setIsAddNew(false)}
                        />
                        </button>
                    </div>
                    <div className="w-full pt-8 bg-white rounded-t-xl pb-[65px] relative">
                        <h2 className="text-ec-body text-2xl leading-8 font-inter font-medium text-center">
                            {__("Add New Customer", "easycommerce")}
                        </h2>
                        <div className="absolute -bottom-[49px] -translate-x-1/2 left-1/2">
                            <div className="relative">
                                <button
                                    onClick={openMediaLibrary}
                                    className="w-[100px] h-[100px] rounded-full bg-[#F2F2F2] p-0 flex items-center justify-center overflow-hidden"
                                >
                                    <img
                                        src={photo || PreDefinedImage}
                                        className="w-full h-full"
                                        alt={__("Customer", "easycommerce")}
                                    />
                                    <div className="absolute w-[32px] h-[32px] rounded-full border-[#F0EDFB] bg-[#FFFFFF30] flex items-center justify-center -right-[10px] top-[55px]" style={{boxShadow: '0px 4px 16.1px 0px #00000040', backdropFilter: 'blur(7.099999904632568px)'}}>
                                        <img  className="w-[17px] h-[14px]" src={cameraIcon} alt={__("Edit", "easycommerce")} />
                                    </div>
                                </button>
                            </div>
                        </div> 
                    </div>
                    <div className="w-full bg-white p-10 rounded-b-xl pt-[60px] pb-8">
                        <form onSubmit={handleSubmit}>
                            <input type="hidden" name="photo" value={photo} />
                            <label
                                htmlFor="easycommerce-first-name"
                                className="block mb-4 h"
                            >
                                <span className=" text-ec-body block text-base font-medium font-inter leading-4 mb-1">
                                    {__("First Name", "easycommerce")}
                                </span>

                                <div className="relative">
                                    <span
                                        id="first-name-icon"
                                       className="absolute left-4 top-8 transform -translate-y-1/2 transition-opacity duration-200 text-gray-400"
                                    >
                                        <svg width="14" height="18" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M6.94482 1.79992C5.38897 1.79992 4.12805 3.06084 4.12805 4.61669C4.12805 6.17173 5.38897 7.43263 6.94482 7.43263C8.50067 7.43263 9.76159 6.17171 9.76159 4.61669C9.76159 3.06084 8.50067 1.79992 6.94482 1.79992ZM2.82812 4.61669C2.82812 2.34256 4.67158 0.5 6.94482 0.5C9.21806 0.5 11.0615 2.34264 11.0615 4.61669C11.0615 6.89 9.21806 8.73256 6.94482 8.73256C4.67158 8.73256 2.82812 6.88991 2.82812 4.61669Z" fill="#7F7F98"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.55492 16.532H12.3312C12.0476 13.3009 9.67516 10.8993 6.94304 10.8993C4.21091 10.8993 1.8384 13.3009 1.55492 16.532ZM0.226562 17.182C0.226562 13.0784 3.15461 9.59937 6.9431 9.59937C10.7316 9.59937 13.6596 13.0784 13.6596 17.182C13.6596 17.5411 13.368 17.8319 13.0097 17.8319H0.876523C0.518223 17.8319 0.226562 17.5411 0.226562 17.182Z" fill="#7F7F98"/>
                                        </svg>
                                    </span>
                                    <input
                                        id="easycommerce-first-name"
                                        type="text"
                                        placeholder={__("Enter first name", "easycommerce")}
                                        name="first_name"
                                        required
                                        defaultValue=""
                                        onInput={(e) => {
                                            const icon = document.getElementById("first-name-icon");
                                            const input = e.target;
                                            if (input.value.trim() === "") {
                                                icon.classList.remove("opacity-0", "invisible");
                                                icon.classList.add("opacity-100", "visible");
                                                input.classList.remove("pl-4");
                                                input.classList.add("pl-11");
                                            } else {
                                                icon.classList.remove("opacity-100", "visible");
                                                icon.classList.add("opacity-0", "invisible");
                                                input.classList.remove("pl-11");
                                                input.classList.add("pl-4");
                                            }
                                        }}
                                       className="h-ec-input mt-3 pr-4 py-4 rounded-lg font-inter border border-ec-light-black placeholder-ec-light-black text-base font-normal hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-all duration-300 ease-in-out w-full pl-11"
                                    />
                                </div>
                            </label>
                            <label
                                htmlFor="easycommerce-last-name"
                                className="block mb-4"
                            >
                                <span className="text-ec-body block text-base font-medium font-inter leading-4 mb-1">
                                    {__("Last Name", "easycommerce")}
                                </span>

                                <div className="relative">
                                    <span
                                        id="last-name-icon"
                                        className="absolute left-4 top-8 transform -translate-y-1/2 transition-opacity duration-300 text-gray-400"
                                    >
                                        <svg width="14" height="18" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M6.94482 1.79992C5.38897 1.79992 4.12805 3.06084 4.12805 4.61669C4.12805 6.17173 5.38897 7.43263 6.94482 7.43263C8.50067 7.43263 9.76159 6.17171 9.76159 4.61669C9.76159 3.06084 8.50067 1.79992 6.94482 1.79992ZM2.82812 4.61669C2.82812 2.34256 4.67158 0.5 6.94482 0.5C9.21806 0.5 11.0615 2.34264 11.0615 4.61669C11.0615 6.89 9.21806 8.73256 6.94482 8.73256C4.67158 8.73256 2.82812 6.88991 2.82812 4.61669Z" fill="#7F7F98"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.55492 16.532H12.3312C12.0476 13.3009 9.67516 10.8993 6.94304 10.8993C4.21091 10.8993 1.8384 13.3009 1.55492 16.532ZM0.226562 17.182C0.226562 13.0784 3.15461 9.59937 6.9431 9.59937C10.7316 9.59937 13.6596 13.0784 13.6596 17.182C13.6596 17.5411 13.368 17.8319 13.0097 17.8319H0.876523C0.518223 17.8319 0.226562 17.5411 0.226562 17.182Z" fill="#7F7F98"/>
                                        </svg>
                                    </span>

                                    <input
                                        id="easycommerce-last-name"
                                        type="text"
                                        placeholder={__("Enter last name", "easycommerce")}
                                        name="last_name"
                                        required
                                        defaultValue=""
                                        onInput={(e) => {
                                            const icon = document.getElementById("last-name-icon");
                                            const input = e.target;
                                            if (input.value.trim() === "") {
                                                icon.classList.remove("opacity-0", "invisible");
                                                icon.classList.add("opacity-100", "visible");
                                                input.classList.remove("pl-4");
                                                input.classList.add("pl-11");
                                            } else {
                                                icon.classList.remove("opacity-100", "visible");
                                                icon.classList.add("opacity-0", "invisible");
                                                input.classList.remove("pl-11");
                                                input.classList.add("pl-4");
                                            }
                                        }}
                                        className="h-ec-input mt-3 pr-4 py-4 rounded-lg font-inter border border-ec-light-black placeholder-ec-light-black text-base font-normal hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-all duration-300 ease-in-out w-full pl-11"
                                    />
                                </div>
                            </label>
                            <label
                                htmlFor="easycommerce-email"
                                className="block mb-4"
                            >
                                <span className="text-ec-body block text-base font-medium font-inter leading-4 mb-1">
                                    {__("Email Address", "easycommerce")}
                                </span>

                                <div className="relative">
                                    <span
                                        id="email-icon"
                                        className="absolute left-4 top-8 transform -translate-y-1/2 transition-opacity duration-300 text-gray-400"
                                    >
                                        <svg width="18" height="15" viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M18.0822 14.5H1.91779C0.859074 14.4985 0.0016818 13.7305 0 12.7823V2.21775C0.00168438 1.26947 0.859096 0.501506 1.91779 0.5H18.0822C19.1409 0.501509 19.9983 1.26949 20 2.21775V12.7823C19.9983 13.7305 19.1409 14.4985 18.0822 14.5ZM2.22698 13.293H17.7732L12.6448 8.38258C12.0115 8.9876 11.5002 9.47646 11.208 9.75408C10.5392 10.3508 9.45693 10.3493 8.78984 9.75106C8.49421 9.46967 7.98381 8.98308 7.35551 8.38184L2.22698 13.293ZM1.34768 2.63268V12.3673L6.43231 7.49923C4.72761 5.86898 2.70023 3.92796 1.34768 2.63268ZM13.5679 7.5L18.6525 12.3673V2.63268C17.0775 4.14071 15.2742 5.86674 13.5679 7.5ZM2.22696 1.70703C4.36288 3.75299 8.59945 7.80861 9.77347 8.92584V8.92659C9.83159 8.98317 9.91413 9.01637 10 9.01637C10.0851 9.01637 10.1668 8.98468 10.2241 8.92886C11.3914 7.81764 15.5278 3.85779 17.7732 1.70773L2.22696 1.70703Z" fill="#7F7F98"/>
                                        </svg>
                                    </span>

                                    <input
                                        id="easycommerce-email"
                                        type="email"
                                        placeholder={__("Enter a valid email address", "easycommerce")}
                                        name="email"
                                        required
                                        defaultValue=""
                                        onInput={(e) => {
                                            const icon = document.getElementById("email-icon");
                                            const input = e.target;
                                            if (input.value.trim() === "") {
                                                icon.classList.remove("opacity-0", "invisible");
                                                icon.classList.add("opacity-100", "visible");
                                                input.classList.remove("pl-4");
                                                input.classList.add("pl-11");
                                            } else {
                                                icon.classList.remove("opacity-100", "visible");
                                                icon.classList.add("opacity-0", "invisible");
                                                input.classList.remove("pl-11");
                                                input.classList.add("pl-4");
                                            }
                                        }}
                                        className="h-ec-input mt-3 pr-4 py-4 rounded-lg font-inter border border-ec-light-black placeholder-ec-light-black text-base font-normal hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-all duration-300 ease-in-out w-full pl-11"
                                    />
                                </div>
                            </label>
                            <label
                                htmlFor="easycommerce-password"
                                className="block mb-4"
                            >
                                <span className="text-ec-body block text-base font-medium font-inter leading-4 mb-1">
                                    {__("Enter Password", "easycommerce")}
                                </span>

                                <div className="relative">
                                    <span
                                        id="password-icon"
                                        className="absolute left-4 top-8 transform -translate-y-1/2 transition-opacity duration-300 text-gray-400"
                                    >
                                        <svg width="16" height="19" viewBox="0 0 16 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M13.5301 7.2525H12.918V5.09489C12.918 2.51776 10.8852 0.5 7.97814 0.5C5.07104 0.5 3.08197 2.55771 3.08197 5.09489V7.23252C3.08197 7.2525 3.08197 7.2525 3.08197 7.23252H2.46995C1.11475 7.23252 0.0218579 8.23141 0 9.49001V16.2425C0 17.4811 1.0929 18.48 2.46995 18.5H13.5301C14.8852 18.5 15.9781 17.5011 16 16.2425V9.49001C16 8.25139 14.8852 7.2525 13.5301 7.2525ZM4.30601 7.2525V5.09489C4.30601 3.17703 5.74863 1.61876 7.97814 1.61876C10.1858 1.61876 11.694 3.13707 11.694 5.09489V7.23252C11.694 7.2525 11.694 7.2525 11.694 7.23252H4.30601C4.30601 7.2525 4.30601 7.2525 4.30601 7.2525ZM14.7541 16.2425C14.7541 16.8618 14.2077 17.3613 13.5301 17.3613H2.46995C1.79235 17.3613 1.2459 16.8618 1.2459 16.2425V9.49001C1.2459 8.8707 1.79235 8.37125 2.46995 8.37125H13.5301C14.2077 8.37125 14.7541 8.8707 14.7541 9.49001V16.2425ZM9.22404 11.7475C9.22404 12.167 8.98361 12.5266 8.61202 12.7264V14.5644C8.61202 14.884 8.32787 15.1238 8 15.1238C7.65027 15.1238 7.38798 14.864 7.38798 14.5644V12.7264C7.01639 12.5266 6.77596 12.167 6.77596 11.7475C6.77596 11.1282 7.3224 10.6287 8 10.6287C8.6776 10.6088 9.22404 11.1082 9.22404 11.7475Z" fill="#7F7F98"/>
                                        </svg>
                                    </span>

                                    <input
                                        id="easycommerce-password"
                                        type="password"
                                        placeholder={__("Enter new password", "easycommerce")}
                                        name="password"
                                        required
                                        defaultValue=""
                                        onInput={(e) => {
                                            const icon = document.getElementById("password-icon");
                                            const input = e.target;
                                            if (input.value.trim() === "") {
                                                icon.classList.remove("opacity-0", "invisible");
                                                icon.classList.add("opacity-100", "visible");
                                                input.classList.remove("pl-4");
                                                input.classList.add("pl-11");
                                            } else {
                                                icon.classList.remove("opacity-100", "visible");
                                                icon.classList.add("opacity-0", "invisible");
                                                input.classList.remove("pl-11");
                                                input.classList.add("pl-4");
                                            }
                                        }}
                                       className="h-ec-input mt-3 pr-4 py-4 rounded-lg font-inter border border-ec-light-black placeholder-ec-light-black text-base font-normal hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-all duration-300 ease-in-out w-full pl-11"
                                    />
                                </div>
                            </label>
                            <label
                                htmlFor="easycommerce-password-again"
                                className="block mb-4"
                            >
                                <span className="text-ec-body block text-base font-medium font-inter leading-4 mb-1">
                                    {__("Re-enter Password", "easycommerce")}
                                </span>

                                <div className="relative">
                                    <span
                                        id="password-again-icon"
                                        className="absolute left-4 top-8 transform -translate-y-1/2 transition-opacity duration-300 text-gray-400"
                                    >
                                        <svg width="16" height="19" viewBox="0 0 16 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M13.5301 7.2525H12.918V5.09489C12.918 2.51776 10.8852 0.5 7.97814 0.5C5.07104 0.5 3.08197 2.55771 3.08197 5.09489V7.23252C3.08197 7.2525 3.08197 7.2525 3.08197 7.23252H2.46995C1.11475 7.23252 0.0218579 8.23141 0 9.49001V16.2425C0 17.4811 1.0929 18.48 2.46995 18.5H13.5301C14.8852 18.5 15.9781 17.5011 16 16.2425V9.49001C16 8.25139 14.8852 7.2525 13.5301 7.2525ZM4.30601 7.2525V5.09489C4.30601 3.17703 5.74863 1.61876 7.97814 1.61876C10.1858 1.61876 11.694 3.13707 11.694 5.09489V7.23252C11.694 7.2525 11.694 7.2525 11.694 7.23252H4.30601C4.30601 7.2525 4.30601 7.2525 4.30601 7.2525ZM14.7541 16.2425C14.7541 16.8618 14.2077 17.3613 13.5301 17.3613H2.46995C1.79235 17.3613 1.2459 16.8618 1.2459 16.2425V9.49001C1.2459 8.8707 1.79235 8.37125 2.46995 8.37125H13.5301C14.2077 8.37125 14.7541 8.8707 14.7541 9.49001V16.2425ZM9.22404 11.7475C9.22404 12.167 8.98361 12.5266 8.61202 12.7264V14.5644C8.61202 14.884 8.32787 15.1238 8 15.1238C7.65027 15.1238 7.38798 14.864 7.38798 14.5644V12.7264C7.01639 12.5266 6.77596 12.167 6.77596 11.7475C6.77596 11.1282 7.3224 10.6287 8 10.6287C8.6776 10.6088 9.22404 11.1082 9.22404 11.7475Z" fill="#7F7F98"/>
                                        </svg>
                                    </span>

                                    <input
                                        id="easycommerce-password-again"
                                        type="password"
                                        placeholder={__("Re-ender password to confirm", "easycommerce")}
                                        name="password_again"
                                        required
                                        defaultValue=""
                                        onInput={(e) => {
                                            const icon = document.getElementById("password-again-icon");
                                            const input = e.target;
                                            if (input.value.trim() === "") {
                                                icon.classList.remove("opacity-0", "invisible");
                                                icon.classList.add("opacity-100", "visible");
                                                input.classList.remove("pl-4");
                                                input.classList.add("pl-11");
                                            } else {
                                                icon.classList.remove("opacity-100", "visible");
                                                icon.classList.add("opacity-0", "invisible");
                                                input.classList.remove("pl-11");
                                                input.classList.add("pl-4");
                                            }
                                        }}
                                      className="h-ec-input mt-3 pr-4 py-4 rounded-lg font-inter border border-ec-light-black placeholder-ec-light-black text-base font-normal hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-all duration-300 ease-in-out w-full pl-11"
                                    />
                                </div>
                            </label>
                            {passwordError && (
                                <p className="text-ec-red text-sm mb-3">
                                    {passwordError}
                                </p>
                            )}
                            <div className="flex items-center justify-between gap-2 pt-2">
                                <button
                                    onClick={() => setIsAddNew(false)}
                                    className="w-1/2 flex justify-center items-center gap-[8px] font-inter bg-white group border border-ec-primary py-[10px] px-4 rounded-lg text-ec-primary focus:shadow-none text-base font-normal"
                                >
                                    {__("Cancel", "easycommerce")}
                                </button>
                                <button
                                    class="easycommerce-primary-button w-1/2 py-[11px] font-inter text-base font-normal"
                                >
                                    {__("Submit", "easycommerce")}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
};

export default AddNewCustomer;
