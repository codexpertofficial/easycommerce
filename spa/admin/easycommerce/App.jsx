import React, { useEffect, useMemo, useState } from "react";
import { createRoot } from "react-dom/client";
import { Provider, useDispatch, useSelector } from "react-redux";
import { SlotFillProvider, Slot } from '@wordpress/components';
import { PluginArea } from '@wordpress/plugins';
import { applyFilters } from "@wordpress/hooks";
import { __ } from "@wordpress/i18n";
import { toast } from "react-toastify";

// CSS
import "./css/add-product.css";
import "./css/customers.css";
import "./css/store-menu.css";
import "./css/store-products.css";
import "./css/main-menu.css";

// toaster
import RootToast from "../common/RootToast";

// Redux
import { store } from "./redux-store/store";
import { setCurrentTab } from "./redux-store/slices/currentTab";

// Layout
import MainLayout from "../common/components/MainLayout";
import Notices from "../common/components/Notices/Notices";

// Pages
import Dashboard from "./pages/Dashboard";
import Products from "./pages/Products";
import AddProduct from "./pages/Products/AddProduct";
import EditProduct from "./pages/Products/EditProduct";
import Attributes from "./pages/Attributes";
import Categories from "./pages/Categories";
import Tags from "./pages/Tags";
import Brands from "./pages/Brands";
import Orders from "./pages/Orders";
import NewOrder from "./pages/Orders/NewOrder";
import SingleOrder from "./pages/Orders/SingleOrder";
import Transactions from "./pages/Transactions";
import Customers from "./pages/Customers";
import SingleCustomer from "./pages/Customers/SingleCustomer";
import Coupons from "./pages/Coupons";
import CouponDetails from "./pages/Coupons/CouponDetails";
import AbandonedCart from "./pages/AbandonedCart";
import ProductReviews from "./pages/ProductReviews";
import Help from "./pages/Help";
import Addons from "./pages/Addons";
import Pro from "./pages/Pro";
import Refunds from "./pages/Refunds";

import ReportsOverview from "./pages/Reports/pages/Overview";
import OrdersReport from "./pages/Reports/pages/Orders";
import RevenuesReport from "./pages/Reports/pages/Revenues";
import ProductsReport from "./pages/Reports/pages/Products";
import SingleProductReport from "./pages/Reports/pages/SingleProduct";
import CustomersReport from "./pages/Reports/pages/Customers";

const getPageFromPath = (path) => {
    const pageRegex = /\/page\/(\d+)$/;
    const match = path.match(pageRegex);
    return match ? Number(match[1]) : 1;
};

