import { useState } from "react";
import { toast, Bounce } from "react-toastify";

//Icon
const crossIcon = `${EASYCOMMERCE.assets}admin/img/icons/cross.png`;

//Images
const modalImage = `${EASYCOMMERCE.assets}admin/img/icons/modal.png`;
const modalImageGif = `${EASYCOMMERCE.assets}admin/img/icons/modal.gif`;

const AddonModal = ({ setShowModal }) => {
    const [showMessage, setShowMessage] = useState(false);

    const removeModal = () => {
        setShowModal(false);
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);

        easycommerce_modal(true);

        fetch(`${EASYCOMMERCE.rest_base}/connectivity/requests`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify(data),
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);
                if (data.success && data.data?.url) {
                    setShowMessage(true);
                } else {
                    toast.error(data.data.message, {
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
                className={`absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[532px] h-auto rounded-lg p-10 bg-white 
                    shadow-search-box-shadow`}
            >
                <button
                    onClick={removeModal}
                    className="absolute top-[7px] right-[7px] flex items-center justify-center w-6 h-6
					bg-[#F8F8F8] border border-ec-border rounded-full"
                >
                    <img src={crossIcon} />
                </button>
                <div>
                    {!showMessage && (
                        <>
                            <h2 className="text-center font-inter text-2xl font-semibold mb-2 text-[#120350]">
                            Looking for More Addons?
                            </h2>
                            <p className="max-w-[344px] mx-auto text-center text-ec-placeholder font-inter text-base leading-[26px]">
                            Tell us what’s missing. We’re here to make it happen for you.
                            </p>
                            <form
                                className="easycommerce-modal-form w-full mt-5"
                                onSubmit={handleSubmit}
                            >
                                <p className="mb-3">
                                    <label
                                        className="block mb-2 font-inter text-base leading-[26px] text-ec-body"
                                        htmlFor="easycommerce-feedback-modal-name"
                                    >
                                        Name
                                    </label>
                                    <input
                                        type="text"
                                        name="name"
                                        id="easycommerce-feedback-modal-name"
                                        className="w-full text-base font-inter p-[11px] focus:outline-none"
                                        placeholder="Enter your name"
                                        required
                                    />
                                </p>
                                <p className="mb-3">
                                    <label
                                        className="block mb-2 font-inter text-base leading-[26px] text-ec-body"
                                        htmlFor="easycommerce-feedback-modal-email"
                                    >
                                        Email
                                    </label>
                                    <input
                                        type="email"
                                        name="email"
                                        id="easycommerce-feedback-modal-email"
                                        className="w-full text-base font-inter p-[11px] focus:outline-none"
                                        placeholder="Enter your email"
                                        required
                                    />
                                </p>
                                <p className="mb-3">
                                    <label
                                        className="block mb-2 font-inter text-base leading-[26px] text-ec-body"
                                        htmlFor="easycommerce-feedback-modal-subject"
                                    >
                                        Subject
                                    </label>
                                    <input
                                        type="text"
                                        name="subject"
                                        id="easycommerce-feedback-modal-subject"
                                        className="w-full text-base font-inter p-[11px] focus:outline-none"
                                        placeholder="Enter a subject"
                                        required
                                    />
                                </p>
                                <p className="mb-3">
                                    <label
                                        className="block mb-2 text-base leading-[26px] text-ec-body font-inter"
                                        htmlFor="easycommerce-feedback-modal-message"
                                    >
                                        Message
                                    </label>
                                    <textarea
                                        className="w-full font-inter h-[114px] p-[11px] focus:outline-none text-base"
                                        id="easycommerce-feedback-modal-message"
                                        placeholder="Write your mesage"
                                        name="message"
                                    ></textarea>
                                </p>
                                <p>
                                    <button
                                        type="submit"
                                        className="block w-full font-inter p-[10px] rounded-md text-base leading-[26px]
										text-white font-medium bg-ec-primary"
                                    >
                                        Send Message
                                    </button>
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
                                <h4 className="text-center my-4 text-ec-body font-inter text-base">
                                Thanks for your request! We'll get in touch if we have any questions about your addon request. Cheers! 
                                </h4>
                                <button
                                    onClick={removeModal}
                                    className="block w-1/3 font-inter p-[10px] mt-3 rounded-md text-base leading-[26px] text-white font-medium bg-ec-primary"
                                >
                                    Close
                                </button>
                            </div>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
};
export default AddonModal;
