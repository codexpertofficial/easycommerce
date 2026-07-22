import React, { useEffect, useState } from "react";
import { __ } from "@wordpress/i18n";
import ProductActionDropdown from "./ProductActionDropdown";
import ProductStatusDropdown from "./ProductStatusDropdown";
import ProductShowBulkOptions from "./ProductShowBulkOptions";

// icons
const View = `${EASYCOMMERCE.assets}admin/img/icons/View.png`;
const Delete = `${EASYCOMMERCE.assets}admin/img/icons/delete.png`;
const Edit = `${EASYCOMMERCE.assets}admin/img/icons/edit.png`;
const Builder = `${EASYCOMMERCE.assets}admin/img/icons/builder.png`;
const Restore = `${EASYCOMMERCE.assets}admin/img/icons/builder.png`;
const RedDelete = `${EASYCOMMERCE.assets}admin/img/icons/delete-hover.png`;
const DemoImage = `${EASYCOMMERCE.assets}admin/img/icons/demo-image-hd.png`;

const ProductTable = ({
    tableColumns,
    products,
    setProductIdToDelete,
    setForceDelete,
    setShowModal,
    restoreProduct,
    selectedProducts,
    handleSelectOneProduct,
    handleSelectAllProducts,
    setProducts,
    productSetStatusCounts,
    setProductToDeleteStatus
}) => {

    const [bulkProductStatus, setBulkProductStatus] = useState(null);
    const [productBulkCountChange, setProductBulkCountChange] = useState(null);
    const productStatusOptions = [
        { label: __("Live", "easycommerce"), value: "publish" },
        { label: __("Draft", "easycommerce"), value: "draft" },
        { label: __("Trash", "easycommerce"), value: "trash" },
    ];
    const allSelected = Array.isArray(products) && Array.isArray(selectedProducts) && products.length > 0 && selectedProducts.length === products.length;

    useEffect(() => {
            if (productBulkCountChange !== null) {
                productSetStatusCounts(productBulkCountChange);
            }
        }, [productBulkCountChange, productSetStatusCounts]);

    return (
 		<div className="w-full overflow-x-auto h-full">
			<table className="min-w-full w-full border-collapse border-spacing-0"> 
                <thead>
                    <tr className="h-auto">
                        {tableColumns.includes("title") && (
                            <th className="p-3 pl-5 flex bg-ec-modal items-center justify-start gap-4 rounded-l-md border-r-0">
                                <input
                                    type="checkbox"
                                    checked={allSelected}
                                    onChange={(e) => handleSelectAllProducts(e.target.checked)}
                                    className="min-w-5 h-5 accent-ec-primary cursor-pointer mr-2 easycommerce-input-checkoutbox pl-2"
                                />
                                <span className="font-inter font-normal text-sm text-ec-title">
                                    {__("Product Name", "easycommerce")}
                                </span>
                            </th>
                        )}
                        {tableColumns.includes("status") && (
                            <th className="font-inter font-normal bg-ec-modal text-sm text-ec-title text-left xl:w-[15%] lg:w-[10%] rtl:text-right">
                                {__("Status", "easycommerce")}
                            </th>
                        )}
                        {tableColumns.includes("category") && (
                            <th className="font-inter font-normal bg-ec-modal text-sm text-ec-title text-left w-[20%] lg:w-[12%] rtl:text-right">
                                {__("Category", "easycommerce")}
                            </th>
                        )}
                        {tableColumns.includes("price") && (
                            <th className="font-inter font-normal bg-ec-modal text-sm text-ec-title text-left lg:w-[8%] rtl:text-right">
                                {__("Price", "easycommerce")}
                            </th>
                        )}
                        {tableColumns.includes("stock") && (
                            <th className="font-inter font-normal bg-ec-modal text-sm text-ec-title text-left lg:w-[10%] rtl:text-right">
                                {__("Quantity", "easycommerce")}
                            </th>
                        )}
                        {tableColumns.includes("sales") && (
                            <th className="font-inter font-normal bg-ec-modal text-sm text-ec-title text-left lg:w-[10%] rounded-r-md rtl:text-right">
                                {__("Total Sale", "easycommerce")}
                            </th>
                        )}
                    </tr>
                </thead>
                <tbody>
                    {products.map((product) => (
                        <tr
                            key={product.id}
                            className="border-b border-ec-table-stock h-[80px] transition-shadow hover:shadow-[0px_4px_40px_0px_#00000014] group"
                        >
                            {tableColumns.includes("title") && (
                                <td className="leading-[26px] pl-5 w-[30%] rtl:pr-5">
                                    <div className="flex items-center gap-4 lg:gap-3 justify-start mt-1 w-full">
                                        <input
                                            type="checkbox"
                                            checked={Array.isArray(selectedProducts) && selectedProducts.includes(product.id)}

                                            onChange={() => handleSelectOneProduct(product.id)}
                                            className="min-w-5 h-5 accent-ec-primary cursor-pointer mr-2 easycommerce-input-checkoutbox pl-2"
                                        />
                                        <div className="flex items-center gap-4 lg:gap-3 focus:shadow-none grow">
                                            <img
                                                src={
                                                    product?.thumbnail?.url ||
                                                    DemoImage
                                                }
                                                alt={product.title}
                                                className="w-10 h-10 inline-block rounded-md pointer-events-none p-[3px] border border-solid border-ec-table-stock"
                                            />
                                            <div className="block w-full h-10 relative">
                                                <span className="text-sm text-ec-body font-inter font-normal absolute top-1/2 -translate-y-1/2 group-hover:top-0 group-hover:translate-y-0 duration-300">
                                                    {product.title.length > 35 ? product.title.slice(0, 35) + "..." : product.title}
                                                </span>
                                                <div className="invisible group-hover:visible opacity-0 group-hover:opacity-100 duration-300 absolute bottom-0">
                                                    <div className="flex items-center gap-1.5 font-inter font-normal text-xs text-ec-light-black">
                                                        {product.status === "trash" ? (
                                                            <button className="hover:text-ec-primary duration-300" onClick={() => restoreProduct(product.id, product.title)}>{__("Restore", "easycommerce")}</button>
                                                        ) : (
                                                            <>
                                                                <a className="hover:text-ec-primary duration-300" href={`#/products/edit/${product.id}`}>{__("Edit", "easycommerce")}</a>
                                                                <span className="text-[#bdbdbd]">|</span>
                                                                <a className="hover:text-ec-primary duration-300" href={`${EASYCOMMERCE.product_edit_base}=${product.id}`}>{__("Builder", "easycommerce")}</a>
                                                                <span className="text-[#bdbdbd]">|</span>
                                                                <a className="hover:text-ec-primary duration-300" href={`${product.link}`}>{__("View", "easycommerce")}</a>
                                                            </>
                                                        )}
                                                        
                                                        <span className="text-[#bdbdbd]">|</span>
                                                        <button 
                                                            onClick={() => {
                                                                setProductIdToDelete(product.id);
                                                                setProductToDeleteStatus(product.status);
                                                                setShowModal(true);
                                                                if (product.status === "trash") setForceDelete(true);
                                                            }}
                                                            className="text-ec-red"
                                                        >
                                                            {__("Delete", "easycommerce")}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            )}
                            {tableColumns.includes("status") && (
                                <td className="w-[15%] py-4 pr-3 lg:pr-0">
                                    <ProductStatusDropdown
                                        options={productStatusOptions}
                                        productId={product.id}
                                        value={bulkProductStatus !== null ? bulkProductStatus : product?.status}
                                        placeholder={__("Select status", "easycommerce")}
                                        width="90px"
                                        menuWidth="120px"
                                        productTitle={product.title}
                                        prevStatus={product?.status}
                                        onStatusChange={(prev, next, id) => {
                                            productSetStatusCounts((counts) => {
                                                const updated = { ...counts };
                                                if (prev && updated[prev] > 0) {
                                                    updated[prev] -= 1;
                                                }
                                                if (next) {
                                                    updated[next] = (updated[next] || 0) + 1;
                                                }
                                                return updated;
                                            });
                                            setProducts((prev) =>
                                                prev.map((product) =>
                                                    product.id === id ? { ...product, status: next } : product
                                                )
                                            );
                                        }}
                                    />
                                </td>
                            )}

                            {tableColumns.includes("category") && (
                                <td className="w-[20%] py-4 pr-3 lg:pr-0">
                                    <span className="flex flex-col min-[1500px]:flex-row min-[1500px]:items-center gap-1 items-start">
                                        {product.categories.length > 0 && (
                                            <>
                                                {product.categories.map(
                                                    (category, index) =>
                                                        index < 2 && (
                                                            <span
                                                                key={index}
                                                                className="font-inter text-[14px] text-ec-body font-normal"
                                                            >
                                                                {category.name}
                                                                {index < product.categories.length - 1 && index < 1 && ", "}
                                                            </span>
                                                        )
                                                )}
                                            </>
                                        )}
                                    </span>
                                </td>
                            )}
                            {tableColumns.includes("price") && (
                                <td className="font-inter font-normal text-sm text-ec-body pr-3 lg:pr-0 w-[15%] py-4 ">
                                    {(!product.sale_price || parseFloat(
                                        typeof product.sale_price === "string"
                                            ? product.sale_price.replace(/[^0-9.]/g, "")
                                            : product.sale_price
                                    ) === 0)
                                        ? product.price
                                        : product.sale_price}
                                </td>
                            )}
                            {tableColumns.includes("stock") && (
                                <td className="font-inter font-normal text-sm text-ec-body pr-3 lg:pr-0 w-[10%] py-4 ">
                                    {product.stock}
                                </td>
                            )}
                            {tableColumns.includes("sales") && (
                                <td className="font-inter font-normal text-sm text-ec-body pl-2 pr-3 lg:pr-0 w-[10%] py-4 ">
                                    {product.sales}
                                </td>
                            )}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default ProductTable;