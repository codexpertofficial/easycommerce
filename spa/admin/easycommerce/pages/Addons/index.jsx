import React, { useEffect, useMemo, useState } from "react";
import { __ } from "@wordpress/i18n";
import Cookies from "universal-cookie";

// components
import Card from "./components/Card";
import FilterTabs from "./components/FilterTabs";
import CommonHeader from "../../../common/components/CommonHeader";
import APIScreen from "../../../common/components/APIScreen";
import APIVarification from "../../../common/components/APIScreen/elements/APIVarification";
import APICreateForm from "../../../common/components/APIScreen/elements/APICreateForm";
import CardSkeleton from "./components/CardSkeleton";
import NotFound from "../../../common/NotFound";
import LicenseVarification from "../../../common/components/LicenseScreen/elements/LicenseVarification";
import LicenseScreen from "../../../common/components/LicenseScreen";

const noDataIcon = `${EASYCOMMERCE.assets}admin/img/nofound/no-data.png`;

// The route re-mounts this component on every category change; cache the
// addons list so filter clicks don't refetch and re-show the skeleton.
let addonsCache = null;

const Addons = ({ category = null }) => {
    const [user, setUser] = useState(null);
    const [addons, setAddons] = useState(addonsCache || []);
    const [isLoading, setIsLoading] = useState(!addonsCache);

    const activeCategory = category || "all";

    useEffect(() => {
        if (addonsCache) return;

        fetch(`${EASYCOMMERCE.rest_base}/addons`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then((res) => res.json())
            .then((data) => {
                setIsLoading(false);
                if (data.success && data.data?.addons) {
                    addonsCache = Object.values(data.data.addons);
                    setAddons(addonsCache);
                }
            });
    }, []);

    useEffect(() => {
        const cookies = new Cookies(null, { path: "/" });
        const easyUser = cookies.get("easycommerce-user");
    }, [user]);

    const tabs = useMemo(() => {
        const map = new Map();

        addons.forEach((addon) => {
            (addon.categories || []).forEach((cat) => {
                if (!cat?.slug) return;

                const existing = map.get(cat.slug);
                if (existing) {
                    existing.count += 1;
                } else {
                    map.set(cat.slug, {
                        key: cat.slug,
                        label: cat.name || cat.slug,
                        count: 1,
                    });
                }
            });
        });

        const categoryTabs = Array.from(map.values()).sort(
            (a, b) => b.count - a.count || a.label.localeCompare(b.label)
        );

        return [
            { key: "all", label: __("All", "easycommerce"), count: addons.length },
            ...categoryTabs,
        ];
    }, [addons]);

    const visibleAddons = useMemo(() => {
        if (activeCategory === "all") return addons;

        return addons.filter((addon) =>
            (addon.categories || []).some((cat) => cat?.slug === activeCategory)
        );
    }, [addons, activeCategory]);

    const handleTabChange = (key) => {
        window.location.hash =
            key === "all" ? "#/addons" : `#/addons/${key}`;
    };

    return (
        <>
            {/* <CommonHeader
                parentSlug="easycommerce"
                parentLavel="EasyCommerce"
                breadcumpSlug="Addons"
            /> */}

            <div className="mt-3 bg-white max-w-full py-[50px] px-[30px] rounded-xl">
                <div className="easycommerce-addons-heading text-center">
                    <h2 className="text-center text-[32px] font-inter font-normal text-ec-title leading-[48px] mb-3">
                        {__("EasyCommerce Addons", "easycommerce")}
                    </h2>
                    <p className="text-ec-body text-base font-inter font-normal leading-[26px] mb-12">
                        {__("Use EasyCommerce addons to extend and customize the functionality of your online store.", "easycommerce")}
                    </p>
                </div>

                {!isLoading ? (
                    <>
                        {addons.length > 0 ? (
                            <>
                                <FilterTabs
                                    tabs={tabs}
                                    active={activeCategory}
                                    onChange={handleTabChange}
                                />

                                <div className="grid 2xl:grid-cols-4 xl:grid-cols-4 gap-[30px]">
                                    {visibleAddons.map((addon, index) => (
                                        <Card
                                            key={addon.slug || index}
                                            addon={addon}
                                        />
                                    ))}
                                </div>
                            </>
                        ) : (
                            <NotFound
                                ImageUrl={noDataIcon}
                                title={__("No Addons Found", "easycommerce")}
                            />
                        )}
                    </>
                ) : (
                    <CardSkeleton />
                )}
            </div>

            {/* {showAPIModal && (
                <>
                    {currentAPIModalTab === "" ? (
                        <APIScreen
                            onClose={handleModalClose}
                            switchVariationModalTab={() =>
                                setCurrentAPIModalTab("apiVarification")
                            }
                            switchCreateModalTab={() =>
                                setCurrentAPIModalTab("apiCreate")
                            }
                        />
                    ) : currentAPIModalTab === "apiVarification" ? (
                        <APIVarification
                            onClose={handleModalClose}
                            switchModalTab={() =>
                                setCurrentAPIModalTab("apiCreate")
                            }
                            setUserAfterVarification={(userData) => {
                                setUser(userData);
                                window.location.reload();
                            }}
                        />
                    ) : currentAPIModalTab === "apiCreate" ? (
                        <APICreateForm
                            onClose={handleModalClose}
                            switchModalTab={() =>
                                setCurrentAPIModalTab("apiVarification")
                            }
                        />
                    ) : currentAPIModalTab === "license" ? ( 
                        <LicenseVarification
                            onClose={handleModalClose}
                            addon={ClickedAddon}
                            switchModalTab={() => setCurrentAPIModalTab("licenseVerification")}
                        />
                    ) : currentAPIModalTab === "licenseVerification" ? (
                        <LicenseScreen
                            addon={ClickedAddon}
                            onClose={handleModalClose}
                            switchVariationModalTab={() =>
                                setCurrentAPIModalTab("license")
                            }
                        />
                    ) : null}
                </>
            )} */}
        </>
    );
};

export default Addons;
