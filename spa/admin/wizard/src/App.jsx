import React, { useState, useEffect,useRef } from "react";
import { __ } from '@wordpress/i18n';
import { createRoot } from "react-dom/client";
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import { Bounce, toast } from "react-toastify";
import { applyFilters } from '@wordpress/hooks';

// CSS
import "./css/wizard-menu.css";

// Pages
import Business from "./pages/Business";
import Store from "./pages/Store";
import Payment from "./pages/Payment";
import Success from "./pages/Success";
import Welcome from "./pages/Welcome";

const logo = `${EASYCOMMERCE.assets}common/img/logo.png`;
const check = `${EASYCOMMERCE.assets}admin/img/icons/check.png`;

/**
 * Filters the wizard tabs.
 *
 * @since 1.0.0
 * @param {Array} defaultTabs The default tabs array.
 */
const tabs = applyFilters('easycommerce.wizard.tabs', ["/welcome", "/business", "/store","/payment", "/success"]);
const defaultTab = tabs[0];

const App = () => {
    const formRef = useRef();
    const [activeTab, setActiveTab] = useState(
        window.location.hash.replace("#", "") || defaultTab
    );
    const [skippedTabs, setSkippedTabs] = useState([]);
    const [importDemoChecked, setImportDemoChecked] = useState(false);
    const [designs, setDesigns] = useState([]);
    const [selectedDesign, setSelectedDesign] = useState("");
    const [designResult, setDesignResult] = useState(null);
    const [hasStaticFront, setHasStaticFront] = useState(false);
    const [hasProducts, setHasProducts] = useState(false);
    const [setAsHomepage, setSetAsHomepage] = useState(false);

    const showToast = (type, message) => {
        toast[type](message, {
            position: "top-right",
            style: {
                margin: "30px 0 0 0",
                fontSize: "16px",
                fontWeight: "500",
                lineHeight: "26px",
                color: "#fff",
            },
            autoClose: 1500,
            hideProgressBar: false,
            closeOnClick: true,
            pauseOnHover: true,
            draggable: false,
            progress: undefined,
            theme: "colored",
            transition: Bounce,
        });
    };

    const [formValues, setFormValues] = useState({
        store_name: "",
        logo: "",
        business_type: "",
        country: "",
        email: "",
        shop: "",
        checkout: "",
        dashboard: "",
        payment: "",
        currency: "",
        format : "",
        payment_methods: [],
        name: "",
    });

    useEffect(() => {
        fetch(`${EASYCOMMERCE.rest_base}/connectivity/setup/get`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success && data?.data?.data) {
                    const d = data.data.data;

                    setDesigns(Array.isArray(d.designs) ? d.designs : []);
                    setSelectedDesign(d.design || "");
                    setHasStaticFront(!!d.has_static_front);
                    setHasProducts(!!d.has_products);

                    setFormValues({
                        store_name: d.store_name || "",
                        logo: d.logo || "",
                        business_type: d.business_type || "",
                        country: d.country || "",
                        email: d.business_email || "",
                        shop: d.shop || "",
                        checkout: d.checkout || "",
                        dashboard: d.dashboard || "",
                        payment: d.payment || "",
                        currency: d.currency || "",
                        format : d.format || "",
                        payment_methods: Array.isArray(d.payment_methods)
                        ? d.payment_methods
                        : (d.payment_methods ? [d.payment_methods] : []),
                        // name: d.name || EASYCOMMERCE.user.data.display_name,
                        name: d.name || EASYCOMMERCE?.user?.name || "",
                    });
                }
            });
    }, []);

    useEffect(() => {
        const handleHashChange = () => {
            const newHash = window.location.hash.replace("#", "");
            if (tabs.includes(newHash)) {
                setActiveTab(newHash);
            } else {
                setActiveTab(defaultTab);
                window.location.hash = defaultTab;
            }
        };

        window.addEventListener("hashchange", handleHashChange);
        handleHashChange();

        return () => {
            window.removeEventListener("hashchange", handleHashChange);
        };
    }, []);

    const saveDataToAPI = () => {
        if (!formRef.current) return;

        let logoInput = formRef.current.querySelector('input[name="general-business-logo"]');
        if (!logoInput) {
            logoInput = document.createElement('input');
            logoInput.type = 'hidden';
            logoInput.name = 'general-business-logo';
            formRef.current.appendChild(logoInput);
        }
        logoInput.value = formValues.logo || '';

        const formDataObject = new FormData(formRef.current);
        const entries        = Object.fromEntries(formDataObject.entries());
        const paymentMethods = formDataObject.getAll('payment-methods-active_methods');

        entries['payment-methods-active_methods'] = paymentMethods;
    
        fetch(`${EASYCOMMERCE.rest_base}/connectivity/setup/save`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify({
                type: activeTab.replace("/", ""),
                data: entries
                }),
            })
            .then((res) => res.json())
            .then((result) => {
            });
    };


    const handleNext = async () => {
        const currentIndex = tabs.indexOf(activeTab);

        // Store Email is required before leaving the Business step.
        if (activeTab === "/business") {
            const email = (formValues.email || "").trim();
            if (!email) {
                showToast("error", __("Store Email is required.", "easycommerce"));
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showToast("error", __("Please enter a valid Store Email.", "easycommerce"));
                return;
            }
        }

        if (activeTab === "/store" && importDemoChecked) {
            try {
                const res = await fetch(`${EASYCOMMERCE.rest_base}/importer/samples`, {
                    method: "POST",
                    headers: {
                        "X-WP-Nonce": EASYCOMMERCE.nonce,
                        "Content-Type": "application/json",
                    },
                });

            } catch (err) {
                showToast("error", __("Demo import failed. Please try again.", "easycommerce"));
            }
        }

        // Apply the chosen store design (no selection leaves current behaviour unchanged).
        if (activeTab === "/store" && selectedDesign) {
            try {
                const res = await fetch(`${EASYCOMMERCE.rest_base}/connectivity/apply-design`, {
                    method: "POST",
                    headers: {
                        "X-WP-Nonce": EASYCOMMERCE.nonce,
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ design_id: selectedDesign, set_homepage: setAsHomepage }),
                });
                const data = await res.json();
                if (data?.success && data?.data && !data.data.skipped) {
                    setDesignResult(data.data);
                }
            } catch (err) {
                showToast("error", "Applying the store design failed. Please try again.");
            }
        }

        if (currentIndex < tabs.length - 1) {
            const nextTab = tabs[currentIndex + 1];
            setActiveTab(nextTab);
            window.location.hash = nextTab;
            window.scrollTo({ top: 0, behavior: "smooth" });

            saveDataToAPI();
        }
    };
    
    const handlePrevious = () => {
        const currentIndex = tabs.indexOf(activeTab);
        if (currentIndex > 0) {
            const prevTab = tabs[currentIndex - 1];
            setActiveTab(prevTab);
            window.location.hash = prevTab;
            window.scrollTo({ top: 0, behavior: "smooth" });
        }
    };

    const handleSkip = () => {
        const currentIndex = tabs.indexOf(activeTab);
        if (currentIndex < tabs.length - 1) {
            const nextTab = tabs[currentIndex + 1];
            setSkippedTabs((prev) => [...new Set([...prev, activeTab])]);
            setActiveTab(nextTab);
            window.location.hash = nextTab;
            window.scrollTo({ top: 0, behavior: "smooth" });
        }
    };


    const completedTabIcon = (
        <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="13" cy="13" r="13" fill="#19AA79"/>
            <path d="M18.7589 9.225C18.9196 9.39167 19 9.58333 19 9.8C19 10.0167 18.9196 10.2083 18.7589 10.375L11.9018 16.775C11.7232 16.925 11.5179 17 11.2857 17C11.0536 17 10.8482 16.925 10.6696 16.775L7.24107 13.575C7.08036 13.4083 7 13.2167 7 13C7 12.7833 7.08036 12.5917 7.24107 12.425C7.41964 12.275 7.625 12.2 7.85714 12.2C8.08929 12.2 8.29464 12.275 8.47321 12.425L11.2589 15.075L17.5268 9.225C17.7054 9.075 17.9107 9 18.1429 9C18.375 9 18.5804 9.075 18.7589 9.225Z" fill="white"/>
        </svg>
    )

    const activeTabIcon = (
        <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="13" cy="13" r="12" stroke="#7351FD" stroke-width="2"/>
            <path d="M18.7589 9.225C18.9196 9.39167 19 9.58333 19 9.8C19 10.0167 18.9196 10.2083 18.7589 10.375L11.9018 16.775C11.7232 16.925 11.5179 17 11.2857 17C11.0536 17 10.8482 16.925 10.6696 16.775L7.24107 13.575C7.08036 13.4083 7 13.2167 7 13C7 12.7833 7.08036 12.5917 7.24107 12.425C7.41964 12.275 7.625 12.2 7.85714 12.2C8.08929 12.2 8.29464 12.275 8.47321 12.425L11.2589 15.075L17.5268 9.225C17.7054 9.075 17.9107 9 18.1429 9C18.375 9 18.5804 9.075 18.7589 9.225Z" fill="#7351FD"/>
        </svg>
    )

    const inactiveTabIcon = (
        <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="13" cy="13" r="12" stroke="#7F7F98" stroke-width="2"/>
            <path d="M18.7589 9.225C18.9196 9.39167 19 9.58333 19 9.8C19 10.0167 18.9196 10.2083 18.7589 10.375L11.9018 16.775C11.7232 16.925 11.5179 17 11.2857 17C11.0536 17 10.8482 16.925 10.6696 16.775L7.24107 13.575C7.08036 13.4083 7 13.2167 7 13C7 12.7833 7.08036 12.5917 7.24107 12.425C7.41964 12.275 7.625 12.2 7.85714 12.2C8.08929 12.2 8.29464 12.275 8.47321 12.425L11.2589 15.075L17.5268 9.225C17.7054 9.075 17.9107 9 18.1429 9C18.375 9 18.5804 9.075 18.7589 9.225Z" fill="#7F7F98"/>
        </svg>
    )

    // Filter tabs for progress bar (exclude welcome and success)
    const progressTabs = tabs.filter(tab => tab !== "/welcome" && tab !== "/success");

    return (
        <div className="flex justify-center items-center">
            <div className="w-full h-screen flex flex-col items-center bg-ec-main-bg">
                {activeTab !== "/success" && (
                    <div className="w-full bg-white">
                        <div className="container flex items-center justify-between p-6 mx-auto">
                            <img
                                src={logo}
                                alt="easycommerce"
                                className="w-[158px]"
                            />

                            <div className="relative flex flex-col items-center w-[725px]">
                                <div className="relative w-full mr-[90px]">
                                    <div className="flex justify-between items-center">
                                        {progressTabs.map((tab, index) => (
                                            <>
                                                <div
                                                    key={index}
                                                    className="relative z-10 flex gap-3 items-center"
                                                >
                                                    {
                                                        tabs.indexOf(activeTab) > tabs.indexOf(tab)
                                                            ? skippedTabs.includes(tab)
                                                                ? inactiveTabIcon
                                                                : completedTabIcon
                                                            : activeTab === tab
                                                            ? activeTabIcon
                                                            : inactiveTabIcon
                                                    }

                                                    <div
                                                        className={`text-center text-[16px] capitalize ${
                                                            activeTab === tab
                                                                ? "text-ec-primary"
                                                                : "text-ec-body"
                                                        }`}
                                                    >
                                                        {tab.replace("/", "")}
                                                    </div>
                                                </div>
                                                {index !== progressTabs.length - 1 && (
                                                    <div className="w-[61px] h-[2px] bg-ec-light-black mx-[10px]"></div>
                                                )}
                                            </>
                                        ))}
                                    </div>
                                </div>
                            </div>

                            <button
                                className="font-inter underline font-normal text-base leading-[26px]
                                hover:text-ec-secondary text-ec-body transition-all ease-in-out duration-300"
                                onClick={handleSkip}
                            >
                                {__("Skip", "easycommerce")}
                            </button>
                        </div>
                    </div>
                )}

                {["/welcome", "/business", "/store", "/payment"].includes(activeTab) && (
                    activeTab === "/welcome" ? (
                        <div className=" bg-ec-main-bg">
                            <Welcome handleNext={handleNext} />
                        </div>
                    ) : (
                        <div className="pt-8 w-full bg-ec-main-bg">
                            <form
                                ref={formRef}
                                className="mb-12 w-[870px] px-[70px] pt-[45px] pb-[60px] mx-auto bg-white rounded-[20px]"
                            >
                                <div>
                                    <div className={activeTab === "/business" ? "block" : "hidden"}>
                                        <Business formValues={formValues} setFormValues={setFormValues} />
                                    </div>
                                    <div className={activeTab === "/store" ? "block" : "hidden"}>
                                        <Store
                                            formValues={formValues}
                                            setFormValues={setFormValues}
                                            designs={designs}
                                            selectedDesign={selectedDesign}
                                            setSelectedDesign={setSelectedDesign}
                                            hasStaticFront={hasStaticFront}
                                            hasProducts={hasProducts}
                                            setAsHomepage={setAsHomepage}
                                            setSetAsHomepage={setSetAsHomepage}
                                            importDemoChecked={importDemoChecked}
                                            setImportDemoChecked={setImportDemoChecked} />
                                    </div>
                                    <div className={activeTab === "/payment" ? "block" : "hidden"}>
                                        <Payment formValues={formValues} setFormValues={setFormValues} />
                                    </div>
                                </div>

                                <div className="mt-8 flex justify-between items-center gap-4">
                                    <div>
                                        {activeTab !== defaultTab &&
                                            activeTab !== tabs[tabs.length - 1] && (
                                                <button
                                                    type="button"
                                                    onClick={handlePrevious}
                                                    className="w-[120px] h-12 bg-transparent font-inter font-normal 
                                                    text-ec-primary hover:text-white text-base leading-[26px] border 
                                                    border-ec-primary rounded-lg focus:shadow-none hover:bg-ec-primary 
                                                    transition-all ease-in-out duration-300"
                                                >
                                                    {__("Previous", "easycommerce")}
                                                </button>
                                            )}
                                    </div>

                                    <div className="flex justify-end items-center gap-8">
                                        {activeTab !== tabs[tabs.length - 1] && (
                                            <button
                                                onClick={handleNext}
                                                type="button"
                                                className="w-[120px] h-12 font-inter bg-ec-primary group border border-ec-primary
                                                rounded-lg text-white font-normal hover:text-white hover:bg-ec-secondary
                                                focus:shadow-none lg:text-base md:text-xs sm:text-sm transition-all ease-in-out
                                                duration-300 leading-[26px] disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                {__("Next", "easycommerce")}
                                            </button>
                                        )}
                                    </div>
                                </div>
                            </form>
                        </div>
                    )
                )}

                {activeTab === "/success" && (
                    <div className="w-full h-screen bg-ec-main-bg">
                        <Success designResult={designResult} />
                    </div>
                )}
            </div>

            <ToastContainer />
        </div>
    );
};
const container = document.getElementById("easycommerce_wizard_render");
const root = createRoot(container);
root.render(
    <App />
);