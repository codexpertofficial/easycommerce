import { useState } from "react";
import { toast, Bounce } from "react-toastify";
import { __ } from "@wordpress/i18n";


//Icon
const crossIcon = `${EASYCOMMERCE.assets}admin/img/icons/cross.png`;

//Images
const modalImage = `${EASYCOMMERCE.assets}admin/img/icons/modal.png`;
const modalImageGif = `${EASYCOMMERCE.assets}admin/img/icons/modal.gif`;

const Modal = ({ setShowModal }) => {
    const [showMessage, setShowMessage] = useState(false);

    const removeModal = () => {
        setShowModal(false);
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);

        easycommerce_modal(true);

        fetch(`${EASYCOMMERCE.rest_base}/connectivity/feedback`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify({ ...data, event: "feedback" }),
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);
                // Not gated on data.data.url: that is the GitHub issue URL, and the
                // hub no longer opens an issue for every kind of submission.
                if (data.success) {
                    setShowMessage(true);
                } else {
                    toast.error(data?.data?.message || __("Something went wrong.", "easycommerce"), {
                        position: "top-right",
                        style: {
                            fontSize: "16px",
                            fontWeight: "500",
                            lineHeight: "26px",
                            color: "#fff",
                        },
                        autoClose: 1500,
                        hideProgressBar: false,
                        closeOnClick: true,
                        pauseOnHover: true,
                        draggable: false,
                        progress: undefined,
                        theme: "colored",
                        transition: Bounce,
                    });
                }
            });
    };

    return (
        <div className="fixed top-0 left-0 w-full h-full bg-[#0000002B] z-10">
            <div
                className={`absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[537px] ${
                    showMessage ? "h-auto" : "h-auto"
                }  rounded-[22px] px-[46px] py-[32px] bg-white shadow-search-box-shadow`}
            >
                <button
                    onClick={removeModal}
                    className="absolute top-[-15px] left-[530px] flex items-center justify-center w-6 h-6
					bg-[#FFFFFF] border border-ec-border rounded-full"
                >
                    <img src={crossIcon} />
                </button>
                <div>
                    {!showMessage && (
                        <>
                            <h2 className="text-center font-inter text-2xl font-medium mb-2 text-ec-title">
                                { __( 'Your Voice Matters', 'easycommerce' ) }
                            </h2>
                            <p className="max-w-[344px] mx-auto text-center text-ec-body font-inter font-normal text-sm leading-[20px]">
                                { __( 'Help us make EasyCommerce the best it can be. Share your feedback and ideas with us!', 'easycommerce' ) }
                            </p>
                            <form
                                className="w-full mt-5"
                                onSubmit={handleSubmit}
                            >
                                <p className="mb-3">
                                    <label
                                        className="block font-inter font-medium text-base leading-[26px] text-ec-body"
                                        htmlFor="easycommerce-feedback-modal-name"
                                    >
                                        { __( 'First Name', 'easycommerce' ) }
                                    </label>
                                    <div className="relative">
                                        <span
                                            id="name-icon"
                                        className="absolute left-4 top-8 transform -translate-y-1/2 transition-opacity duration-200 text-gray-400"
                                        >
                                            <svg width="14" height="18" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.94482 1.79992C5.38897 1.79992 4.12805 3.06084 4.12805 4.61669C4.12805 6.17173 5.38897 7.43263 6.94482 7.43263C8.50067 7.43263 9.76159 6.17171 9.76159 4.61669C9.76159 3.06084 8.50067 1.79992 6.94482 1.79992ZM2.82812 4.61669C2.82812 2.34256 4.67158 0.5 6.94482 0.5C9.21806 0.5 11.0615 2.34264 11.0615 4.61669C11.0615 6.89 9.21806 8.73256 6.94482 8.73256C4.67158 8.73256 2.82812 6.88991 2.82812 4.61669Z" fill="#7F7F98"/>
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M1.55492 16.532H12.3312C12.0476 13.3009 9.67516 10.8993 6.94304 10.8993C4.21091 10.8993 1.8384 13.3009 1.55492 16.532ZM0.226562 17.182C0.226562 13.0784 3.15461 9.59937 6.9431 9.59937C10.7316 9.59937 13.6596 13.0784 13.6596 17.182C13.6596 17.5411 13.368 17.8319 13.0097 17.8319H0.876523C0.518223 17.8319 0.226562 17.5411 0.226562 17.182Z" fill="#7F7F98"/>
                                            </svg>
                                        </span>
                                        <input
                                            type="text"
                                            name="name"
                                            id="easycommerce-feedback-modal-name"
                                            placeholder={ __( 'Enter first name', 'easycommerce' ) }
                                            required
                                            defaultValue=""
                                            onInput={(e) => {
                                                const icon = document.getElementById("name-icon");
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
                                            className="mt-3 pr-4 py-4 rounded-xl font-inter border border-ec-light-black placeholder-ec-light-black text-base font-normal hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-all duration-300 ease-in-out w-full pl-11 h-ec-input"
                                        />
                                    </div>
                                </p>
                                <p className="mb-3">
                                    <label
                                        className="block font-inter font-medium text-base leading-[26px] text-ec-body"
                                        htmlFor="easycommerce-feedback-modal-email"
                                    >
                                        { __( 'Email Address', 'easycommerce' ) }
                                    </label>
                                    <div className="relative">
                                        <span
                                            id="mail-icon"
                                            className="absolute left-4 top-8 transform -translate-y-1/2 transition-opacity duration-300 text-gray-400"
                                        >
                                            <svg width="18" height="15" viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M18.0822 14.5H1.91779C0.859074 14.4985 0.0016818 13.7305 0 12.7823V2.21775C0.00168438 1.26947 0.859096 0.501506 1.91779 0.5H18.0822C19.1409 0.501509 19.9983 1.26949 20 2.21775V12.7823C19.9983 13.7305 19.1409 14.4985 18.0822 14.5ZM2.22698 13.293H17.7732L12.6448 8.38258C12.0115 8.9876 11.5002 9.47646 11.208 9.75408C10.5392 10.3508 9.45693 10.3493 8.78984 9.75106C8.49421 9.46967 7.98381 8.98308 7.35551 8.38184L2.22698 13.293ZM1.34768 2.63268V12.3673L6.43231 7.49923C4.72761 5.86898 2.70023 3.92796 1.34768 2.63268ZM13.5679 7.5L18.6525 12.3673V2.63268C17.0775 4.14071 15.2742 5.86674 13.5679 7.5ZM2.22696 1.70703C4.36288 3.75299 8.59945 7.80861 9.77347 8.92584V8.92659C9.83159 8.98317 9.91413 9.01637 10 9.01637C10.0851 9.01637 10.1668 8.98468 10.2241 8.92886C11.3914 7.81764 15.5278 3.85779 17.7732 1.70773L2.22696 1.70703Z" fill="#7F7F98"/>
                                            </svg>
                                        </span>

                                        <input
                                            type="email"
                                            name="email"
                                            id="easycommerce-feedback-modal-email"
                                            placeholder={ __( 'Enter a valid email address', 'easycommerce' ) }
                                            required
                                            defaultValue=""
                                            onInput={(e) => {
                                                const icon = document.getElementById("mail-icon");
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
                                            className="mt-3 pr-4 py-4 rounded-xl font-inter border border-ec-light-black placeholder-ec-light-black text-base font-normal hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-all duration-300 ease-in-out w-full pl-11 h-ec-input"
                                        />
                                    </div>
                                </p>
                                <p className="mb-3">
                                    <label
                                        className="block font-inter font-medium text-base leading-[26px] text-ec-body"
                                        htmlFor="easycommerce-feedback-modal-subject"
                                    >
                                        { __( 'Subject', 'easycommerce' ) }
                                    </label>
                                    <div className="relative">
                                        <span
                                            id="subject-icon"
                                            className="absolute left-4 top-8 transform -translate-y-1/2 transition-opacity duration-300 text-gray-400"
                                        >
                                            <svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M17.8911 2.25585H17.6088V1.9736C17.6088 0.88586 16.723 0 15.6352 0H2.66786C1.58117 0.00105702 0.695312 0.886888 0.695312 1.97465V14.4262C0.695312 14.8839 0.968039 15.293 1.39088 15.4675C1.53147 15.5256 1.6784 15.5541 1.82324 15.5541C2.11604 15.5541 2.40252 15.44 2.61923 15.2233L3.16046 14.682C3.48392 15.3343 4.14883 15.7867 4.92368 15.7867H16.2473L17.9387 17.478C18.1554 17.6947 18.4418 17.8089 18.7346 17.8089C18.8805 17.8089 19.0275 17.7804 19.167 17.7222C19.5909 17.5478 19.8647 17.1387 19.8647 16.681V4.22942C19.8647 3.14168 18.9788 2.25585 17.8911 2.25585ZM1.82326 14.4262V1.97468C1.82326 1.50849 2.20275 1.12901 2.66894 1.12901H15.6363C16.1025 1.12901 16.482 1.50849 16.482 1.97468V11.5593C16.482 12.0255 16.1025 12.405 15.6363 12.405L4.31157 12.404C4.01452 12.404 3.72382 12.5245 3.51453 12.7348L3.11599 13.1334L1.82326 14.4262ZM18.7367 16.681L17.0454 14.9897C16.8361 14.7804 16.5454 14.6598 16.2483 14.6598H4.92362C4.45744 14.6598 4.07795 14.2803 4.07795 13.8142V13.7655L4.31157 13.5319H15.6363C16.724 13.5319 17.6099 12.6461 17.6099 11.5583L17.6088 3.38384H17.8911C18.3573 3.38384 18.7367 3.76332 18.7367 4.22951L18.7367 16.681Z" fill="#7F7F98"/>
                                                <path d="M7.45299 6.20304C7.76483 6.20304 8.01642 5.95039 8.01642 5.63962C8.01642 5.01804 8.52276 4.51169 9.14434 4.51169C9.76592 4.51169 10.2723 5.01804 10.2723 5.63962C10.2723 6.26119 9.76592 6.76754 9.14434 6.76754C8.83251 6.76754 8.58092 7.02019 8.58092 7.33096V7.89439C8.58092 8.20622 8.83356 8.45781 9.14434 8.45781C9.45617 8.45781 9.70776 8.20516 9.70776 7.89439V7.82251C10.6803 7.57198 11.4002 6.68825 11.4002 5.63856C11.4002 4.39435 10.3885 3.38379 9.1454 3.38379C7.90119 3.38379 6.89063 4.39543 6.89063 5.63856C6.88957 5.95039 7.14222 6.20304 7.45299 6.20304Z" fill="#7F7F98"/>
                                                <path d="M9.70605 9.58578C9.70605 10.3374 8.57812 10.3374 8.57812 9.58578C8.57812 8.8342 9.70605 8.8342 9.70605 9.58578Z" fill="#7F7F98"/>
                                            </svg>
                                        </span>

                                        <input
                                            type="text"
                                            name="subject"
                                            id="easycommerce-feedback-modal-subject"
                                            placeholder={ __( 'Write here', 'easycommerce' ) }
                                            required
                                            defaultValue=""
                                            onInput={(e) => {
                                                const icon = document.getElementById("subject-icon");
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
                                            className="mt-3 pr-4 py-4 rounded-xl font-inter border border-ec-light-black placeholder-ec-light-black text-base font-normal hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-all duration-300 ease-in-out w-full pl-11 h-ec-input"
                                        />
                                    </div>
                                </p>
                                <p className="mb-3">
                                    <label
                                        className="block text-base font-medium leading-[26px] text-ec-body font-inter"
                                        htmlFor="easycommerce-feedback-modal-message"
                                    >
                                        { __( 'Message', 'easycommerce' ) }
                                    </label>
                                    <div className="relative">
                                        <span
                                            id="message-icon"
                                            className="absolute left-4 top-8 transform -translate-y-1/2 transition-opacity duration-300 text-gray-400"
                                        >
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8.87296 8.97656C8.54751 8.97656 8.28125 9.24282 8.28125 9.56827C8.28125 9.89372 8.54751 10.16 8.87296 10.16H15.0858C15.4113 10.16 15.6775 9.89372 15.6775 9.56827C15.6775 9.24282 15.4113 8.97656 15.0858 8.97656H8.87296Z" fill="#7F7F98"/>
                                                <path d="M15.0903 11.7339H5.62296C5.29751 11.7339 5.03125 12.0001 5.03125 12.3256C5.03125 12.651 5.29751 12.9173 5.62296 12.9173H15.0903C15.4158 12.9173 15.682 12.651 15.682 12.3256C15.682 12.0001 15.4158 11.7339 15.0903 11.7339Z" fill="#7F7F98"/>
                                                <path d="M2.66202 12.9173H3.25373C3.57918 12.9173 3.84544 12.651 3.84544 12.3256C3.84544 12.0001 3.57918 11.7339 3.25373 11.7339H2.66202C2.33657 11.7339 2.07031 12.0001 2.07031 12.3256C2.07031 12.651 2.33657 12.9173 2.66202 12.9173Z" fill="#7F7F98"/>
                                                <path d="M15.0879 14.4973H2.66202C2.33657 14.4973 2.07031 14.7636 2.07031 15.089C2.07031 15.4145 2.33657 15.6807 2.66202 15.6807H15.0879C15.4134 15.6807 15.6796 15.4145 15.6796 15.089C15.6796 14.7636 15.4134 14.4973 15.0879 14.4973Z" fill="#7F7F98"/>
                                                <path d="M17.1597 4.14226H13.9644L15.3431 3.17776C15.6686 2.95291 15.8816 2.60972 15.9526 2.22512C16.0236 1.83459 15.9348 1.44406 15.71 1.11861L15.3727 0.633406C15.1479 0.307956 14.8047 0.0949401 14.4201 0.0239349C14.0296 -0.0470703 13.639 0.0416861 13.3136 0.266527L7.78104 4.14228H0.59171C0.26626 4.14228 0 4.40854 0 4.73399V17.1599C0 17.4853 0.26626 17.7516 0.59171 17.7516H17.1596C17.485 17.7516 17.7513 17.4853 17.7513 17.1599V4.73399C17.7513 4.40854 17.4851 4.14226 17.1597 4.14226ZM13.9881 1.23691C14.0769 1.17774 14.1656 1.17774 14.207 1.18958C14.2544 1.19549 14.3372 1.22508 14.3964 1.31383L14.7336 1.79904C14.7928 1.88779 14.7928 1.97655 14.781 2.01796C14.7751 2.0653 14.7455 2.14814 14.6567 2.20731L13.9289 2.71618L13.2484 1.74577L13.9762 1.2369L13.9881 1.23691ZM12.9704 3.39661L6.10066 8.20716C5.97048 8.29592 5.81073 8.33142 5.65686 8.30775L5.07106 8.20716L5.17165 7.62136C5.20124 7.46752 5.28408 7.33143 5.41424 7.23676L12.2839 2.4262L12.9644 3.39661H12.9704ZM16.568 16.568H1.1835V5.32549H6.08873L4.74553 6.26632C4.35501 6.5385 4.10058 6.94679 4.01772 7.41423L3.85204 8.3373L3.08282 8.87577C2.81656 9.06511 2.75146 9.43197 2.93489 9.69825C3.04732 9.86393 3.23075 9.9527 3.42009 9.9527C3.53844 9.9527 3.65678 9.9172 3.75736 9.84619L4.52658 9.30772L5.44965 9.4734C5.55616 9.49116 5.65676 9.50299 5.76325 9.50299C6.12418 9.50299 6.47921 9.39056 6.78099 9.18347L12.2779 5.33744H16.5678V16.5799L16.568 16.568Z" fill="#7F7F98"/>
                                            </svg>
                                        </span>

                                        <textarea
                                            id="easycommerce-feedback-modal-message"
                                            placeholder={ __( 'Write here', 'easycommerce' ) }
                                            name="message"
                                            defaultValue=""
                                            onInput={(e) => {
                                                const icon = document.getElementById("message-icon");
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
                                            className="mt-3 pr-4 py-4 rounded-xl font-inter border border-ec-light-black placeholder-ec-light-black text-base font-normal hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-all duration-300 ease-in-out w-full pl-11 h-[102px]"
                                        ></textarea>
                                    </div>   
                                </p>
                                <p>
                                    <div className="flex items-center justify-between gap-2 pt-2">
                                        <button
                                            onClick={removeModal}
                                            className="w-1/2 flex justify-center items-center gap-[8px] font-inter bg-white group border border-ec-primary py-[10px] px-4 rounded-xl text-ec-primary focus:shadow-none text-base font-normal"
                                        >
                                            { __( 'Cancel', 'easycommerce' ) }
                                        </button>
                                        <button
                                            type="submit"
                                            className="easycommerce-primary-button w-1/2 py-[11px] font-inter text-base font-normal"
                                        >
                                            { __( 'Send Message', 'easycommerce' ) }
                                        </button>
                                    </div>
                                </p>
                            </form>
                        </>
                    )}

                    {showMessage && (
                        <>
                            <div className="flex flex-col items-center justify-center">
                                <img
                                    className="mx-auto w-[135px] h-[108px] mb-3"
                                    src={modalImageGif}
                                    alt="Modal"
                                />
                                <h4 className="text-center mt-4 text-ec-title font-inter font-medium text-2xl leading-[34px]">
                                    { __( 'Thank you for your feedback', 'easycommerce' ) }
                                </h4>
                                <p className="w-[286px] text-center text-ec-body font-inter font-normal text-sm leading-5">
                                    { __( 'We will use your feedback to build a better experience for everyone.', 'easycommerce' ) }
                                </p>
                                <button
                                    onClick={removeModal}
                                    className="block w-[98px] h-[47px] easycommerce-primary-button font-inter text-base font-normal my-[16px]"
                                >
                                    { __( 'Close', 'easycommerce' ) }
                                </button>
                            </div>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
};
export default Modal;