const App = () => {
    const dispatch = useDispatch();
    const currentTab = useSelector((state) => state.currentTab.active);

    const [isLoading, setIsLoading] = useState(true);
    const [productView, setProductView] = useState(null);
    const [orderId, setOrderId] = useState(null);
    const [customerId, setCustomerId] = useState(null);
    const [page, setPage] = useState(1);
    const [couponView, setCouponView] = useState(null);
    const [addonCategory, setAddonCategory] = useState(null);
    const [productFilterId, setProductFilterId] = useState(null);
    const [reportProductId, setReportProductId] = useState(null);
    const [reportProductName, setReportProductName] = useState('');
    const SingleProductReportPage = useMemo(
        () => reportProductId
            ? () => <SingleProductReport productId={reportProductId} setBreadcrumbTitle={setReportProductName} />
            : null,
        [reportProductId]
    );

    // For dynamic breadcrumb title
    const [editProductTitle, setEditProductTitle] = useState('');
    const [editCouponTitle, setEditCouponTitle] = useState('');
    const [customerName, setCustomerName] = useState('');

    // Show a toast after returning from the AI magic-link verification.
    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        if (!params.has('ec_connected')) return;

        if (params.get('ec_connected') === '1') {
            toast.success(__('AI connected successfully', 'easycommerce'));
        } else {
            toast.error(__('AI connection failed. Please try again.', 'easycommerce'));
        }

        // Strip the flag so the toast doesn't fire again on refresh.
        params.delete('ec_connected');
        const query = params.toString();
        window.history.replaceState(
            {},
            '',
            window.location.pathname + (query ? `?${query}` : '') + window.location.hash
        );
    }, []);

    useEffect(() => {
        const updateBackendMenu = () => {
            const wrapper = document.querySelector('.toplevel_page_easycommerce');
            if (!wrapper) return;

            const allLi = wrapper.querySelectorAll('.wp-submenu li');
            const allLinks = wrapper.querySelectorAll('.wp-submenu a');

            allLi.forEach(li => li.classList.remove('current'));
            allLinks.forEach(a => a.classList.remove('current'));

            let normalizedTab = currentTab || "/dashboard";

            const routeMapper = [
                { match: "/products/add", to: "/products/add" },
                { match: "/products/edit", to: "/products/edit" },
                { match: "/products", to: "/products" },
                { match: "/attributes", to: "/attributes" },
                { match: "/categories", to: "/categories" },
                { match: "/tags", to: "/tags" },
                { match: "/brands", to: "/brands" },
                { match: "/orders", to: "/orders" },
                { match: "/refunds", to: "/refunds" },
                { match: "/customers", to: "/customers" },
                { match: "/coupons", to: "/coupons" },
                { match: "/transactions", to: "/transactions" },
                { match: "/reports", to: "/reports" },
                { match: "/abandoned-cart", to: "/abandoned-cart" },
                { match: "/reviews", to: "/reviews" },
                { match: "/licenses", to: "/licenses" },
                { match: "/subscriptions", to: "/subscriptions" },
                { match: "/settings", to: "easycommerce-settings" },
                { match: "/dashboard", to: "/dashboard" }, 
                { match: "/get-pro", to: "/get-pro" },
                { match: "/help", to: "/help" },
                { match: "/addons", to: "/addons" },
            ];

            let searchHash = "";

            for (const item of routeMapper) {
                if (normalizedTab.startsWith(item.match)) {
                    searchHash = item.to;
                    break;
                }
            }

            const links = wrapper.querySelectorAll('.wp-submenu a');

            links.forEach(link => {
                const href = link.getAttribute('href');
                const li = link.closest('li');
                if (!href || !li) return;

                if (link.search.includes('page=easycommerce-settings')) {
                    if (normalizedTab === "/settings") {
                        li.classList.add("current");
                        link.classList.add("current");
                    }
                    return;
                }

                // Dashboard: easycommerce page with no hash route (link.hash="" for bare #)
                if (link.search.includes('page=easycommerce') && !link.search.includes('page=easycommerce-') && link.hash === '') {
                    if (normalizedTab === "/dashboard") {
                        li.classList.add("current");
                        link.classList.add("current");
                    }
                    return;
                }

                if (link.hash) {
                    const hash = link.hash.substring(1);

                    if (hash === searchHash) {
                        li.classList.add("current");
                        link.classList.add("current");
                    }
                }
            });
            
        };

        updateBackendMenu();
    }, [currentTab]);


    const handleHashChange = () => {
        const hash = window.location.hash.replace("#", "");
        const [hashPath] = hash.split("/page/");
        const currentPage = getPageFromPath(hash);

        const resetState = () => {
            setProductView(null);
            setOrderId(null);
            setCustomerId(null);
            setCouponView(null);
            setEditProductTitle('');
            setReportProductId(null);
        };

        const routeConfig = {
            "": () => {  
                dispatch(setCurrentTab("/dashboard"));
                resetState();
            },
            "/dashboard": () => {
                dispatch(setCurrentTab("/dashboard"));
                resetState();
            },
            "/products": () => {
                dispatch(setCurrentTab("/products"));
                setProductView(null);
                setPage(currentPage);
            },
            "/products/add": () => {
                dispatch(setCurrentTab("/products/add"));
                setProductView("add");
            },
            "/attributes": () => {
                dispatch(setCurrentTab("/attributes"));
                setPage(currentPage);
            },
            "/categories": () => {
                dispatch(setCurrentTab("/categories"));
                setPage(currentPage);
            },
            "/tags": () => {
                dispatch(setCurrentTab("/tags"));
                setPage(currentPage);
            },
            "/brands": () => {
                dispatch(setCurrentTab("/brands"));
                setPage(currentPage);
            },
            "/orders": () => {
                dispatch(setCurrentTab("/orders"));
                setOrderId(null);
                setProductFilterId(null);
                setPage(currentPage);
            },
            "/refunds": () => {
                dispatch(setCurrentTab("/refunds"));
                setPage(currentPage);
            },
            ordersByProduct: (match) => {
                dispatch(setCurrentTab("/orders"));
                setOrderId(null);
                setProductFilterId(match[1]);
                setPage(currentPage);
            },
            "/transactions": () => {
                dispatch(setCurrentTab("/transactions"));
                setPage(currentPage);
            },
            "/customers": () => {
                dispatch(setCurrentTab("/customers"));
                setCustomerId(null);
                setPage(currentPage);
            },
            "/abandoned-cart": () => {
                dispatch(setCurrentTab("/abandoned-cart"));
                setPage(currentPage);
            },
            "/reviews": () => {
                dispatch(setCurrentTab("/reviews"));
                setPage(currentPage);
            },
            "/coupons": () => {
                dispatch(setCurrentTab("/coupons"));
                setCouponView(null);
                setPage(currentPage);
            },
            "/coupons/new": () => {
                dispatch(setCurrentTab("/coupons"));
                setCouponView({ mode: "add" });
            },
            "/reports": () => {
                dispatch(setCurrentTab("/reports"));
            },
            "/reports/orders": () => {
                dispatch(setCurrentTab("/reports/orders"));
            },
            "/reports/revenues": () => {
                dispatch(setCurrentTab("/reports/revenues"));
            },
            "/reports/products": () => {
                dispatch(setCurrentTab("/reports/products"));
                setReportProductId(null);
            },
            "/reports/customers": () => {
                dispatch(setCurrentTab("/reports/customers"));
            },
            "/help": () => {
                dispatch(setCurrentTab("/help"));
            },
            "/addons": () => {
                dispatch(setCurrentTab("/addons"));
                setAddonCategory(null);
            },
            "/get-pro": () => {
                dispatch(setCurrentTab("/get-pro"));
            },
            "/pro": () => {
                dispatch(setCurrentTab("/get-pro"));
            },
            // Dynamic
            editProduct: (match) => {
                dispatch(setCurrentTab("/products"));
                setProductView({ mode: "edit", id: match[1] });
            },
            singleOrder: (match) => {
                dispatch(setCurrentTab("/orders"));
                setOrderId(match[1]);
            },
            singleCustomer: (match) => {
                dispatch(setCurrentTab("/customers"));
                setCustomerId(match[1]);
            },
            editCoupon: (match) => {
                dispatch(setCurrentTab("/coupons"));
                setCouponView({ mode: "edit", id: match[1] });
            },
            singleProductReport: (match) => {
                dispatch(setCurrentTab("/reports/products"));
                setReportProductId(match[1]);
            },
            addonCategory: (match) => {
                dispatch(setCurrentTab("/addons"));
                setAddonCategory(match[1]);
            }
        };

        
        if (routeConfig[hashPath]) {
            routeConfig[hashPath]();
        } else {
            const matchers = [
                [routeConfig.editProduct, /^\/products\/edit\/(\d+)$/],
                [routeConfig.singleOrder, /^\/orders\/(\d+)$/],
                [routeConfig.singleCustomer, /^\/customers\/(\d+)$/],
                [routeConfig.editCoupon, /^\/coupons\/edit\/(\d+)$/],
                [routeConfig.ordersByProduct, /^\/orders\/product\/(\d+)$/],
                [routeConfig.singleProductReport, /^\/reports\/products\/(\d+)$/],
                [routeConfig.addonCategory, /^\/addons\/([\w-]+)$/],
            ];

            let matched = false;
            for (const [handler, regex] of matchers) {
                const match = hashPath.match(regex);
                if (match) {
                    handler(match);
                    matched = true;
                    break;
                }
            }

            if (!matched) {
                dispatch(setCurrentTab(hashPath || "/dashboard"));
            }
        }

        window.scrollTo(0, 0);
        setIsLoading(false);
    };

    useEffect(() => {
        window.addEventListener("hashchange", handleHashChange);
        handleHashChange();

        return () => {
            window.removeEventListener("hashchange", handleHashChange);
        };
    }, []);

    const breadcrumbMap = applyFilters("easycommerce.store.breadcrumbMap", {
        "/dashboard": ["EasyCommerce", "Dashboard"], 
        "/products": productView?.mode === "edit"
            ? ["EasyCommerce", "Products", editProductTitle || "Edit"]
            : ["EasyCommerce", "Products"],
        "/products/add": ["EasyCommerce", "Products", "Add Product"],
        "/attributes": ["EasyCommerce", "Products", "Attributes"],
        "/categories": ["EasyCommerce", "Products", "Categories"],
        "/tags": ["EasyCommerce", "Products", "Tags"],
        "/brands": ["EasyCommerce", "Products", "Brands"],
        "/orders": orderId ? ["EasyCommerce", "Orders", `#${orderId}`] : ["EasyCommerce", "Orders"],
        "/orders/new": ["EasyCommerce", "Orders", "New Order"],
        "/refunds": ["EasyCommerce", "Refunds"],
        "/transactions": ["EasyCommerce", "Transactions"],
        "/customers": customerId ? ["EasyCommerce", "Customers", `#${customerName}`] : ["EasyCommerce", "Customers"],
        "/coupons": couponView?.mode === "edit"
        ? ["EasyCommerce", "Coupons", editCouponTitle || "Edit Coupon"]
        : couponView?.mode === "add"
        ? ["EasyCommerce", "Coupons", "Add Coupon"]
        : ["EasyCommerce", "Coupons"],
        "/reports": ["EasyCommerce", "Reports"],
        "/reports/orders": ["EasyCommerce", "Reports", "Orders"],
        "/reports/products": reportProductId ? ["EasyCommerce", "Reports", "Products", reportProductName] : ["EasyCommerce", "Reports", "Products"],
        "/reports/customers": ["EasyCommerce", "Reports", "Customers"],
        "/reports/revenues": ["EasyCommerce", "Reports", "Revenues"],
        "/abandoned-cart": ["EasyCommerce", "Abandoned Cart"],
        "/reviews": ["EasyCommerce", "Product Reviews"],
        "/addons": ["EasyCommerce", "Addons"],
        "/help": ["EasyCommerce", "Help & Support"],
        "/get-pro": ["EasyCommerce", "Pro"],
    });

    const renderContent = () => {
        let PageComponent = null;

        switch (currentTab) {
            case "/dashboard": 
                PageComponent = Dashboard;
                break;
            case "/products":
                if (productView?.mode === "edit") {
                    PageComponent = () => (
                        <EditProduct
                            id={productView.id}
                            setBreadcrumbTitle={setEditProductTitle}
                        />
                    );
                } else {
                    PageComponent = () => <Products page={page} />;
                }
                break;
            case "/products/add":
                PageComponent = AddProduct;
                break;
            case "/attributes":
                PageComponent = () => <Attributes page={page} />;
                break;
            case "/categories":
                PageComponent = () => <Categories page={page} />;
                break;
            case "/tags":
                PageComponent = () => <Tags page={page} />;
                break;
            case "/brands":
                PageComponent = () => <Brands page={page} />;
                break;
            case "/orders":
                PageComponent = orderId ? () => <SingleOrder id={orderId} /> : () => <Orders page={page} productId={productFilterId} />;
                break;
            case "/refunds":
                PageComponent = () => <Refunds page={page} />;
                break;
            case "/transactions":
                PageComponent = () => <Transactions page={page} />;
                break;
            case "/customers":
                PageComponent = customerId ? () => <SingleCustomer id={customerId} page={page} setBreadcrumbTitle={setCustomerName} /> : () => <Customers page={page} />;
                break;
            case "/coupons":
                if (couponView?.mode === "add") {
                    PageComponent = () => <CouponDetails setBreadcrumbTitle={() => {}} />;
                }
                else if (couponView?.mode === "edit") {
                    PageComponent = () => <CouponDetails id={couponView.id} setBreadcrumbTitle={setEditCouponTitle}/>;
                } else {
                    PageComponent = () => <Coupons page={page} />;
                }
                break;
            case "/reports":
                PageComponent = ReportsOverview;
                break;
            case "/reports/orders":
                PageComponent = OrdersReport;
                break;
            case "/reports/revenues":
                PageComponent = RevenuesReport;
                break;
            case "/reports/products":
                PageComponent = SingleProductReportPage || ProductsReport;
                break;
            case "/reports/customers":
                PageComponent = CustomersReport;
                break;
            case "/abandoned-cart":
                PageComponent = () => <AbandonedCart page={page} />;
                break;
            case "/reviews":
                PageComponent = () => <ProductReviews page={page} />;
                break;
            case "/help":
                PageComponent = () => <Help page={page} />;
                break;
            case "/addons":
                PageComponent = () => <Addons category={addonCategory} />;
                break;
            case "/get-pro":
                PageComponent = () => <Pro page={page} />;
                break;
        }

        const breadcrumb = breadcrumbMap[currentTab];

        return (
            <MainLayout breadcrumb={breadcrumb}>
                
                <div>
                    <Notices />
                </div>

                <Slot name="easycommerce.beforeMainContent" fillProps={{ currentTab }} />
                    {PageComponent && <PageComponent />}
                <Slot name="easycommerce.afterMainContent" fillProps={{ currentTab }} />
            </MainLayout>
        );
    };

    return (
        <div className="bg-[#EEF0FF]">
            {isLoading ? <div>Loading...</div> : renderContent()}
            <RootToast />
        </div>
    );
};

const container = document.getElementById("easycommerce_render");
const root = createRoot(container);
root.render(
    <Provider store={store}>
        <SlotFillProvider>
            <PluginArea scope="easycommerce" />
            <App />
        </SlotFillProvider>
    </Provider>
);

export default App;