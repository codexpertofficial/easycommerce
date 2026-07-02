import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import { toast } from "react-toastify";
import Dropdown from '../../../../../../common/components/inputs/Dropdown';

const cross = `${EASYCOMMERCE.assets}admin/img/icons/cross.png`;
const SendEmailTop = `${EASYCOMMERCE.assets}admin/img/icons/SendEmail.png`;

const options = [];
const emailEvents = EASYCOMMERCE.email_events;

Object.keys(emailEvents).map((key) => {
    options.push({ value: key, label: key.charAt(0).toUpperCase() + key.slice(1) });
});

const SendEmail = ({ hideModal, order }) => {

   const [emailEvent, setEmailEvent] = useState(null);

    const handleSendEmail = () => {

        easycommerce_modal(true);

        fetch(`${EASYCOMMERCE.rest_base}/orders/${order.id}/email`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify({
                event: emailEvent.value,
            }),
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success) {
                    toast.success("Email sent");
                } else {
                    toast.error(data.data?.message);
                }
            });
    };

    return (
        <>
            <div className="w-full top-0 left-0 h-lvh bg-[#00000082] fixed">
                <div
                    className="absolute top-1/2 left-1/2 bg-white w-[530px] 
                    min-h-[350px] -translate-y-1/2 -translate-x-1/2 rounded-xl"
                >
                    <button
                        className="group absolute w-[24px] h-[24px] top-[-20px] right-[-20px] bg-white rounded-full hover:bg-[#FF3A52] flex items-center justify-center transition-colors duration-200"
                        onClick={hideModal}
                    >
                        <img src={cross} className="w-[9px] h-auto" />
                    </button>
                    <div className="p-10">
                        <div className="text-center">
                            <img src={SendEmailTop} className="mx-auto  mb-5" />
                            <h2 className="text-ec-body font-semibold text-2xl leading-8 font-inter">
                                Resend Order Email
                            </h2>
                        </div>
                        <div className="mb-4 flex flex-col">
                            <label
                                htmlFor="emailEvent"
                                className="text-ec-body inline-block font-inter font-medium text-base leading-[26px] mb-2"
                            >
                                {__("Email Event", "easycommerce")}
                            </label>
                            <div className="h-[48px]">
                                <Dropdown
                                    placeholder={__('Select an event', 'easycommerce')}
                                    options={options}
                                    value={emailEvent?.value || ""}
                                    onChange={(selected) => {
                                        setEmailEvent(selected);
                                    }}
                                />
                            </div>
                        </div>
                        <div className="mt-10 flex items-center justify-between gap-4">
                            <button
                                className="p-[10px] rounded-lg text-ec-primary border border-ec-primary font-inter font-medium 
                                text-base leading-[26px] w-[224px] bg-white hover:bg-ec-primary hover:text-white transition-all 
								ease-in-out duration-500"
                                onClick={hideModal}
                            >
                                Cancel
                            </button>
                            <button
                                onClick={handleSendEmail}
                                className="p-[10px] rounded-lg text-white border border-ec-primary font-inter font-medium text-base
                                leading-[26px] w-[224px] bg-ec-primary hover:bg-ec-secondary transition-all ease-in-out duration-500"
                            >
                                Send
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

export default SendEmail;
