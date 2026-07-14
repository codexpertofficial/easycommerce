import React, { useState, useEffect } from "react";

// Components
import BillingAddress from "./Components/BillingAddress";
import ShippingAddress from "./Components/ShippingAddress";
import AddressSkeleton from "./Components/AddressSkeleton";

const defaultData = {
	first_name: "",
	last_name: "",
	email: "",
	phone: "",
	company: "",
	address_1: "",
	address_2: "",
	city: "",
	state: "",
	country: "",
	postcode: "",
};

// add billing and shipping before defalut data key
const addPrefixToKeys = (obj, prefix) => {
	return Object.keys(obj).reduce((acc, key) => {
		acc[prefix + key] = obj[key];
		return acc;
	}, {});
};

const Address = () => {
	const [isLoading, setIsLoading] = useState(true);
	const [billindData, setBillingData] = useState(
		addPrefixToKeys(defaultData, "billing_")
	);
	const [shippingData, setShippingData] = useState(
		addPrefixToKeys(defaultData, "shipping_")
	);
	const [billingStates, setBillingStates] = useState([]);
	const [billingCities, setBillingCities] = useState([]);
	const [shippingStates, setShippingStates] = useState([]);
	const [shippingCities, setShippingCities] = useState([]);

	const handleBillingDataChange = (e) => {
		const { name, value } = e.target;
		setBillingData((prevData) => ({ ...prevData, [name]: value }));
	};

	const handleShippingDataChange = (e) => {
		const { name, value } = e.target;
		setShippingData((prevData) => ({ ...prevData, [name]: value }));
	};

	const fetchAndStoreStates = (country, type = "billing") => {
		fetch(`${EASYCOMMERCE.rest_base}/geo/states/${country}`,{
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			}
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success && data.data?.states) {
					if (type === "billing") {
						setBillingStates(data.data.states);
						setBillingCities([]);
						setBillingData((prevData) => ({
							...prevData,
							billing_state: "",
							billing_city: "",
						}));
					} else {
						setShippingStates(data.data.states);
						setShippingCities([]);
						setShippingData((prevData) => ({
							...prevData,
							shipping_state: "",
							shipping_city: "",
						}));
					}
				}
			});
	};

	const fetchAndStoreCities = (country, state, type = "billing") => {
		fetch(`${EASYCOMMERCE.rest_base}/geo/cities/${country}?state=${state}`,{
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			}
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success && data.data?.cities) {
					if (type === "billing") {
						setBillingCities(data.data.cities);
					} else {
						setShippingCities(data.data.cities);
					}
				}
			});
	};

	// Submitting Billing Address Data
	const handleBillingFormSubmit = (e) => {
		e.preventDefault();

		const formData = new FormData(e.target);
		const billingData = Object.fromEntries(formData);

		easycommerce_modal(true);

		fetch(`${EASYCOMMERCE.rest_base}/me`, {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			},
			body: JSON.stringify({
				fields: {
					billing_address: billingData,
				},
			}),
		})
			.then((res) => res.json())
			.then((data) => {
				easycommerce_modal(false);
			});
	};

	// Submitting Shipping Address Data
	const handleShippingFormSubmit = (e) => {
		e.preventDefault();

		const formData = new FormData(e.target);
		const shippingData = Object.fromEntries(formData);

		easycommerce_modal(true);

		fetch(`${EASYCOMMERCE.rest_base}/me`, {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			},
			body: JSON.stringify({
				fields: {
					shipping_address: shippingData,
				},
			}),
		})
			.then((res) => res.json())
			.then((data) => {
				easycommerce_modal(false);
			});
	};

	const updateStateAndCityLists = (country, state, type) => {
		if (type === "billing") {
			fetch(`${EASYCOMMERCE.rest_base}/geo/states/${country}`,{
				headers: {
					"Content-Type": "application/json",
					"X-WP-Nonce": EASYCOMMERCE.nonce,
				}
			})
				.then((res) => res.json())
				.then((data) => {
					if (data.success && data.data?.states) {
						setBillingStates(data.data.states);
					}
				});

			if (!state) return;

			fetch(
				`${EASYCOMMERCE.rest_base}/geo/cities/${country}?state=${state}`,
				{
					headers: {
						"Content-Type": "application/json",
						"X-WP-Nonce": EASYCOMMERCE.nonce,
					}
				}
			)
				.then((res) => res.json())
				.then((data) => {
					if (data.success && data.data?.cities) {
						setBillingCities(data.data.cities);
					}
				});
		} else if (type === "shipping") {
			fetch(`${EASYCOMMERCE.rest_base}/geo/states/${country}`,{
				headers: {
					"Content-Type": "application/json",
					"X-WP-Nonce": EASYCOMMERCE.nonce,
				}
			})
				.then((res) => res.json())
				.then((data) => {
					if (data.success && data.data?.states) {
						setShippingStates(data.data.states);
					}
				});

			if (!state) return;

			fetch(
				`${EASYCOMMERCE.rest_base}/geo/cities/${country}?state=${state}`,
				{
					headers: {
						"Content-Type": "application/json",
						"X-WP-Nonce": EASYCOMMERCE.nonce,
					}
				}
			)
				.then((res) => res.json())
				.then((data) => {
					if (data.success && data.data?.cities) {
						setShippingCities(data.data.cities);
					}
				});
		}
	};

	useEffect(() => {
		const fields = ["billing_address", "shipping_address"];
		const params = new URLSearchParams({
			fields: fields,
		});

		fetch(`${EASYCOMMERCE.rest_base}/me?${params.toString()}`, {
			method: "GET",
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			},
		})
			.then((res) => res.json())
			.then((data) => {
				setIsLoading(false);

				if (data.success && data.data.customer?.billing_address) {
					setBillingData(data.data.customer.billing_address);

					updateStateAndCityLists(
						data.data.customer.billing_address?.country,
						data.data.customer.billing_address?.state,
						"billing"
					);
				}

				if (data.data.customer?.shipping_address) {
					setShippingData(data.data.customer.shipping_address);

					updateStateAndCityLists(
						data.data.customer.shipping_address?.country,
						data.data.customer.shipping_address?.state,
						"shipping"
					);
				}
			});
	}, []);

	return (
		<>
			<h3 className="easycommerce-dashboard-section-title !text-lg sm:!text-2xl mb-6">
				Address
			</h3>
			{!isLoading ? (
				<div className="grid gap-6">
					<BillingAddress
						data={billindData}
						billingStates={billingStates}
						billingCities={billingCities}
						fetchAndStoreStates={fetchAndStoreStates}
						fetchAndStoreCities={fetchAndStoreCities}
						handleBillingDataChange={handleBillingDataChange}
						handleBillingFormSubmit={handleBillingFormSubmit}
					/>
					<ShippingAddress
						data={shippingData}
						shippingStates={shippingStates}
						shippingCities={shippingCities}
						fetchAndStoreStates={fetchAndStoreStates}
						fetchAndStoreCities={fetchAndStoreCities}
						handleShippingDataChange={handleShippingDataChange}
						handleShippingFormSubmit={handleShippingFormSubmit}
					/>
				</div>
			) : (
				<div className="grid gap-6">
					<AddressSkeleton title="Billing" />
					<AddressSkeleton title="Shipping" />
				</div>
			)}
		</>
	);
};

export default Address;
