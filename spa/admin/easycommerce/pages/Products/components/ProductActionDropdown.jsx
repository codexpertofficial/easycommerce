import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
const actionIcon = `${EASYCOMMERCE.assets}admin/img/icons/actionIcon.png`;
const ProductActionDropdown = ({
	product,
	onEdit,
	onBuilder,
	onView,
	onRestore,
	onDelete,
	icons = {},
	menuWidth = "160px",
}) => {
	const [isOpen, setIsOpen] = useState(false);

	const actions = [];

	if (product.status === "trash") {
		actions.push({
			label: __("Restore", "easycommerce"),
			value: "restore",
			icon: icons.restore,
		});
	} else {
		actions.push(
			{
				label: __("Edit", "easycommerce"),
				value: "edit",
				icon: icons.edit,
			},
			{
				label: __("Builder", "easycommerce"),
				value: "builder",
				icon: icons.builder,
			},
			{
				label: __("View", "easycommerce"),
				value: "view",
				icon: icons.view,
			}
		);
	}

	actions.push({
		label: __("Delete", "easycommerce"),
		value: "delete",
		icon: product.status === "trash" ? icons.deleteRed : icons.delete,
	});

	const handleClick = (action) => {
		setIsOpen(false);
		switch (action.value) {
			case "edit":
				onEdit?.(product.id);
				break;
			case "builder":
				onBuilder?.(product.id);
				break;
			case "view":
				onView?.(product?.link);
				break;
			case "restore":
				onRestore?.(product.id, product.title);
				break;
			case "delete":
				onDelete?.(product.id, product.status);
				break;
			default:
				break;
		}
	};

	return (
		<div className="relative flex items-center justify-end">
			<button
				type="button"
				onClick={() => setIsOpen(true)}
				onBlur={() => setIsOpen(false)}
				className="flex items-center justify-center w-8 h-8 rounded-md"
			>
				<img src={actionIcon} alt={__("Action Icon", "easycommerce")} className="w-3" />
			</button>

			{isOpen && (
				<ul
					className="absolute right-0 top-full z-[99] p-3 border bg-white border-ec-border rounded-[12px] shadow-2xl min-w-[100px]"
					style={{ width: menuWidth }}
				>
					{actions.map((option) => (
						<li
							key={option.value}
							className="group flex items-center gap-2 px-4 py-2 text-sm text-ec-body font-normal leading-[26px] hover:bg-ec-modal cursor-pointer rounded-[4px]"
							onMouseDown={() => handleClick(option)}
						>
							{option.icon && (
								<img
									src={option.icon}
									alt={option.label}
									className="w-5 h-4 object-contain"
								/>
							)}
							{option.label}
						</li>
					))}
				</ul>
			)}
		</div>
	);
};
export default ProductActionDropdown;