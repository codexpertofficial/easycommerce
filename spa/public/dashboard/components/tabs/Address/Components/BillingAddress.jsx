import React from "react";

const BillingAddress = ({
	data,
	billingStates,
	billingCities,
	fetchAndStoreStates,
	fetchAndStoreCities,
	handleBillingDataChange,
	handleBillingFormSubmit,
}) => {
	return (
		<form onSubmit={handleBillingFormSubmit}>
			<div className="bg-white border border-ec-border rounded-2xl overflow-hidden shadow-[0_2px_16px_-8px_rgba(18,3,80,0.10)]">
			<div className="w-full flex items-center gap-2 px-6 py-4 border-b border-ec-border bg-ec-table-bg">
				<svg className="w-[18px] h-[18px] text-ec-primary" data-slot="icon" fill="none" stroke="currentColor" strokeWidth="1.7" viewBox="0 0 24 24">
					<path strokeLinecap="round" strokeLinejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
				</svg>
				<h3 className="easycommerce-dashboard-address-title">
					Billing Address
				</h3>
			</div>
			<div className="easycommerce-dashboard-address-wrapper grid grid-cols-1 sm:grid-cols-2 gap-5 p-6">
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="billing_first_name"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						First Name
					</label>
					<input
						type="text"
						name="first_name"
						id="billing_first_name"
						className="easycommerce-dashboard-input"
						placeholder="Enter your first name"
						value={data.first_name || ""}
						onChange={handleBillingDataChange}
						required
					/>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="billing_last_name"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						Last Name
					</label>
					<input
						type="text"
						name="last_name"
						id="billing_last_name"
						className="easycommerce-dashboard-input"
						placeholder="Enter your last name"
						value={data.last_name}
						onChange={handleBillingDataChange}
						required
					/>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="billing_email"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						Email
					</label>
					<input
						type="email"
						name="email"
						id="billing_email"
						className="easycommerce-dashboard-input"
						placeholder="Enter your email"
						value={data.email}
						onChange={handleBillingDataChange}
						required
					/>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="billing_company"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						Company Name
					</label>
					<input
						type="text"
						name="company"
						id="billing_company"
						className="easycommerce-dashboard-input"
						placeholder="Enter company name"
						value={data.company}
						onChange={handleBillingDataChange}
					/>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="billing_phone"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						Phone Number
					</label>
					<input
						type="tel"
						name="phone"
						id="billing_phone"
						className="easycommerce-dashboard-input"
						placeholder="Enter your phone number"
						value={data.phone}
						onChange={handleBillingDataChange}
					/>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="billing_address_1"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						Address Line 1
					</label>
					<input
						type="text"
						name="address_1"
						id="billing_address_1 "
						className="easycommerce-dashboard-input"
						placeholder="Enter address"
						value={data.address_1}
						onChange={handleBillingDataChange}
					/>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="billing_address_2"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						Address Line 2
					</label>
					<input
						type="text"
						name="address_2"
						id="billing_address_2"
						className="easycommerce-dashboard-input"
						placeholder="Apartment, suite, etc."
						value={data.address_2}
						onChange={handleBillingDataChange}
					/>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="billing_country"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						Country
					</label>
					<select
						name="country"
						id="billing_country"
						className="easycommerce-dashboard-input-select"
						value={data.country}
						onChange={(e) => {
							handleBillingDataChange(e);
							fetchAndStoreStates(e.target.value, "billing");
						}}
						required>
						<option value="">Select your country</option>
						{EASYCOMMERCE.shipping.countries &&
							Object.entries(EASYCOMMERCE.shipping.countries).map(
								([code, countryName]) => (
									<option key={code} value={code}>
										{countryName}
									</option>
								)
							)}
					</select>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="billing_state"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						State/Province
					</label>
					<select
						name="state"
						id="billing_state"
						className="easycommerce-dashboard-input-select"
						value={data.state}
						onChange={(e) => {
							handleBillingDataChange(e);
							fetchAndStoreCities(
								data.country,
								e.target.value,
								"billing"
							);
						}}>
						<option value="">Select State</option>
						{billingStates.length > 0 &&
							billingStates.map((state) => (
								<option key={state} value={state}>
									{state}
								</option>
							))}
					</select>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="billing_city"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						City
					</label>
					<select
						name="city"
						id="billing_city"
						className="easycommerce-dashboard-input-select"
						onChange={handleBillingDataChange}
						value={data.city}>
						<option value="">Select City</option>
						{billingCities.length > 0 &&
							billingCities.map((city) => (
								<option key={city} value={city}>
									{city}
								</option>
							))}
					</select>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="billing_postcode"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						ZIP/Postal Code
					</label>
					<input
						type="text"
						name="postcode"
						id="billing_postcode"
						className="easycommerce-dashboard-input"
						placeholder="Enter postal code"
						value={data.postcode}
						onChange={handleBillingDataChange}
					/>
				</div>
			</div>
			<div className="flex justify-end items-center px-6 py-4 border-t border-ec-border">
				<button
					className="easycommerce-dashboard-form-btn save"
					type="submit">
					Update Address
				</button>
			</div>
			</div>
		</form>
	);
};

export default BillingAddress;
