import React, { useState } from "react";
import { useSelector } from "react-redux";
import Header from "../Header";
import SideBar from "./Sidebar";

const MainLayout = ({ breadcrumb, children }) => {
    const [showAPIModal, setShowAPIModal] = useState(false);
    const [user, setUser] = useState(null);
    
    return (
        <>
            <Header
                breadcrumb={breadcrumb}
                showAPIModal={showAPIModal}
                setShowAPIModal={setShowAPIModal}
                user={user}
                setUser={setUser}
            />
            <div className="flex">
                <SideBar
                    showAPIModal={showAPIModal}
                    setShowAPIModal={setShowAPIModal}
                    user={user}
                />
                <div className="flex-1 px-6 py-4 font-inter w-[calc(100%-240px)]">{children}</div>
            </div>
        </>
    );
};

export default MainLayout;