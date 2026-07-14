import React from "react";
import {createRoot} from "react-dom/client";
import {createHashRouter, Outlet, RouterProvider, useLocation, useParams} from "react-router-dom";
import {applyFilters} from "@wordpress/hooks";
import {SlotFillProvider} from '@wordpress/components';
import domReady from '@wordpress/dom-ready';
import {PluginArea} from '@wordpress/plugins';

// style
import "./assets/style.css";
import {ToastContainer} from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

// Components
import Sidebar from "./components/Sidebar";
import Dashboard from "./components/tabs/Dashboard";
import Orders from "./components/tabs/Orders";
import Transactions from "./components/tabs/Transactions";
import Downloads from "./components/tabs/Downloads";
import Profile from "./components/tabs/Profile";
import Address from "./components/tabs/Address";
import Password from "./components/tabs/Password";
import SingleOrder from "./components/tabs/Orders/SingleOrder";
import {convertRegexToRouteParam} from "./utils";

const App = () => {
    const location = useLocation();

    const activeTab = location.pathname.substring(1);

    return (
        <>
            <div className="w-full flex flex-col gap-[50px] py-10">
                <div className="w-full flex easycommerce-sidebar ec-db-md:flex-row flex-col bg-white border border-ec-border rounded-2xl shadow-[0_10px_40px_-12px_rgba(18,3,80,0.12)] overflow-hidden">
                    <Sidebar activeTab={activeTab || "dashboard"}/>

                    <div className="easycommerce-dashboard-content-details ec-db-md:w-[calc(100%_-_268px)] w-full p-4 sm:p-8 lg:p-10 bg-[#FCFCFE]">
                        <Outlet/>
                    </div>
                </div>
            </div>

            <ToastContainer/>
        </>
    );
};

domReady(() => {
    const defaultTabContentList = [
        {id: "dashboard", component: <Dashboard/>},
        {id: "orders", component: <Orders/>},
        {id: "transactions", component: <Transactions/>},
        {id: "downloads", component: <Downloads/>},
        {id: "profile", component: <Profile/>},
        {id: "address", component: <Address/>},
        {id: "password", component: <Password/>},
    ];

    const tabContentList = applyFilters(
        "easycommerce_dashboard_tab_content_list",
        defaultTabContentList
    );

    const singleViewRoutes = applyFilters("easycommerce_dashboard_single_views", [
        {
            match: /^orders\/(\d+)$/,
            component: (id) => <SingleOrder orderId={id}/>,
        },
    ]);

    const router = createHashRouter([{
        path: "/",
        element: <App/>,
        children: [
            ...tabContentList.map((item) => {
                if (item.id === 'dashboard') {
                    return ({
                        index: true,
                        element: item.component,
                    })
                }

                return ({
                    path: `/${item.id}`,
                    element: item.component,
                });
            }),
            ...singleViewRoutes.map((route) => {
                const RenderSingleComponent = () => {
                    const { id } = useParams();

                    return route.component(id)
                }

                return ({
                    path: convertRegexToRouteParam(route.match),
                    element: <RenderSingleComponent/>,
                });
            })
        ],
    }]);

    const container = document.getElementById("easycommerce_dashboard_render");
    if (!container) {
        return;
    }

    const root = createRoot(container);

    root.render(
        <SlotFillProvider>
            <PluginArea scope="easycommerce-dashboard"/>
            <RouterProvider router={router}/>
        </SlotFillProvider>
    );

})
