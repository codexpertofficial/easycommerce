import React from "react";
import { __ } from "@wordpress/i18n";

const THead = ({ columnList, allChecked, toggleAll }) => {
	return (
		<thead>
			<tr className="h-11">
				{columnList.map((column, index) => {
					if (column.key === "email") {
						return (
							<th
								key={column.key}
								className="w-[28%] px-5 py-3 bg-ec-modal border-0 rounded-l-lg"
							>
								<div className="flex items-center">
									<input
										type="checkbox"
										checked={allChecked}
										onChange={(e) => toggleAll(e.target.checked)}
										className="min-w-5 h-5 accent-ec-primary cursor-pointer mr-2 easycommerce-input-checkoutbox pl-2"
									/>
									<span className="ml-4 font-inter font-normal text-sm text-ec-title rtl:mr-4">
										{column.label}
									</span>
								</div>
							</th>
						);
					}

					return (
						<th
							key={column.key}
							className={`${column.key === "product" ? "w-[28%]" : "w-[14%]"} ${
								index === columnList.length - 1 ? "rounded-r-lg" : ""
							}
							text-left text-ec-title font-inter font-normal text-sm leading-[26px]
							pl-5 bg-ec-modal border-0 rtl:text-right`}
						>
							{column.label}
						</th>
					);
				})}
			</tr>
		</thead>
	);
};

export default THead;
