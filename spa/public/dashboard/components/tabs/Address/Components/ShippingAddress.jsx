import React from "react";
import { __ } from "@wordpress/i18n";

const ShippingAddress = ({
	data,
	shippingStates,
	shippingCities,
	fetchAndStoreStates,
	fetchAndStoreCities,
	handleShippingDataChange,
	handleShippingFormSubmit,
}) => {
	return (
		<form onSubmit={handleShippingFormSubmit}>
			<div className="bg-white border border-ec-border rounded-2xl overflow-hidden shadow-[0_2px_16px_-8px_rgba(18,3,80,0.10)]">
			<div className="w-full flex items-center gap-2 px-6 py-4 border-b border-ec-border bg-ec-table-bg">
				<svg className="w-[18px] h-[18px] text-ec-primary" data-slot="icon" fill="none" stroke="currentColor" strokeWidth="1.7" viewBox="0 0 24 24">
					<path strokeLinecap="round" strokeLinejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
				</svg>
				<h3 className="easycommerce-dashboard-address-title">
					{__( "Shipping Address", "easycommerce" )}
				</h3>
			</div>
			<div
				className="grid grid-cols-1 sm:grid-cols-2 gap-5 p-6">
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="shipping_first_name"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						{__( "First Name", "easycommerce" )}
					</label>
					<input
						type="text"
						name="first_name"
						id="shipping_first_name"
						className="easycommerce-dashboard-input"
						placeholder={__( "Enter your first name", "easycommerce" )}
						value={data.first_name}
						onChange={handleShippingDataChange}
						required
					/>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="shipping_last_name"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						{__( "Last Name", "easycommerce" )}
					</label>
					<input
						type="text"
						name="last_name"
						id="shipping_last_name"
						className="easycommerce-dashboard-input"
						placeholder={__( "Enter your last name", "easycommerce" )}
						value={data.last_name}
						onChange={handleShippingDataChange}
						required
					/>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="shipping_email"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						{__( "Email", "easycommerce" )}
					</label>
					<input
						type="email"
						name="email"
						id="shipping_email"
						className="easycommerce-dashboard-input"
						placeholder={__( "Enter your email", "easycommerce" )}
						value={data.email}
						onChange={handleShippingDataChange}
						required
					/>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="shipping_company"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						{__( "Company Name", "easycommerce" )}
					</label>
					<input
						type="text"
						name="company"
						id="shipping_company"
						className="easycommerce-dashboard-input"
						placeholder={__( "Enter company name", "easycommerce" )}
						value={data.company}
						onChange={handleShippingDataChange}
					/>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="shipping_phone"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						{__( "Phone Number", "easycommerce" )}
					</label>
					<input
						type="tel"
						name="phone"
						id="shipping_phone"
						className="easycommerce-dashboard-input"
						placeholder={__( "Enter your phone number", "easycommerce" )}
						value={data.phone}
						onChange={handleShippingDataChange}
					/>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="shipping_address_1"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						{__( "Address Line 1", "easycommerce" )}
					</label>
					<input
						type="text"
						name="address_1"
						id="shipping_address_1"
						className="easycommerce-dashboard-input"
						placeholder={__( "Enter address", "easycommerce" )}
						value={data.address_1}
						onChange={handleShippingDataChange}
					/>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="shipping_address_2"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						{__( "Address Line 2", "easycommerce" )}
					</label>
					<input
						type="text"
						name="address_2"
						id="shipping_address_2"
						className="easycommerce-dashboard-input"
						placeholder={__( "Apartment, suite, etc.", "easycommerce" )}
						value={data.address_2}
						onChange={handleShippingDataChange}
					/>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="shipping_country"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						{__( "Country", "easycommerce" )}
					</label>
					<select
						name="country"
						id="shipping_country"
						className="easycommerce-dashboard-input-select"
						value={data.country}
						onChange={(e) => {
							handleShippingDataChange(e);
							fetchAndStoreStates(e.target.value, "shipping");
						}}
						required>
						<option value="">{__( "Select your country", "easycommerce" )}</option>
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
						htmlFor="shipping_state"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						{__( "State/Province", "easycommerce" )}
					</label>
					<select
						name="state"
						id="shipping_state"
						className="easycommerce-dashboard-input-select"
						value={data.state}
						onChange={(e) => {
							handleShippingDataChange(e);
							fetchAndStoreCities(
								data.country,
								e.target.value,
								"shipping"
							);
						}}>
						<option value="">{__( "Select State", "easycommerce" )}</option>
						{shippingStates.length > 0 &&
							shippingStates.map((state) => (
								<option key={state} value={state}>
									{state}
								</option>
							))}
					</select>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="shipping_city"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						{__( "City", "easycommerce" )}
					</label>
					<select
						name="city"
						id="shipping_city"
						className="easycommerce-dashboard-input-select"
						value={data.city}
						onChange={handleShippingDataChange}>
						<option value="">{__( "Select City", "easycommerce" )}</option>
						{shippingCities.length > 0 &&
							shippingCities.map((city) => (
								<option key={city} value={city}>
									{city}
								</option>
							))}
					</select>
				</div>
				<div className="col-span-1 flex flex-col gap-2 items-start">
					<label
						htmlFor="shipping_postcode"
						className="font-inter font-medium text-base leading-[26px] text-ec-body">
						{__( "ZIP/Postal Code", "easycommerce" )}
					</label>
					<input
						type="text"
						name="postcode"
						id="shipping_postcode"
						className="easycommerce-dashboard-input"
						placeholder={__( "Enter postal code", "easycommerce" )}
						value={data.postcode}
						onChange={handleShippingDataChange}
					/>
				</div>
			</div>
			<div className="flex justify-end items-center px-6 py-4 border-t border-ec-border">
				<button
					className="easycommerce-dashboard-form-btn save"
					type="submit">
					{__( "Update Address", "easycommerce" )}
				</button>
			</div>
			</div>
		</form>
	);
};

export default ShippingAddress;
