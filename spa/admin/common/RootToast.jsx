
import React from "react";
import { ToastContainer, Bounce } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

const RootToast = () => {
    return (
        <ToastContainer
            position="top-right"
            autoClose={1000}
            hideProgressBar={false}
            closeOnClick
            pauseOnHover
            draggable={false}
            theme="colored"
            transition={Bounce}
            toastStyle={{
                margin: "30px 0 0 0",
                fontSize: "16px",
                fontWeight: "500",
                lineHeight: "26px",
                color: "#fff",
            }}
        />
    );
};

export default RootToast;
