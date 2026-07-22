import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import Modal from "./Modal";

const Feedback = () => {
    const [showModal, setShowModal] = useState(false);

    const handleModal = () => {
        setShowModal(true);
    };

    return (
        <>
            <button
                onClick={handleModal}
                className="flex items-center cursor-pointer p-2 font-inter 
                font-medium text-sm leading-5 ec-body hover:text-ec-primary"
            >
                { __( 'Feedback', 'easycommerce' ) }
            </button>
            {showModal && <Modal setShowModal={setShowModal} />}
        </>
    );
};

export default Feedback;