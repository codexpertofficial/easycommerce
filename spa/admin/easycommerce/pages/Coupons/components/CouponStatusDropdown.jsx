import React, { useState, useEffect } from "react";
import { __ } from "@wordpress/i18n";
import globalToast from "../../../../common/components/globalToast";

const arrowDown = `${EASYCOMMERCE.assets}admin/img/icons/arrowDown.png`;
const activeStatus = `${EASYCOMMERCE.assets}admin/img/icons/Complete.png`;
const inactiveStatus = `${EASYCOMMERCE.assets}admin/img/icons/Cancle.png`;

const CouponStatusDropdown = ({
	value,
	couponId,
	onStatusChange = () => {},
	width = "116px",
	menuWidth = "150px",
}) => {
	const statusOptions = [
		{ label: __( "Active", "easycommerce" ), value: "active" },
		{ label: __( "Inactive", "easycommerce" ), value: "inactive" },
	];

	const statusColors = {
		active: {
			color: "var(--color-ec-activeText)",
			background: "var(--color-ec-activeBg)",
			border: "1px solid var(--color-ec-activeBorder)",
		},
		inactive: {
			color: "var(--color-ec-inactiveText)",
			background: "var(--color-ec-inactiveBg)",
			border: "1px solid var(--color-ec-inactiveBorder)",
		},
	};
	
	const [isOpen, setIsOpen] = useState(false);
	const [selected, setSelected] = useState(value);
	const { addToastData } = globalToast();

	useEffect(() => {
		setSelected(value);
	}, [value]);

	const handleOptionClick = (option) => {
		setIsOpen(false);
		if (selected === option.value) return;

		easycommerce_modal(true);

		fetch(`${EASYCOMMERCE.rest_base}/coupons/${couponId}?active=${option.value === "active" ? 1 : 0}`, {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			},
		})
			.then((res) => res.json())
			.then((data) => {
				easycommerce_modal(false);

				if (data.success && data.data?.id) {
					setSelected(option.value);
					addToastData({
						type: "success",
						message: data.data.message || __( "Coupon status updated", "easycommerce" ),
					});
					onStatusChange(option.value);
				} else {
					addToastData({
						type: "error",
						message: data.data?.message || __( "Failed to update coupon status", "easycommerce" ),
					});
				}
			})
			.catch(() => {
				easycommerce_modal(false);
				addToastData({
					type: "error",
					message: __( "Something went wrong while updating coupon status", "easycommerce" ),
				});
			});
	};

	return (
		<div
			className="relative flex items-center justify-center rounded-lg"
			style={{
				width,
				color: statusColors[selected]?.color,
				background: statusColors[selected]?.background,
				border: `${statusColors[selected]?.border}`,
			}}
		>
			<button
				type="button"
				onClick={() => setIsOpen(!isOpen)}
				onBlur={() => setIsOpen(false)}
				className="easycommerce-dropdown-field h-[32px] text-sm font-inter text-left pl-4 pr-10 capitalize w-full"
			>
				{selected}
			</button>

			{isOpen && (
				<ul
					className="absolute top-10 p-3 right-[-18px] border bg-white border-ec-border rounded-[12px] shadow-2xl z-[9999]"
					style={{ width: menuWidth }}
				>
					{statusOptions.map((option) => (
						<li
							key={option.value}
							className="px-4 py-2 text-sm font-normal text-ec-body leading-[26px] hover:bg-ec-modal cursor-pointer rounded-[4px]"
							onMouseDown={() => handleOptionClick(option)}
						>
							{option.label}
						</li>
					))}
				</ul>
			)}

			<svg xmlns="http://www.w3.org/2000/svg" width="11" height="6" viewBox="0 0 11 6" fill="none" className={`easycommerce-select-icon absolute w-3 ml-0 right-3 transition-transform duration-300 ${isOpen ? "rotate-180" : "rotate-0"}`}>
				<path d="M9.87109 1.71094L5.71484 5.62109C5.56901 5.7487 5.41406 5.8125 5.25 5.8125C5.08594 5.8125 4.9401 5.7487 4.8125 5.62109L0.65625 1.71094C0.382812 1.40104 0.373698 1.09115 0.628906 0.78125C0.920573 0.507812 1.23047 0.498698 1.55859 0.753906L5.25 4.25391L8.96875 0.753906C9.27865 0.498698 9.57943 0.498698 9.87109 0.753906C10.1263 1.08203 10.1263 1.40104 9.87109 1.71094Z" fill={statusColors[selected]?.color}/>
			</svg>
		</div>
	);
};

export default CouponStatusDropdown;
