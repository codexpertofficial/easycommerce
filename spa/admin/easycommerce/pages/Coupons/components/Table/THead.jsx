import React from "react";
import { __ } from "@wordpress/i18n";

const THead = ({ columnList, allChecked, toggleAll }) => {
	return (
		<thead>
			<tr className="h-auto">
				{columnList.map((column) => {
					if (column.key === "name") {
						return (
							<th
								key={column.key}
								className="p-3 pl-5 bg-ec-modal border-0 flex items-center rounded-l-lg [15%]"
							>
								<input
									type="checkbox"
									className="min-w-5 h-5 accent-ec-primary cursor-pointer mr-2 easycommerce-input-checkoutbox pl-2"
									checked={allChecked}
									onChange={toggleAll}
								/>

								<span className="ml-2 font-inter font-normal text-sm text-ec-title rtl:mr-2">
									{ __( "Name", "easycommerce" ) }
								</span>

							</th>
						);
					} else {
						return (
							<th
								key={column.key}
								className={`${
									column.key === "code" ? "w-[18%]" : "w-[15%]"
								} ${
									column.key === "status" ? "rounded-r-lg" : ""
								}
								text-left text-ec-title font-inter font-normal text-sm
								 py-3 pl-5 bg-ec-modal border-0 rtl:text-right rtl:pr-5`}
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
