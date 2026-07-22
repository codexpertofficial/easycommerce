import React from "react";
import { Slot } from "@wordpress/components";
import { __, sprintf } from "@wordpress/i18n";

const dummyImageUrl = `${EASYCOMMERCE.assets}admin/img/icons/demo-image.png`;

const ItemsTable = ({ order }) => {
    return (
        <table className="w-full border-0 m-0">
            <thead>
                <tr className="easycommerce-dashboard-order-iteam-wrap grid grid-cols-12 gap-0">
                    <th className="col-span-8 font-inter font-semibold text-base md:text-xl text-left rtl:text-right leading-8 text-ec-body p-0 pl-4 pb-6 border-0 border-b border-ec-border">
                        {__( "Items", "easycommerce" )}
                    </th>
                    <th className="col-span-2 font-inter font-semibold text-base md:text-xl text-right leading-8 text-ec-body p-0 pb-6 border-0 border-b border-ec-border">
                        {__( "Quantity", "easycommerce" )}
                    </th>
                    <th className="col-span-2 font-inter font-semibold text-base md:text-xl text-right leading-8 text-ec-body p-0 pr-4 pb-6 border-0 border-b border-ec-border">
                        {__( "Total", "easycommerce" )}
                    </th>
                </tr>
            </thead>

            <tbody>
                {order.items.map((item, index) => (
                    <>
                        <tr key={index} className="grid grid-cols-12 gap-0 p-0">
                            {item.product && item.product.name ? (
                                <td
                                    className={`col-span-8 p-0 pl-4 border-0 ${
                                        index === 0 ? "pt-5" : ""
                                    } ${
                                        index === order.items.length - 1
                                            ? "pt-[10px] pb-5 border-b border-ec-border"
                                            : "pb-[10px]"
                                    }`}
                                >
                                    <span className="flex justify-start items-start gap-7">
                                        <span className="w-[80px] h-[80px] shrink-0">
                                            <img
                                                src={
                                                    item?.variation?.thumbnail?.url ||
                                                    item?.product?.thumbnail?.url ||
                                                    dummyImageUrl
                                                }
                                                alt={item?.variation?.name || item?.product?.name || __( "Product Image", "easycommerce" )}
                                                className="w-full h-full rounded-xl border border-ec-border object-cover bg-ec-accent pointer-events-none"
                                            />
                                        </span>
                                        <span className="flex flex-col gap-3">
                                            <span className="font-inter font-medium text-base leading-[26px] text-ec-body">
                                                {item.product.name}
                                            </span>
                                            <span className="font-inter text-sm text-ec-body">
                                                {item.variation.name}
                                            </span>
                                            <span className="font-inter font-normal text-sm leading-4 text-ec-placeholder">
                                                { ! item.meta.is_free && item.rate }
                                                { item.meta.is_free && (
                                                    <>
                                                        <del className="text-ec-placeholder mr-2">{item.rate}</del>
                                                        {__( "Free Product", "easycommerce" )}
                                                    </>
                                                )}
                                            </span>

                                            <Slot name={`easycommerce-customer-dashboard/after-order-item_${index}`} fillProps={{ item: item, order_id: order.id }} />
                                        </span>

                                        {item.variation.downloads?.length > 0 && (
                                            <a
                                                href={item.variation.downloads[0].secure_url}
                                                download
                                                className="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-ec-primary no-underline hover:gap-2 transition-all"
                                            >
                                                <svg className="w-4 h-4" data-slot="icon" fill="none" stroke="currentColor" strokeWidth="1.8" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                </svg>
                                                {
                                                    // translators: %s: downloadable file name.
                                                    sprintf( __( "Download %s", "easycommerce" ), item.variation.downloads[0].filename )
                                                }
                                            </a>
                                        )}
                                    </span>
                                </td>
                            ) : (
                                <td className="col-span-8 border-0 border-b border-ec-border"></td>
                            )}

                            {item.quantity ? (
                                <td
                                    className={`col-span-2 flex justify-end items-center text-right rtl:text-left rtl:justify-start p-0 border-0 ${
                                        index === 0 ? "pt-5" : ""
                                    } ${
                                        index === order.items.length - 1
                                            ? "pt-[10px] pb-5 border-b border-ec-border"
                                            : "pb-[10px]"
                                    }`}
                                >
                                    <span className="font-inter font-medium text-base leading-[26px] text-ec-placeholder">
                                        &times;{item.quantity}
                                    </span>
                                </td>
                            ) : (
                                <td className="col-span-2 border-0 border-b border-ec-border"></td>
                            )}

                            {item.price ? (
                                <td
                                    className={`col-span-2 flex justify-end items-center text-right rtl:text-left rtl:justify-start p-0 pr-4 border-0 ${
                                        index === 0 ? "pt-5" : ""
                                    } ${
                                        index === order.items.length - 1
                                            ? "pt-[10px] pb-5 border-b border-ec-border"
                                            : "pb-[10px]"
                                    }`}
                                >
                                    <span className="font-inter font-medium text-base leading-[26px] text-ec-placeholder">
                                        {item.price}
                                    </span>
                                </td>
                            ) : (
                                <td className="col-span-2 border-0 border-b border-ec-border"></td>
                            )}
                        </tr>
                    </>
                ))}

                {order.subtotal ? (
                    <tr className="grid grid-cols-12 gap-0 p-0">
                        <td className="col-span-8 p-0 pt-4 pb-[10px] pl-4 text-left rtl:text-right border-0">
                            <span className="font-inter font-medium text-base leading-[26px] text-ec-placeholder">
                                {__( "Subtotal", "easycommerce" )}
                            </span>
                        </td>
                        <td className="col-span-2 p-0 pt-4 pb-[10px] border-0"></td>
                        <td className="col-span-2 p-0 pt-4 pb-[10px] pr-4 text-right border-0">
                            <span className="font-inter font-medium text-base leading-[26px] text-ec-body">
                                {order.subtotal}
                            </span>
                        </td>
                    </tr>
                ) : null}

                {order.discount && order.discount > 0 ? (
                    <tr className="grid grid-cols-12 gap-0 p-0">
                        <td className="col-span-8 p-0 py-[10px] pl-4 text-left rtl:text-right border-0">
                            <span className="font-inter font-medium text-base leading-[26px] text-ec-placeholder">
                                {__( "Discount", "easycommerce" )}
                            </span>
                        </td>
                        <td className="col-span-2 p-0 py-[10px] border-0"></td>
                        <td className="col-span-2 p-0 py-[10px] pr-4 text-right border-0">
                            <span className="font-inter font-medium text-base leading-[26px] text-ec-placeholder">
                                {order.discount_formatted}
                            </span>
                        </td>
                    </tr>
                ) : null}

                {order.shipping && order.shipping > 0 ? (
                    <tr className="grid grid-cols-12 gap-0 p-0">
                        <td className="col-span-8 p-0 py-[10px] pl-4 text-left rtl:text-right border-0">
                            <span className="font-inter font-medium text-base leading-[26px] text-ec-placeholder">
                                {__( "Shipping", "easycommerce" )}
                            </span>
                        </td>
                        <td className="col-span-2 p-0 py-[10px] border-0"></td>
                        <td className="col-span-2 p-0 py-[10px] pr-4 text-right border-0">
                            <span className="font-inter font-medium text-base leading-[26px] text-ec-body">
                                {order.shipping_formatted}
                            </span>
                        </td>
                    </tr>
                ) : null}

                {order.product_tax && order.product_tax > 0 ? (
                    <tr className="grid grid-cols-12 gap-0 p-0">
                        <td className="col-span-8 p-0 pt-[10px] pb-4 pl-4 text-left rtl:text-right border-0">
                            <span className="font-inter font-medium text-base leading-[26px] text-ec-placeholder">
                                {__( "Product Tax", "easycommerce" )}
                            </span>
                        </td>
                        <td className="col-span-2 p-0 py-[10px] border-0"></td>
                        <td className="col-span-2 p-0 py-[10px] pr-4 text-right border-0">
                            <span className="font-inter font-medium text-base leading-[26px] text-ec-body">
                                {order.product_tax_formatted}
                            </span>
                        </td>
                    </tr>
                ) : null}

                {order.shipping_tax && order.shipping_tax > 0 ? (
                    <tr className="grid grid-cols-12 gap-0 p-0">
                        <td className="col-span-8 p-0 py-[10px] pl-4 text-left border-0">
                            <span className="font-inter font-medium text-base leading-[26px] text-ec-placeholder">
                                {__( "Shipping Tax", "easycommerce" )}
                            </span>
                        </td>
                        <td className="col-span-2 p-0 py-[10px] border-0"></td>
                        <td className="col-span-2 p-0 py-[10px] pr-4 text-right border-0">
                            <span className="font-inter font-medium text-base leading-[26px] text-ec-body">
                                {order.shipping_tax_formatted}
                            </span>
                        </td>
                    </tr>
                ) : null}
            </tbody>

            <tfoot>
                <tr className="grid grid-cols-12 gap-0 p-0">
                    <td className="col-span-8 p-0 pt-5 pl-4 text-left  border-0 border-t border-ec-border rtl:text-right">
                        <span className="font-inter font-bold text-base leading-[26px] text-ec-body">
                            {__( "Total", "easycommerce" )}
                        </span>
                    </td>
                    <td className="col-span-2 p-0 pt-5 border-0 border-t border-ec-border"></td>
                    <td className="col-span-2 p-0 pt-5 pr-4 text-right border-0 border-t border-ec-border">
                        <span className="font-inter font-bold text-base leading-[26px] text-ec-primary">
                            {order.total}
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
    );
};

export default ItemsTable;
