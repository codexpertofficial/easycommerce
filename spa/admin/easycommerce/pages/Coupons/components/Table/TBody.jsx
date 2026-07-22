import React from "react";
import { __ } from "@wordpress/i18n";
import CopyButton from "../../../../../common/CopyButton";
import CouponStatusDropdown from "../CouponStatusDropdown";
import ActionDropdown from "../ActionDropdown";

const TBody = ({ coupons, setCoupons, columnList, deleteCoupon, onCouponStatusChange, selectedCoupons, toggleCoupon }) => {
	return (
		<tbody>
			{coupons.map((coupon, index) => (
			<tr
				key={index}
				className={'border-b border-ec-table-stock h-[80px] transition-shadow hover:shadow-[0px_4px_40px_0px_#00000014] group/coupons'}
			>
  
					{columnList.map((column) => {
						if (column.key === "name") {
							return (
								<td key="name" className="p-5 flex items-center">
									<div className="flex items-center gap-4 justify-start mt-1 w-full">
										<input
											type="checkbox"
											checked={selectedCoupons.includes(coupon.id)}
											onChange={() => toggleCoupon(coupon.id)}
											className="min-w-5 h-5 accent-ec-primary cursor-pointer mr-2 easycommerce-input-checkoutbox pl-2"
										/>

										<div className="flex items-center gap-4 focus:shadow-none grow">
											<div className="block w-full h-10 relative">
												<span className="text-sm text-ec-body font-inter font-normal absolute top-1/2 -translate-y-1/2 group-hover/coupons:top-0 group-hover/coupons:translate-y-0 duration-300">
													{coupon.name}
												</span>
												<div className="invisible group-hover/coupons:visible opacity-0 group-hover/coupons:opacity-100 duration-300 absolute bottom-0">
													<div className="flex items-center gap-1.5 font-inter font-normal text-xs text-ec-light-black">
														<a className="hover:text-ec-primary duration-300 hover:cursor-pointer" onClick={() => (window.location.hash = `#/coupons/edit/${coupon.id}`)} >{ __( "Edit", "easycommerce" ) }</a>
														<span className="text-[#bdbdbd]">|</span>
														<button
															onClick={() => deleteCoupon(coupon.id, coupon.name)}
															className="text-ec-red"
														>
															{ __( "Delete", "easycommerce" ) }
														</button>
													</div>
												</div>
											</div>
										</div>
									</div>
								</td>
							
							);
						}

						if (column.key === "code") {
							return (
								<td key="coupon-code">
									<div className="flex items-center justify-start gap-3 p-5">
										<span className="text-sm text-ec-body font-inter font-normal">
											{coupon.code}
										</span>
										<CopyButton copy={coupon.code} />
									</div>
								</td>
							);
						}

						if (column.key === "type") {
							return (
								<td key="type" className="text-sm text-ec-body font-inter font-normal capitalize p-5">
									{coupon.type.replaceAll("_", " ")}
								</td>
							);
						}

						if (column.key === "offer") {
							return (
								<td key="offer" className="text-sm text-ec-body font-inter font-normal capitalize p-5">
									{coupon.type === "fixed" && EASYCOMMERCE.currency_symbol }
									{coupon.type !== "products" && coupon.offer}
                                    {coupon.type === "percentage" && "%"}
                                    {coupon.type === "products" && [].map.call(coupon.offer, (product) => product.title).join(", ")}
                                    {coupon.type === "free_shipping" && __( "Free Shipping", "easycommerce" )}
								</td>
							);
						}

						if (column.key === "usage") {
							return (
								<td key="usage" className="text-sm text-ec-body font-inter font-normal capitalize p-5">
									{coupon.usage}
								</td>
							);
						}

						if (column.key === "status") {
							return (
								<td key="status" className="p-5">
									<CouponStatusDropdown
										value={coupon.active ? "active" : "inactive"}
										couponId={coupon.id}
										onStatusChange={() => {
											if (typeof onCouponStatusChange === "function") {
												onCouponStatusChange();
											}
										}}
									/>
								</td>
							);
						}

						return null;
					})}
				</tr>
			))}
		</tbody>
	);
};

export default TBody;
