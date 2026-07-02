import React, { useState, useEffect } from "react";
import {applyFilters} from "@wordpress/hooks";
import {Link} from "react-router-dom";
import {twMerge} from "tailwind-merge";

const dashboardActiveIcon = `${EASYCOMMERCE.assets}public/img/icons/dashboard-active-icon.png`;
const dashboardDefaultIcon = `${EASYCOMMERCE.assets}public/img/icons/dashboard-default-icon.png`;
const defaultArrowDownIcon = `${EASYCOMMERCE.assets}public/img/icons/dashboard-button-arrow-down-default-icon.png`;
const activeArrowDownIcon = `${EASYCOMMERCE.assets}public/img/icons/dashboard-button-arrow-down-active-icon.png`;
const defaultOrdersIcon = `${EASYCOMMERCE.assets}public/img/icons/my-orders-default.png`;
const activeOrdersIcon = `${EASYCOMMERCE.assets}public/img/icons/my-orders-active.png`;
const defaultSettingsIcon = `${EASYCOMMERCE.assets}public/img/icons/settings-default.png`;
const activeSettingsIcon = `${EASYCOMMERCE.assets}public/img/icons/settings-active.png`;
const defaultLogoutIcon = `${EASYCOMMERCE.assets}public/img/icons/logout-default.png`;
const dashboardDefaultUuser = `${EASYCOMMERCE.assets}public/img/icons/dashboard-default-user.png`;

const sidemenuList = [
    {
        id: "dashboard",
        icon: dashboardDefaultIcon,
        activeIcon: dashboardActiveIcon,
        title: "Dashboard",
        subMenu: false,
        subMenuList: [],
    },
    {
        id: "orders",
        icon: defaultOrdersIcon,
        activeIcon: activeOrdersIcon,
        title: "Purchases",
        subMenu: true,
        subMenuList: [
            { id: "orders", label: "Orders" },
            { id: "transactions", label: "Transactions" },
            { id: "downloads", label: "Downloads" },
        ],
    },
    {
        id: "profile",
        icon: defaultSettingsIcon,
        activeIcon: activeSettingsIcon,
        title: "Settings",
        subMenu: true,
        subMenuList: [
            { id: "profile", label: "Profile" },
            { id: "address", label: "Address" },
            { id: "password", label: "Password" },
        ],
    },
    {
        id: "logout",
        icon: defaultLogoutIcon,
        activeIcon: defaultLogoutIcon,
        title: "Logout",
        subMenu: false,
        subMenuList: [],
    },
];


