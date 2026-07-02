import React, { useEffect, useState } from "react";
import Cookies from "universal-cookie";

// components
import Card from "./components/Card";
import CommonHeader from "../../../common/components/CommonHeader";
import APIScreen from "../../../common/components/APIScreen";
import APIVarification from "../../../common/components/APIScreen/elements/APIVarification";
import APICreateForm from "../../../common/components/APIScreen/elements/APICreateForm";
import CardSkeleton from "./components/CardSkeleton";
import NotFound from "../../../common/NotFound";
import LicenseVarification from "../../../common/components/LicenseScreen/elements/LicenseVarification";
import LicenseScreen from "../../../common/components/LicenseScreen";

const noDataIcon = `${EASYCOMMERCE.assets}admin/img/nofound/no-data.png`;

const Addons = () => {
    const [user, setUser] = useState(null);
    const [addons, setAddons] = useState([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
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
                    const addonsArray = Object.values(data.data.addons);
                    setAddons(addonsArray);
                }
            });
    }, []);

    useEffect(() => {
        const cookies = new Cookies(null, { path: "/" });
        const easyUser = cookies.get("easycommerce-user");
    }, [user]);

    return (
        <>
            {/* <CommonHeader
                parentSlug="easycommerce"
                parentLavel="EasyCommerce"
                breadcumpSlug="Addons"
            /> */}

            <div className="mt-3 bg-white max-w-full py-[50px] px-[30px] rounded-xl">
                {!isLoading ? (
                    <>
                        {addons.length > 0 ? (
                            <>
                                <div className="easycommerce-addons-heading text-center">
                                    <h2 className="text-center text-[32px] font-inter font-normal text-ec-title leading-[48px] mb-3">
                                        EasyCommerce Addons
                                    </h2>
                                    <p className="text-ec-body text-base font-inter font-normal leading-[26px] mb-12">
                                        Use EasyCommerce addons to extend and customize the functionality of your online store.
                                    </p>
                                </div>

                                <div className="grid 2xl:grid-cols-4 xl:grid-cols-4 gap-[30px]">
                                    {addons.length > 0 &&
                                        addons.map((addon, index) => (
                                            <Card
                                                key={index}
                                                addon={addon}
                                            />
                                        ))}
                                </div>
                            </>
                        ) : (
                            <NotFound
                                ImageUrl={noDataIcon}
                                title="No Addons Found"
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
