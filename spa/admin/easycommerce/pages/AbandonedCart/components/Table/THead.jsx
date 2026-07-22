import React from "react";
import { __ } from "@wordpress/i18n";

const THead = ({ columnList, allChecked, toggleAll }) => {
	return (
		<thead>
			<tr className="h-11">
				{columnList.map((column) => {
					if (column.key === "name") {
						return (
							<th
								key={column.key}
								className="w-[25%] px-5 py-3 bg-ec-modal border-0 rounded-l-lg"
							>
								<div className="flex items-center">
									<input
										type="checkbox"
										checked={allChecked}
										onChange={(e) => toggleAll(e.target.checked)}
										className="min-w-5 h-5 accent-ec-primary cursor-pointer mr-2 easycommerce-input-checkoutbox pl-2"
									/>
									<span className="ml-4 font-inter font-normal text-sm text-ec-title rtl:mr-4">
										{__("Name", "easycommerce")}
									</span>
								</div>
							</th>
						);
					} else {
						return (
							<th
								key={column.key}
								className={`${
									column.key === "email" ? "w-1/4" : column.key === "items" ? "w-[20%]" : "w-[10%]"
								} ${
									column.key === "actions" ? "rounded-r-lg" : ""
								}
								text-left text-ec-title font-inter font-normal text-sm leading-[26px]
								pl-5 bg-ec-modal border-0 rtl:text-right`}
							>
								{column.label}
							</th>
						);
					}
				})}
			</tr>
		</thead>
	);
};

export default THead;
