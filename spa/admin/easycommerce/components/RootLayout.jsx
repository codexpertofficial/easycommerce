import { Outlet } from "react-router-dom";
import { ToastContainer } from "react-toastify";

const RootLayout = () => {
    return (
        <div className="min-h-screen bg-ec-main-bg">
            <Outlet />

            <ToastContainer />
        </div>
    );
};

export default RootLayout;