const Sidebar = ({ activeTab }) => {
    const filteredSidemenuList = applyFilters(
        "easycommerce_dashboard_sidebar_menu_list",
        sidemenuList
    );

    const [userInfo, setUserInfo] = useState({
        image: dashboardDefaultUuser,
        name: "",
    });

    useEffect(() => {
        const params = new URLSearchParams({
            fields: ["photo", "name"],
        });

        fetch(`${EASYCOMMERCE.rest_base}/me?${params.toString()}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then((response) => response.json())
            .then((data) => {
                const photoUrl =
                    data.data?.customer?.photo || dashboardDefaultUuser;
                const name = data.data?.customer?.name || "Unknown User";
                setUserInfo({ image: photoUrl, name: name });
            })
            .catch((error) => {
            });
    }, []);

    return (
        <div className="dashboard-sidebar w-full ec-db-md:w-[268px] flex flex-col sm:gap-10 gap-1 border-r border-r-ec-border p-[26px]">
            <div className="w-full pb-[15px] flex flex-col justify-center items-center gap-3 border-b border-b-ec-border">
                <div className="border border-[#F8F8F8] p-1 rounded-full inline-block mb-[10px]">
                    <img
                        src={userInfo.image}
                        alt="User Profile Image"
                        className="w-[118px] h-[118px] rounded-full object-fill"
                    />
                </div>
                <h3 className="font-inter dashboard-sidebar-title">
                    {userInfo.name}
                </h3>
            </div>

            <div
                className="w-full flex flex-col"
                id="easycommerce-sidebar-menu-list"
            >
                {filteredSidemenuList.map((item) => (
                    <div
                        key={item.id}
                        className={
                            item.subMenu ? "easycommerce-sidebar-submenu" : ""
                        }
                    >
                        {item.id === "logout" ? (
                            <a
                                href={EASYCOMMERCE.logout_url}
                                className={`easycommerce-sidebar-btn w-full p-4 gap-4 cursor-pointer text-ec-placeholder`}
                            >
                                <span className="flex justify-start items-center gap-4">
                                    <img
                                        src={
                                            activeTab === item.id
                                                ? item.activeIcon
                                                : item.icon
                                        }
                                        alt={item.title}
                                        className="w-[14px] h-[14px] pointer-events-none"
                                    />
                                    <span
                                        className={`${
                                            activeTab === item.id
                                                ? "text-ec-body"
                                                : "text-ec-placeholder"
                                        } font-inter font-medium text-base leading-[26px]`}
                                    >
                                        {item.title}
                                    </span>
                                </span>
                            </a>
                        ) : (
                            <Link
                                to={ item.id === "dashboard" ? "/" : `/${item.id}`}
                                className="easycommerce-sidebar-btn w-full p-4  gap-4 cursor-pointer"
                            >
                                <span className="flex justify-start items-center gap-4">
                                    <img
                                        src={
                                            activeTab === item.id ||
                                            (item.subMenu &&
                                                item.subMenuList.filter(
                                                    (subItem) =>
                                                        subItem.id === activeTab
                                                ).length > 0)
                                                ? item.activeIcon
                                                : item.icon
                                        }
                                        alt={item.title}
                                        className="w-[14px] h-[14px] pointer-events-none"
                                    />
                                    <span
                                        className={`${
                                            activeTab === item.id ||
                                            (item.subMenu &&
                                                item.subMenuList.filter(
                                                    (subItem) =>
                                                        subItem.id === activeTab
                                                ).length > 0)
                                                ? "text-ec-body"
                                                : "text-ec-placeholder"
                                        } font-inter font-medium text-base leading-[26px]`}
                                    >
                                        {item.title}
                                    </span>
                                </span>
                                {item.subMenu && (
                                    <img
                                        src={
                                            activeTab === item.id ||
                                            (item.subMenu &&
                                                item.subMenuList.filter(
                                                    (subItem) =>
                                                        subItem.id === activeTab
                                                ).length > 0)
                                                ? activeArrowDownIcon
                                                : defaultArrowDownIcon
                                        }
                                        alt="arrow"
                                        className="w-[15px] h-2"
                                    />
                                )}
                            </Link>
                        )}

                                {item.subMenu && (
                                    <div
                                        id={`easycommerce-submenu-${item.id}`}
                                        className={twMerge(
                                            `easycommerce-submenu-wrapper pl-6`,
                                           ( item.subMenuList.filter((subItem) => subItem.id === activeTab).length > 0 ) ? "block" : "hidden"
                                        )}
                                    >
                                        <div className="border-l border-l-ec-border">
                                            {item.subMenuList.map((subItem) => (
                                                <Link
                                                    key={subItem.id}
                                                    className={`easycommerce-submenu-btn text-left rtl:text-right w-full block px-6 py-3 cursor-pointer group`}
                                                    to={subItem.id}
                                                >
                                                    <span
                                                        className={`${
                                                            subItem.id === activeTab
                                                                ? "text-ec-body"
                                                                : "text-ec-placeholder"
                                                        } font-inter font-normal text-base leading-[26px]`}
                                                    >
                                                        {subItem.label}
                                                    </span>
                                                </Link>
                                            ))}
                                        </div>
                                    </div>
                                )}
                    </div>
                ))}
            </div>
        </div>
    );
};

export default Sidebar;
