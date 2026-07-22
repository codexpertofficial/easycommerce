import React from "react";
import { __ } from "@wordpress/i18n";

const dummyImageUrl = `${EASYCOMMERCE.assets}admin/img/icons/demo-image.png`;

const TBody = ({ order }) => {
    return (
        <tbody>
            {order.items.map((item, index) => (
                <tr
                    key={index}
                    className={`grid grid-cols-12 gap-0 p-0 ${
                        index === order.items.length - 1
                            ? "border-b-2 border-ec-table-stock" 
                            : "border-b border-ec-table-stock"
                    }`}
                >
                    {item.product && item.meta.name ? (
                        <td
                            className={`col-span-8 p-0 pl-6 border-0 ${
                                index === 0 ? "pt-3" : "pt-3"
                            } ${
                                index === order.items.length - 1
                                    ? "pt-3 pb-3"
                                    : "pb-3"
                            }`}
                        >
                            <span className="flex justify-start items-start gap-3">
                                <span className="w-[65px] h-[65px] rounded border border-ec-table-stock">
                                    
                                    <img
                                        src={
                                            item?.product?.thumbnail?.url  ||
                                            dummyImageUrl
                                        }
                                        
                                        alt={item.variation.name}
                                        className="w-full h-full object-cover rounded pointer-events-none"
                                    />
                                </span>
                                <span className="flex flex-col gap-2">
                                    <span className="font-inter font-medium text-sm  text-ec-title">
                                        {item.meta.name}
                                    </span>
                                    <span className="font-inter text-xs font-normal text-ec-body">
                                        {item.meta.attributes}
                                    </span>
                                    <span className="font-inter font-normal text-xs leading-4 text-ec-body">
                                        {item.meta.price}
                                    </span>
                                </span>
                            </span>
                        </td>
                    ) : (
                        <td className="col-span-8 pt-3 pb-3"></td>
                    )}

                    {item.quantity ? (
                        <td
                            className={`col-span-2 flex justify-end items-center text-right p-0 pr-6 border-0 ${
                                index === 0 ? "pt-3" : "pt-3"
                            } ${
                                index === order.items.length - 1
                                    ? "pt-3 pb-3"
                                    : "pb-3"
                            }`}
                        >
                            <span className="font-inter font-normal text-sm leading-[26px] text-ec-body">
                                &times; {item.quantity}
                            </span>
                        </td>
                    ) : (
                        <td className="col-span-2 pt-3 pb-3"></td>
                    )}

                    {item.price ? (
                        <td
                            className={`col-span-2 flex justify-end items-center text-right p-0 pr-6 border-0 ${
                                index === 0 ? "pt-3" : "pt-3"
                            } ${
                                index === order.items.length - 1
                                    ? "pt-3 pb-3"
                                    : "pb-3"
                            }`}
                        >
                            <span className="font-inter font-normal text-sm leading-[26px] text-ec-body">
                                {item.price}
                            </span>
                        </td>
                    ) : (
                        <td className="col-span-2 pt-3 pb-3"></td>
                    )}
                </tr>
            ))}
            {order.subtotal ? (
                <tr className="grid grid-cols-12 gap-0 p-0 border-b border-dashed border-ec-table-stock">
                    <td className="col-span-8 p-0 pt-4 pb-[10px] pl-4 text-left border-0">
                        <span className="font-inter font-normal text-base leading-[26px] text-ec-title">
                            {__("Price", "easycommerce")}
                        </span>
                    </td>
                    <td className="col-span-2 p-0 pt-4 pb-[10px] border-0"></td>
                    <td className="col-span-2 p-0 pt-4 pb-[10px] pr-4 text-right border-0">
                        <span className="font-inter font-normal text-sm leading-[26px] text-ec-body">
                            {order.subtotal}
                        </span>
                    </td>
                </tr>
            ) : null}
            {order.discount && order.discount > 0 ? (
                <tr className="grid grid-cols-12 gap-0 p-0 border-b border-dashed border-ec-table-stock">
                    <td className="col-span-8 p-0 py-[10px] pl-4 text-left border-0">
                        <span className="font-inter font-normal text-base leading-[26px] text-ec-title">
                            {__("Discount", "easycommerce")}
                        </span>
                    </td>
                    <td className="col-span-2 p-0 py-[10px] border-0"></td>
                    <td className="col-span-2 p-0 py-[10px] pr-4 text-right border-0">
                        <span className="font-inter font-normal text-sm leading-[26px] text-ec-body">
                            {order.discount_formatted}
                        </span>
                    </td>
                </tr>
            ) : null}
            {order.shipping && order.shipping > 0 ? (
                <tr className="grid grid-cols-12 gap-0 p-0 border-b border-dashed border-ec-table-stock">
                    <td className="col-span-8 p-0 py-[10px] pl-4 text-left border-0">
                        <span className="font-inter font-normal text-base leading-[26px] text-ec-title">
                            {__("Shipping", "easycommerce")}
                        </span>
                    </td>
                    <td className="col-span-2 p-0 py-[10px] border-0"></td>
                    <td className="col-span-2 p-0 py-[10px] pr-4 text-right border-0">
                        <span className="font-inter font-normal text-sm leading-[26px] text-ec-body">
                            {order.shipping_formatted}
                        </span>
                    </td>
                </tr>
            ) : null}

            {order.product_tax && order.product_tax > 0 ? (
                <tr className="grid grid-cols-12 gap-0 p-0 border-b border-dashed border-ec-table-stock">
                    <td className="col-span-8 p-0 py-[10px] pl-4 text-left border-0">
                        <span className="font-inter font-normal text-base leading-[26px] text-ec-title">
                            {__("Product Tax", "easycommerce")}
                        </span>
                    </td>
                    <td className="col-span-2 p-0 py-[10px] border-0"></td>
                    <td className="col-span-2 p-0 py-[10px] pr-4 text-right border-0">
                        <span className="font-inter font-normal text-sm leading-[26px] text-ec-body">
                            {order.product_tax_formatted}
                        </span>
                    </td>
                </tr>
            ) : null}

            {order.shipping_tax && order.shipping_tax > 0 ? (
                <tr className="grid grid-cols-12 gap-0 p-0 border-b border-dashed border-ec-table-stock">
                    <td className="col-span-8 p-0 py-[10px] pl-4 text-left border-0">
                        <span className="font-inter font-normal text-base leading-[26px] text-ec-title">
                            {__("Shipping Tax", "easycommerce")}
                        </span>
                    </td>
                    <td className="col-span-2 p-0 py-[10px] border-0"></td>
                    <td className="col-span-2 p-0 py-[10px] pr-4 text-right border-0">
                        <span className="font-inter font-normal text-sm leading-[26px] text-ec-body">
                            {order.shipping_tax_formatted}
                        </span>
                    </td>
                </tr>
            ) : null}
        </tbody>
    );
};

export default TBody;
