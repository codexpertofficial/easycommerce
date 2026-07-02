import React from "react";

// Components
import RefundModal from "./RefundModal";
import SendEmail from "./SendEmail";
// import ChangeStatus from "./ChangeStatus";
// import FulfillmentStatus from "./FulfillmentStatus";

const Modal = ({ modalName, hideModal, order, updateOrder }) => {
    return (
        <>
            {modalName === "refund" && (
                <RefundModal
                    hideModal={hideModal}
                    order={order}
                    updateOrder={updateOrder}
                />
            )}

            {modalName === "email" && (
                <SendEmail hideModal={hideModal} order={order} />
            )}

            {/* {modalName === "status" && (
                <ChangeStatus
                    hideModal={hideModal}
                    order={order}
                    updateOrder={updateOrder}
                />
            )}

            {modalName === "fulfillment" && (
                <FulfillmentStatus
                    hideModal={hideModal}
                    order={order}
                    updateOrder={updateOrder}
                />
            )} */}
        </>
    );
};

export default Modal;
