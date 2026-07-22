import React, { useEffect, useState } from "react";
import { useDispatch } from "react-redux";
import { toast } from "react-toastify";
import { __, sprintf } from "@wordpress/i18n";

// redux slice
import { resetProduct } from "../../redux-store/slices/newProduct";

// components
import ProductTable from "./components/ProductTable";
import ProductTableFilter from "./components/ProductTableFilter";
import Pagination from "../../../common/components/Pagination";
import NotFound from "../../../common/NotFound";
import DeletePopup from "../../../common/components/DeletePopup"
import TableSkeleton from "../../../common/TableSkeleton";
import ProductActionBar from "./components/ProductActionBar";
import ImportModal from "./ImportModal";
import ImportProgress from "./ImportProgress";
import SampleProductsModal from "./components/SampleProductsModal";

const noProduct = `${EASYCOMMERCE.assets}admin/img/nofound/no-products.png`;

const Products = ({ page }) => {
	const dispatch = useDispatch();
	const [tableColumns, setTableColumns] = useState([
		"title",
		"status",
		"category",
		"price",
		"stock",
		"sales",
	]);
	const [totalPage, setTotalPage] = useState(0);
	const [postPerPage, setPostPerPage] = useState(20);
	const [products, setProducts] = useState([]);
	const [categories, setCategories] = useState([]);
	const [isLoading, setIsLoading] = useState(true);
    const [isStatusLoaded, setisStatusLoaded] = useState(false);
    const [productStatusCounts, productSetStatusCounts] = useState({});
    const [activeTab, setActiveTab] = useState("all");
	const [showModal, setShowModal] = useState(false);
    const [productAllShowModal, setProductAllShowModal] = useState(false);
	const [productIdToDelete, setProductIdToDelete] = useState(null);
	const [forceDelete, setForceDelete] = useState(false);
	const [productsFiltered, setProductsFiltered] = useState(false);
	const [searchTrigger, setSearchTrigger] = useState(0);
    const [selectedProducts, setSelectedProducts] = useState([]);
    const [productIsBulkSelect, setProductIsBulkSelect] = useState(false);
    const [productToDeleteStatus, setProductToDeleteStatus] = useState(null);
    const [filterLoader, setFilterLoader] = useState(false);
    const [showImportModal, setShowImportModal] = useState(false);
    const [isImporting, setIsImporting] = useState(false);
    const [showSampleModal, setShowSampleModal] = useState(false);
    const [demoCount, setDemoCount] = useState(0);
    const hasAnyProducts = Object.values(productStatusCounts).reduce((a, b) => a + b, 0) > 0;

    const fetchDemoCount = () => {
        fetch(`${EASYCOMMERCE.rest_base}/importer/demo`, {
            headers: { "X-WP-Nonce": EASYCOMMERCE.nonce },
        })
            .then((res) => res.json())
            .then((data) => setDemoCount(data?.data?.count ?? 0))
            .catch(() => {});
    };

    useEffect(() => {
        fetchDemoCount();
    }, []);

    const handleSelectAllProducts = (checked) => {
        setProductIsBulkSelect(checked);
        if (checked) {
            const allIds = products.map(product => product.id);
            setSelectedProducts(allIds);
        } else {
            setSelectedProducts([]);
        }
    };

    const handleSelectOneProduct = (id) => {
        setProductIsBulkSelect(false);
        setSelectedProducts((prev) =>
            prev.includes(id)
                ? prev.filter((productId) => productId !== id)
                : [...prev, id]
        );
    };
	const [formState, setFormState] = useState({
		search: "",
		category: [],
		sortBy: "",
	});
    const statusStyles = {
        publish: "bg-ec-liveBg text-ec-liveText",
        trash: "bg-ec-trashBg text-ec-trashText",
        draft: "bg-ec-draftBg text-ec-draftText",
    };
    const productStatuses = EASYCOMMERCE.product_statuses;
    const tabOptions = [
        {
            label: __("All", "easycommerce"),
            key: "all",
            bg: "bg-ec-allBg text-ec-allText",
        },
        ...Object.entries(productStatuses).map(([key, label]) => ({
            label,
            key,
            bg: statusStyles[key],
        })),
    ];

    // Tab counts for "All"
    const tabCounts = {
        all: productStatusCounts
            ? Object.values(productStatusCounts).reduce((a, b) => a + b, 0)
            : 0,
        ...productStatusCounts,
    };

	const handleAddProduct = () => {
		dispatch(resetProduct());
		window.location.hash = "#/products/add";
	};

	const restoreProduct = (productId, productTitle) => {
		console.table([productId, productTitle]);
 
		const url = `${EASYCOMMERCE.rest_base}/products/${productId}`;
		easycommerce_modal(true);
 
		fetch(url, {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			},
			body: JSON.stringify({ status: "draft", title: productTitle }),
		})
			.then((res) => res.json())
			.then((data) => {
				easycommerce_modal(false);
 
				if (data.success) {
                    toast.success(data.data.message);
					setProducts(
						products.map((product) => {
							if (product.id === productId) {
								return {
									...product,
									status: "draft",
								};
							}
 
							return product;
						})
					);
					setForceDelete(false);
				}
			})
			.catch(() => {
				easycommerce_modal(false);
				toast.error(__('Unable to restore the product. Please try again.', 'easycommerce'));
			});
	};

    const filterProducts = () => {
        setSearchTrigger(prev => prev + 1);
        setProductsFiltered(true);
        window.location.hash = "#/products";
    };

    const resetFilter = () => {
        setFormState({
            search: "",
            category: [],
            sortBy: "",
        });
        setProductsFiltered(false);
        setSearchTrigger(prev => prev + 1);
        window.location.hash = "#/products";
    };
    
	useEffect(() => {
		fetch(`${EASYCOMMERCE.rest_base}/products/categories`, {
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			},
		})
			.then((resp) => resp.json())
			.then((data) => {
				if (data.success && Array.isArray(data.data?.categories)) {
					setCategories(data.data.categories);
				}
			})
			.catch(() => {
				toast.error(__('Unable to load product categories. Please refresh and try again.', 'easycommerce'));
			});
	}, []);

    const fetchProductsStatuses = () => {
        const url = `${EASYCOMMERCE.rest_base}/products?page=${page}&per_page=${postPerPage}&status=any`;
    
        fetch(url, {
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
        .then((res) => res.json())
        .then((data) => {
            if (data.success && data.data.statuses_counts) {
                productSetStatusCounts(data.data.statuses_counts);
            }
            setisStatusLoaded(true);
        })
        .catch(() => {
            setisStatusLoaded(true);
            toast.error(__('Unable to load product status counts. Please refresh and try again.', 'easycommerce'));
        });
    };
    
    useEffect(() => {
        fetchProductsStatuses();
    }, []);
    
	const fetchProducts = () => {
        const params = {
            categories: formState.category,
        };

        const categoryParams = new URLSearchParams(params);
        const categoryParamsString = categoryParams.toString();

        const searchQuery =
            formState.search.length > 0 ? `s=${formState.search}&` : "";

        const querySep = EASYCOMMERCE.permalink ? "?" : "&";
        const statusValue = activeTab === "all" ? "any,trash" : activeTab;
        const url = !productsFiltered
            ? `${EASYCOMMERCE.rest_base}/products${querySep}page=${page}&per_page=${postPerPage}&status=${statusValue}`
            : `${EASYCOMMERCE.rest_base}/products${querySep}${searchQuery}${categoryParamsString}&sort_by=${formState.sortBy}&page=${page}&per_page=${postPerPage}&status=${statusValue}`;

        setIsLoading(true);
        productsFiltered && setFilterLoader(true);

        fetch(url, {
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
        .then((res) => res.json())
        .then((data) => {
            setIsLoading(false);
            if (data.success && Array.isArray(data.data?.products)) {
                setTotalPage(data.data.total_pages);
                setProducts(data.data.products);
                 productSetStatusCounts(data.data.statuses_counts);
            } else {
                setTotalPage(1);
                setProducts([]);
            }
        })
        .catch(() => {
            setIsLoading(false);
            setFilterLoader(false);
            toast.error(__('Unable to load products. Please refresh and try again.', 'easycommerce'));
        });
    };

    useEffect( () => {
        const storedTab = localStorage.getItem('easycommerce_products_activeTab');
        if (storedTab ) {
            setActiveTab(storedTab);
        }
        if(isStatusLoaded ) {
            fetchProducts();
        }
        
	}, [searchTrigger, activeTab, isStatusLoaded] );


    const afterApiCallAction = () => {
        if (forceDelete) {
            setProducts(
                products.filter(
                    (product) =>
                        product.id !== productIdToDelete
                )
            );
            setForceDelete(false);
        } else {
            setProducts(
                products.map((product) => {
                    if (product.id === productIdToDelete) {
                        return {
                            ...product,
                            status: "trash",
                        };
                    }
                    return product;
                })
            );
        }

        setProductIdToDelete(null);
    };

    const afterInstatntDelete = () => {
        setProducts(products.filter((product) => product.id !== productIdToDelete));
        setProductIdToDelete(null);
    };

    const deleteAction = () => {
        easycommerce_modal(true);
        setShowModal(false);
        setProductIdToDelete(null);

        fetch(`${EASYCOMMERCE.rest_base}/products/${productIdToDelete}${forceDelete ? "?force=true" : ""}`,
            {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "X-WP-Nonce": EASYCOMMERCE.nonce,
                }
            }
        )
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success) {
                    toast.success(__('Product trashed successfully!', 'easycommerce'));
                    productSetStatusCounts((prev) => {
                        const updated = { ...prev };
                        if (productToDeleteStatus && updated[productToDeleteStatus] > 0) {
                            updated[productToDeleteStatus] -= 1;
                        }
                        updated['trash'] = (updated['trash'] || 0) + 1;
                        return updated;
                    });
                    setProductToDeleteStatus(null);
                    afterApiCallAction();
                }
            })
            .catch(() => {
                easycommerce_modal(false);
                toast.error(__('Unable to trash the product. Please try again.', 'easycommerce'));
            });
    };

    const deleteNow = () => {
        easycommerce_modal(true);
        setShowModal(false);

        fetch(`${EASYCOMMERCE.rest_base}/products/${productIdToDelete}?force=true`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            }
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success) {
                    toast.success(__('Product deleted successfully!', 'easycommerce'));
                    productSetStatusCounts((prev) => {
                        const updated = { ...prev };
                        if (productToDeleteStatus && updated[productToDeleteStatus] > 0) {
                            updated[productToDeleteStatus] -= 1;
                        }
                        return updated;
                    });
                    setProductToDeleteStatus(null);
                    setForceDelete(false);
                    afterInstatntDelete();
                }
            })
            .catch(() => {
                easycommerce_modal(false);
                toast.error(__('Unable to delete the product. Please try again.', 'easycommerce'));
            });
    };

    if (isImporting) {
        return <ImportProgress onComplete={() => {
            setIsImporting(false);
            fetchProducts();
            fetchProductsStatuses();
        }} />;
    }

	return (
		<>
            <div className="flex items-start justify-start gap-4 mb-4">
                <div className="product-panel-title">
                    <h3>{__("Products", "easycommerce")}</h3>
                </div>
                <button
                    onClick={handleAddProduct}
                    className="flex h-[41px] justify-center items-center gap-1 font-inter bg-white group border border-ec-primary px-3 py-2 rounded-lg text-ec-primary hover:text-white hover:bg-ec-primary focus:shadow-none focus:text-white focus:bg-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out duration-500"
                >
                    <svg
                        class="w-4 h-4 font-medium"
                        data-slot="icon"
                        fill="none"
                        stroke-width="1.5"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 4.5v15m7.5-7.5h-15"
                        ></path>
                    </svg>
                     {__("New Product", "easycommerce")}
                 </button>
                  <button
                      onClick={() => setShowImportModal(true)}
                      className="ml-auto mt-2 text-ec-primary underline text-base font-medium px-2 py-1 rounded"
                  >
                      {__("Import Products", "easycommerce")}
                  </button>
             </div>
            <div className="w-full bg-white border border-solid border-ec-table-stock rounded-xl p-6 min-h-screen flex flex-col h-[94%]">
                {isStatusLoaded && (
                    <div className="flex justify-between gap-5 mb-4">
                        {selectedProducts.length > 0 ? (
                            <ProductActionBar
                                selectedProducts={selectedProducts}
                                setSelectedProducts={setSelectedProducts}
                                setProducts={setProducts}
                                fetchProductsStatuses={fetchProductsStatuses}
                                productSetStatusCounts={productSetStatusCounts}
                            />
                        ) : (
                            <div className="flex flex-wrap items-center gap-4">
                            <div className="flex flex-wrap gap-4 border-b-2 border-[#F0EDFB]">
                                {tabOptions.map((tab) => {
                                    const isActive = activeTab === tab.key;
                                    const count = tabCounts[tab.key] ?? 0;
                                    return (
                                        <button
                                            key={tab.key}
                                            onClick={() => {
                                                setActiveTab(tab.key);
                                                localStorage.setItem(
                                                    "easycommerce_products_activeTab",
                                                    tab.key
                                                );
                                                window.location.hash =
                                                    "#/products";
                                            }}
                                            className={`relative flex items-center gap-[3px] font-inter text-sm text-ec-body transition-colors duration-300 
                                                ${
                                                    isActive
                                                        ? "after:absolute after:bottom-[-2px] after:left-0 after:right-0 after:h-[2px] after:bg-ec-primary"
                                                        : ""
                                                }`}>
                                            <span>{tab.label}</span>
                                            <span
                                                className={`text-xs font-medium px-2 py-0.5 rounded-full ${tab.bg}`}>
                                                {count}
                                            </span>
                                        </button>
                                    );
                                })}
                            </div>
                            {demoCount > 0 && (
                                <button
                                    onClick={() => setShowSampleModal(true)}
                                    className="flex h-[34px] justify-center items-center font-inter bg-white border border-[#FF3A52] px-3 py-1.5 rounded-lg text-[#FF3A52] text-sm hover:text-white hover:bg-[#FF3A52] transition-all ease-in-out duration-500"
                                >
                                    Delete Demo Products
                                </button>
                            )}
                            </div>
                        )}
                        <ProductTableFilter
                            categories={categories}
                            formState={formState}
                            productsFiltered={productsFiltered}
                            setFormState={setFormState}
                            filterProducts={() => {
                                filterProducts();
                            }}
                            resetFilter={resetFilter}
                        />
                    </div>
                )}
                {isLoading ? (
                    <div className="flex flex-col gap-8 m-[15px] mb-10 rounded-2xl">
                        <TableSkeleton
                            numberOfRows={15}
                            SkeletonHeight={30}
                        />
                    </div>
                ) : (
                    <>
                        {products.length > 0 ? (
                            <div className="flex flex-col rounded-2xl h-full">
                                <ProductTable
                                    tableColumns={tableColumns}
                                    products={products}
                                    setProductIdToDelete={setProductIdToDelete}
                                    setForceDelete={setForceDelete}
                                    setShowModal={setShowModal}
                                    restoreProduct={restoreProduct}
                                    selectedProducts={selectedProducts}
                                    handleSelectOneProduct={handleSelectOneProduct}
                                    handleSelectAllProducts={handleSelectAllProducts}
                                    setProducts={setProducts}
                                    productSetStatusCounts={productSetStatusCounts}
                                    setProductToDeleteStatus={setProductToDeleteStatus}
                                />

                                {totalPage > 1 && (
                                    <Pagination
                                        baseSlug="products"
                                        current={page}
                                        total={totalPage}
                                    />
                                )}

                                {showModal && (
                                    <DeletePopup
                                            onClose={() => {
                                            setShowModal(false);
                                            setProductIdToDelete(null);
                                            setForceDelete(false)
                                        }}
                                        onConfirm={deleteAction}
                                        onPermanentDelete={deleteNow}
                                        itemName="Product"
                                        forceDelete={forceDelete}
                                        isProduct={true}
                                    />
                                )}
                            </div>
                        ) : (
                            <>
                                <NotFound
                                    ImageUrl={noProduct}
                                    btnText={__("Add New Product", "easycommerce")}
                                    title={
                                        activeTab !== 'all'
                                            ? // translators: %s: product status label, e.g. Draft or Published.
                                            sprintf(__("No %s Products Found", "easycommerce"), tabOptions.find(tab => tab.key === activeTab)?.label || activeTab)
                                            : __("Your Store is Empty", "easycommerce")
                                    }
                                    description={__("Create a new product or generate samples to get started.", "easycommerce")}
                                    isBtn={!hasAnyProducts}
                                    btnCallBack={handleAddProduct}
                                >
                                    {!hasAnyProducts && (
                                        <>
                                            <span className="text-ec-light-black text-center font-inter font-normal text-base leading-6 pt-[4px] px-2"> {__("or", "easycommerce")} </span>
                                            <button 
                                                className="font-inter py-[8px] px-4 border border-ec-primary rounded-lg font-medium text-ec-primary text-base capitalize hover:bg-ec-primary hover:text-white transition-all ease-in-out duration-500"
                                                onClick={() => setShowSampleModal(true)}
                                            >
                                                {__("Generate Samples", "easycommerce")}
                                            </button>
                                        </>
                                    )}
                                </NotFound>
                            </>
                         )}

                        {showImportModal && (
                            <ImportModal
                                onClose={() => setShowImportModal(false)}
                                onImportComplete={() => {
                                    setShowImportModal(false);
                                    fetchProducts();
                                    fetchProductsStatuses();
                                }}
                            />
                        )}

                        {showSampleModal && (
                            <SampleProductsModal
                                demoCount={demoCount}
                                hideModal={() => setShowSampleModal(false)}
                                onImport={() => {
                                    setShowSampleModal(false);
                                    setIsImporting(true);
                                }}
                                onDeleted={() => {
                                    fetchProducts();
                                    fetchProductsStatuses();
                                    fetchDemoCount();
                                }}
                            />
                        )}
                     </>
                 )}
             </div>
 		</>
 	);
 };

export default Products;