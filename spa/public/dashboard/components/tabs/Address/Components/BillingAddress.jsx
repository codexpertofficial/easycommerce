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
			<div className="w-full h-[57px] flex items-center px-5 border border-ec-border rounded-tl-lg rounded-tr-lg">
				<h3 className="easycommerce-dashboard-address-title">
					Billing Addressd
				</h3>
			</div>
			<div className="easycommerce-dashboard-address-wrapper grid grid-cols-1 sm:grid-cols-2 gap-4 px-5 pt-6 pb-[30px] border border-ec-border border-t-0 rounded-bl-lg rounded-br-lg">
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
			<div className="easycommerce-dashboard-form-submit w-full mt-4 flex justify-end items-center">
				<button
					className="easycommerce-dashboard-form-btn save"
					type="submit">
					Update Address
				</button>
			</div>
		</form>
	);
};

export default BillingAddress;
