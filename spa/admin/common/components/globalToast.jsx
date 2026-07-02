import { toast, Bounce } from "react-toastify";

const toastStyle = {
    margin: "30px 0 0 0",
    fontSize: "16px",
    fontWeight: "500",
    lineHeight: "26px",
    color: "#fff",
};

const globalToast = () => {
    const addToastData = ({ type = "success", message = "" }) => {
        toast[type](message, {
            position: "top-right",
            style: toastStyle,
            autoClose: 1000,
            hideProgressBar: false,
            closeOnClick: true,
            pauseOnHover: true,
            draggable: false,
            progress: undefined,
            theme: "colored",
            transition: Bounce,
        });
    };

    return { addToastData };
};

export default globalToast;
