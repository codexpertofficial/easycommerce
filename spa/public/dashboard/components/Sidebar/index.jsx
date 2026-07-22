import React, { useState, useEffect } from "react";
import {applyFilters} from "@wordpress/hooks";
import { __ } from "@wordpress/i18n";
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
        title: __( "Dashboard", "easycommerce" ),
        subMenu: false,
        subMenuList: [],
    },
    {
        id: "orders",
        icon: defaultOrdersIcon,
        activeIcon: activeOrdersIcon,
        title: __( "Purchases", "easycommerce" ),
        subMenu: true,
        subMenuList: [
            { id: "orders", label: __( "Orders", "easycommerce" ) },
            { id: "transactions", label: __( "Transactions", "easycommerce" ) },
            { id: "downloads", label: __( "Downloads", "easycommerce" ) },
        ],
    },
    {
        id: "profile",
        icon: defaultSettingsIcon,
        activeIcon: activeSettingsIcon,
        title: __( "Settings", "easycommerce" ),
        subMenu: true,
        subMenuList: [
            { id: "profile", label: __( "Profile", "easycommerce" ) },
            { id: "address", label: __( "Address", "easycommerce" ) },
            { id: "password", label: __( "Password", "easycommerce" ) },
        ],
    },
    {
        id: "logout",
        icon: defaultLogoutIcon,
        activeIcon: defaultLogoutIcon,
        title: __( "Logout", "easycommerce" ),
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
                const name = data.data?.customer?.name || __( "Unknown User", "easycommerce" );
                setUserInfo({ image: photoUrl, name: name });
            })
            .catch((error) => {
            });
    }, []);

    return (
        <div className="dashboard-sidebar w-full ec-db-md:w-[268px] flex flex-col sm:gap-8 gap-1 bg-white border-r border-r-ec-border p-5 sm:p-6">
            <div className="w-full pb-6 flex flex-col justify-center items-center gap-3 border-b border-b-ec-border">
                <div className="p-1 rounded-full inline-block mb-[6px] border border-ec-border bg-white">
                    <img
                        src={userInfo.image}
                        alt={__( "User Profile Image", "easycommerce" )}
                        className="w-[104px] h-[104px] rounded-full object-cover"
                    />
                </div>
                <h3 className="font-inter dashboard-sidebar-title text-center">
                    {userInfo.name}
                </h3>
            </div>

            <div
                className="w-full flex flex-col gap-1"
                id="easycommerce-sidebar-menu-list"
            >
                {filteredSidemenuList.map((item) => {
                    const isActive =
                        activeTab === item.id ||
                        (item.subMenu &&
                            item.subMenuList.some(
                                (subItem) => subItem.id === activeTab
                            ));

                    const isLogout = item.id === "logout";

                    const labelClass = `font-inter font-medium text-base leading-[26px] ${
                        isActive ? "text-ec-primary" : "text-ec-placeholder"
                    }`;

                    const btnClass = `easycommerce-sidebar-btn w-full p-3 sm:p-[14px] gap-4 cursor-pointer rounded-xl transition-colors duration-200 hover:bg-ec-active ${
                        isActive && !isLogout ? "bg-ec-active" : ""
                    }`;

                    const iconSrc = isActive ? item.activeIcon : item.icon;

                    return (
                        <div
                            key={item.id}
                            className={
                                item.subMenu ? "easycommerce-sidebar-submenu" : ""
                            }
                        >
                            {isLogout ? (
                                <a
                                    href={EASYCOMMERCE.logout_url}
                                    className={`easycommerce-sidebar-btn w-full p-3 sm:p-[14px] gap-4 cursor-pointer rounded-xl transition-colors duration-200 hover:bg-ec-red-bg group`}
                                >
                                    <span className="flex justify-start items-center gap-4">
                                        <img
                                            src={iconSrc}
                                            alt={item.title}
                                            className="w-[16px] h-[16px] pointer-events-none"
                                        />
                                        <span className="font-inter font-medium text-base leading-[26px] text-ec-placeholder group-hover:text-ec-red">
                                            {item.title}
                                        </span>
                                    </span>
                                </a>
                            ) : (
                                <Link
                                    to={item.id === "dashboard" ? "/" : `/${item.id}`}
                                    className={btnClass}
                                >
                                    <span className="flex justify-start items-center gap-4">
                                        <img
                                            src={iconSrc}
                                            alt={item.title}
                                            className="w-[16px] h-[16px] pointer-events-none"
                                        />
                                        <span className={labelClass}>
                                            {item.title}
                                        </span>
                                    </span>
                                    {item.subMenu && (
                                        <img
                                            src={
                                                isActive
                                                    ? activeArrowDownIcon
                                                    : defaultArrowDownIcon
                                            }
                                            alt={__( "arrow", "easycommerce" )}
                                            className={`w-[15px] h-2 transition-transform duration-200 ${
                                                isActive ? "rotate-180" : ""
                                            }`}
                                        />
                                    )}
                                </Link>
                            )}

                            {item.subMenu && (
                                <div
                                    id={`easycommerce-submenu-${item.id}`}
                                    className={twMerge(
                                        `easycommerce-submenu-wrapper pl-6 pt-1`,
                                        item.subMenuList.some(
                                            (subItem) => subItem.id === activeTab
                                        )
                                            ? "block"
                                            : "hidden"
                                    )}
                                >
                                    <div className="flex flex-col gap-1">
                                        {item.subMenuList.map((subItem) => {
                                            const subActive =
                                                subItem.id === activeTab;

                                            return (
                                                <Link
                                                    key={subItem.id}
                                                    className={`easycommerce-submenu-btn text-left rtl:text-right w-full block px-4 py-2 cursor-pointer group rounded-xl transition-colors duration-200 ${
                                                        subActive
                                                            ? "bg-ec-active"
                                                            : "hover:bg-ec-active"
                                                    }`}
                                                    to={subItem.id}
                                                >
                                                    <span
                                                        className={`${
                                                            subActive
                                                                ? "text-ec-primary font-medium"
                                                                : "text-ec-placeholder"
                                                        } font-inter text-base leading-[26px]`}
                                                    >
                                                        {subItem.label}
                                                    </span>
                                                </Link>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}
                        </div>
                    );
                })}
            </div>
        </div>
    );
};

export default Sidebar;
