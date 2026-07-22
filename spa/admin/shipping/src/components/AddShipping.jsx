import React, { useEffect, useState, useRef } from "react";
import { createPortal } from 'react-dom';
import { __, sprintf } from "@wordpress/i18n";

// Components
import ShippingSkeleton from "./ShippingSkeleton";
import Dropdown from "../../../common/components/inputs/Dropdown";

// Icons
const deleteIcon = `${EASYCOMMERCE.assets}admin/img/icons/delete.png`;
const deletehoverIcon = `${EASYCOMMERCE.assets}admin/img/icons/delete-hover.png`;
const plusIcon = `${EASYCOMMERCE.assets}admin/img/icons/plusIcon.png`;

const shippingCountries = Object.keys(EASYCOMMERCE.shipping.countries).map(
	(key) => {
		return { value: key, label: EASYCOMMERCE.shipping.countries[key] };
	}
);

const taxCountries = typeof EASYCOMMERCE !== 'undefined' && EASYCOMMERCE.tax?.countries
	? Object.keys(EASYCOMMERCE.tax.countries)
	: [];

const weightUnits = EASYCOMMERCE.units.weight;

const AddShipping = ({ handleCancel, showToast, ShippingPlanId }) => {
	const [states, setStates] = useState([]);
	const [cities, setCities] = useState([]);
	const [isLoading, setIsLoading] = useState(false);
	const [shippingMethodList, setShippingMethodList] = useState([
		{
			name: "",
			min: 0,
			max: null,
			cost: 0,
			min_unit: 'kg',
			max_unit: 'kg',
		},
	]);
	const [shippingRegionList, setShippingRegionList] = useState([
		{
			country: "",
			state: "",
			city: "",
		},
	]);
	const [shippingPlan, setShippingPlan] = useState({
		name: "",
		description: "",
		active: true,
		taxable: true,
		calculation_base: "price",
		regions: [],
		methods: [],
	});
	const [minWeightDropdowns, setMinWeightDropdowns] = useState([]);
	const [maxWeightDropdowns, setMaxWeightDropdowns] = useState([]);
	const [minDropdownPositions, setMinDropdownPositions] = useState([]);
	const [maxDropdownPositions, setMaxDropdownPositions] = useState([]);
	const minDropdownRefs = useRef([]);
	const maxDropdownRefs = useRef([]);

	const hasUntaxedCountry = shippingRegionList.some(
		region => region.country && !taxCountries.includes(region.country)
	);

	const handleAddMethodRow = (index) => {
		const newShippingMethodList = [...shippingMethodList];
		newShippingMethodList.splice(index + 1, 0, {
			name: "",
			min: 0,
			max: null,
			cost: 0,
			min_unit: 'kg',
			max_unit: 'kg',
		});
		setShippingMethodList(newShippingMethodList);
		setMinWeightDropdowns([...minWeightDropdowns, false]);
		setMaxWeightDropdowns([...maxWeightDropdowns, false]);
		setMinDropdownPositions([...minDropdownPositions, { top: 0, left: 0 }]);
		setMaxDropdownPositions([...maxDropdownPositions, { top: 0, left: 0 }]);
	};

	const labelMap = {
		price: {
			min: sprintf( __( 'Min Price (%s)', 'easycommerce' ), EASYCOMMERCE.currency_symbol || '$' ),
			max: sprintf( __( 'Max Price (%s)', 'easycommerce' ), EASYCOMMERCE.currency_symbol || '$' ),
		},
		weight: {
			min: __( 'Min Weight', 'easycommerce' ),
			max: __( 'Max Weight', 'easycommerce' ),
		},
		quantity: {
			min: __( 'Min Quantity', 'easycommerce' ),
			max: __( 'Max Quantity', 'easycommerce' ),
		},
		default: {
			min: __( 'Min', 'easycommerce' ),
			max: __( 'Max', 'easycommerce' ),
		},
	};

	const handleAddRegionRow = (index) => {
		const newShippingRegionList = [...shippingRegionList];
		newShippingRegionList.splice(index + 1, 0, {
			country: "",
			state: "",
			city: "",
		});
		setShippingRegionList(newShippingRegionList);
	};

	const handleDeleteMethod = (index) => {
		if (shippingMethodList.length > 1) {
			shippingMethodList.splice(index, 1);
			setShippingMethodList([...shippingMethodList]);
			setMinWeightDropdowns(minWeightDropdowns.filter((_, i) => i !== index));
			setMaxWeightDropdowns(maxWeightDropdowns.filter((_, i) => i !== index));
			setMinDropdownPositions(minDropdownPositions.filter((_, i) => i !== index));
			setMaxDropdownPositions(maxDropdownPositions.filter((_, i) => i !== index));
		}
	};

	const handleDeleteRegion = (index) => {
		if (shippingRegionList.length > 1) {
			shippingRegionList.splice(index, 1);
			setShippingRegionList([...shippingRegionList]);
			const updatedStates = [...states];
			updatedStates.splice(index, 1);
			setStates(updatedStates);
			const updatedCities = [...cities];
			updatedCities.splice(index, 1);
			setCities(updatedCities);
		}
	};

	const handleMethodItemChange = (value, index, key) => {
		const updatedList = [...shippingMethodList];
		updatedList[index][key] = value;
		setShippingMethodList(updatedList);
	};

	const handleUnitChange = (unit, index, type) => {
		const updatedList = [...shippingMethodList];
		updatedList[index][type === 'min' ? 'min_unit' : 'max_unit'] = unit;
		setShippingMethodList(updatedList);
	};

	const handleSaveShipping = () => {
		const shippingData = {
			...shippingPlan,
			regions: shippingRegionList.map(region => ({
				country: region.country,
				state: region.state,
				city: region.city,
				zip_code: region.zip || ''
			})),
			methods: shippingMethodList.map(method => ({
				name: method.name,
				min: parseFloat(method.min) || 0,
				max: method.max !== null && method.max !== '' ? parseFloat(method.max) : null,
				cost: parseFloat(method.cost) || 0,
				min_unit: method.min_unit,
				max_unit: method.max_unit,
				taxable: shippingPlan.taxable,
			}))
		};

		if (!shippingData.name) {
			showToast("error", __( "Shipping plan name is required.", "easycommerce" ));
			return;
		} else if (!shippingData.calculation_base) {
			showToast("error", __( "Calculation base is required.", "easycommerce" ));
			return;
		}

		easycommerce_modal(true);

		fetch(`${EASYCOMMERCE.rest_base}/shipping-plans`, {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			},
			body: JSON.stringify(shippingData),
		})
			.then((res) => res.json())
			.then((data) => {
				easycommerce_modal(false);
				if (data.success && data.data?.id) {
					showToast("success", data.data.message);
					handleCancel();
				} else {
					showToast("error", data.data.message);
				}
			});
	};

	const handleShippingRegionChange = (value, index, key) => {
		const updatedList = [...shippingRegionList];
		updatedList[index][key] = value;
		setShippingRegionList(updatedList);

		if (key === "country") {
			fetchAndStoreStates(value, index);
		} else if (key === "state") {
			fetchAndStoreCities(
				shippingRegionList[index].country,
				value,
				index
			);
		}
	};

	const fetchAndStoreStates = (country, index) => {
		fetch(`${EASYCOMMERCE.rest_base}/geo/states/${country}`, {
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			}
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success && data.data?.states) {
					const updatedStates = [...states];
					updatedStates[index] = data.data.states;
					setStates(updatedStates);
					const updatedRegionList = [...shippingRegionList];
					updatedRegionList[index].state = "";
					updatedRegionList[index].city = "";
					setShippingRegionList(updatedRegionList);
				}
			});
	};

	const fetchAndStoreCities = (country, state, index) => {
		fetch(`${EASYCOMMERCE.rest_base}/geo/cities/${country}?state=${state}`, {
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			}
		})
			.then((res) => res.json())
			.then((data) => {
				if (data.success && data.data?.cities) {
					const updatedCities = [...cities];
					updatedCities[index] = data.data.cities;
					setCities(updatedCities);
					const updatedRegionList = [...shippingRegionList];
					updatedRegionList[index].city = "";
					setShippingRegionList(updatedRegionList);
				}
			});
	};

	const handleUpdateShipping = () => {
		const shippingData = {
			...shippingPlan,
			regions: shippingRegionList.map(region => ({
				country: region.country,
				state: region.state,
				city: region.city,
				zip_code: region.zip || ''
			})),
			methods: shippingMethodList.map(method => ({
				name: method.name,
				min: parseFloat(method.min) || 0,
				max: method.max !== null && method.max !== '' ? parseFloat(method.max) : null,
				cost: parseFloat(method.cost) || 0,
				min_unit: method.min_unit,
				max_unit: method.max_unit,
			    taxable: shippingPlan.taxable,
			})),
		};

		easycommerce_modal(true);

		fetch(`${EASYCOMMERCE.rest_base}/shipping-plans/${ShippingPlanId}`, {
			method: "PUT",
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			},
			body: JSON.stringify(shippingData),
		})
			.then((res) => res.json())
			.then((data) => {
				easycommerce_modal(false);
				if (data.success) {
					showToast("success", __( "Shipping plan updated successfully.", "easycommerce" ));
					handleCancel();
				} else {
					showToast("error", data.data?.message || __( "Failed to update shipping plan.", "easycommerce" ));
				}
			});
	};

	const updateStateAndCityLists = async (regions) => {
		const updatedStates = [];
		const updatedCities = [];
		const fetchPromises = regions.map((region, index) => {
			return fetch(
				`${EASYCOMMERCE.rest_base}/geo/states/${region.country}`, {
					headers: {
						"Content-Type": "application/json",
						"X-WP-Nonce": EASYCOMMERCE.nonce,
					}
				}
			)
				.then((res) => res.json())
				.then((data) => {
					if (data.success && data.data?.states) {
						updatedStates[index] = data.data.states;
						if (
							region.state &&
							data.data.states.includes(region.state)
						) {
							return fetch(
								`${EASYCOMMERCE.rest_base}/geo/cities/${region.country}?state=${region.state}`, {
									headers: {
										"Content-Type": "application/json",
										"X-WP-Nonce": EASYCOMMERCE.nonce,
									}
								}
							)
								.then((res) => res.json())
								.then((data) => {
									if (data.success && data.data?.cities) {
										updatedCities[index] = data.data.cities;
									}
								});
						}
					}
				});
		});
		await Promise.all(fetchPromises);
		setStates(updatedStates);
		setCities(updatedCities);
	};

	useEffect(() => {
		if (!ShippingPlanId) {
			setMinWeightDropdowns([false]);
			setMaxWeightDropdowns([false]);
			setMinDropdownPositions([{ top: 0, left: 0 }]);
			setMaxDropdownPositions([{ top: 0, left: 0 }]);
			return;
		}

		setIsLoading(true);

		fetch(`${EASYCOMMERCE.rest_base}/shipping-plans?id=${ShippingPlanId}`, {
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": EASYCOMMERCE.nonce,
			}
		})
			.then((res) => res.json())
			.then(async (data) => {
				if (data.success && data.data?.id) {
					const fetchedPlan = data.data;
					const methods = fetchedPlan.methods.map((method) => ({
						name: method.name || "",
						min: parseFloat(method.min) || 0,
						max: method.max !== null && method.max !== undefined ? parseFloat(method.max) : null,
						cost: parseFloat(method.cost) || 0,
						min_unit: method.min_unit || 'kg',
						max_unit: method.max_unit || 'kg',
					}));
					const regions = fetchedPlan.regions.map((region) => {
						const [country, state, city] = region.region_code.split("-");
						return {
							country,
							state,
							city,
							zip: region.zip_code || ""
						};
					});
					setShippingPlan({
						id: fetchedPlan.id,
						name: fetchedPlan.name,
						description: fetchedPlan.description,
						active: fetchedPlan.active,
						taxable: fetchedPlan.taxable || false,
						calculation_base: fetchedPlan.calculation_base,
						regions: regions,
						methods: methods,
					});
					setShippingRegionList(regions);
					setShippingMethodList(methods);
					setMinWeightDropdowns(methods.map(() => false));
					setMaxWeightDropdowns(methods.map(() => false));
					setMinDropdownPositions(methods.map(() => ({ top: 0, left: 0 })));
					setMaxDropdownPositions(methods.map(() => ({ top: 0, left: 0 })));
					await updateStateAndCityLists(regions);
				}
			})
			.catch((error) => {
			})
			.finally(() => {
				setIsLoading(false);
			});
	}, [ShippingPlanId]);

	useEffect(() => {
		shippingMethodList.forEach((_, index) => {
			if (minDropdownRefs.current[index] && minWeightDropdowns[index]) {
				const rect = minDropdownRefs.current[index].getBoundingClientRect();
				setMinDropdownPositions(prev => {
					const newPositions = [...prev];
					newPositions[index] = {
						top: rect.bottom + 10 + window.scrollY,
						left: rect.left + window.scrollX,
					};
					return newPositions;
				});
			}
			if (maxDropdownRefs.current[index] && maxWeightDropdowns[index]) {
				const rect = maxDropdownRefs.current[index].getBoundingClientRect();
				setMaxDropdownPositions(prev => {
					const newPositions = [...prev];
					newPositions[index] = {
						top: rect.bottom + 10 + window.scrollY,
						left: rect.left + window.scrollX,
					};
					return newPositions;
				});
			}
		});
	}, [minWeightDropdowns, maxWeightDropdowns]);

	return (
		<>
			{!isLoading ? (
				<div className="bg-white mt-8">
					<div>
						<p className="flex ec-db-lg:items-center items-start py-4 ec-db-lg:flex-row flex-col ec-db-lg:gap-0 gap-4">
							<label
								className="text-ec-body font-inter font-normal text-base leading-4 w-[180px]"
								htmlFor="easycommerce-shipping-name">
								{ __( "Name", "easycommerce" ) }
							</label>
							<div className="w-full">
								<div className="flex">
									<input
										onChange={(e) =>
											setShippingPlan({
												...shippingPlan,
												name: e.target.value,
											})
										}
										value={shippingPlan.name}
										type="text"
										className="h-ec-input p-3 rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none 
    									focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out w-full disabled:cursor-not-allowed disabled:bg-ec-table-stock disabled:hover:border-ec-table-stock disabled:focus:border-ec-table-stock"
										placeholder={__( "Shipping name here", "easycommerce" )}
									/>
								</div>
							</div>
						</p>
						<p className="flex ec-db-lg:items-center items-start py-4 ec-db-lg:flex-row flex-col ec-db-lg:gap-0 gap-4">
							<label
								className="text-ec-body font-inter font-normal text-base leading-4 w-[180px]"
								htmlFor="easycommerce-shipping-description">
								{ __( "Description", "easycommerce" ) }
							</label>
							<div className="w-full">
								<div className="flex">
									<input
										onChange={(e) =>
											setShippingPlan({
												...shippingPlan,
												description: e.target.value,
											})
										}
										value={shippingPlan.description}
										type="text"
										className="h-ec-input p-3 rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none 
    									focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out w-full disabled:cursor-not-allowed disabled:bg-ec-table-stock disabled:hover:border-ec-table-stock disabled:focus:border-ec-table-stock"
										placeholder={__( "Write description here", "easycommerce" )}
									/>
								</div>
							</div>
						</p>
						<p className="flex items-center py-4">
							<label
								className="text-ec-body font-inter font-normal text-base leading-4 w-[180px]"
								htmlFor="easycommerce-shipping-description">
								{ __( "Enable/Disable", "easycommerce" ) }
							</label>
							<div className="w-full">
								<div className="flex">
									<label className="easycommerce-switch">
										<input
											type="checkbox"
											checked={shippingPlan.active}
											onChange={(e) => {
												setShippingPlan({
													...shippingPlan,
													active: e.target.checked,
												});
											}}
										/>
										<span className="easycommerce-slider easycommerce-round"></span>
									</label>
								</div>
							</div>
						</p>
						<p className="flex items-start py-4 ec-db-lg:flex-row flex-col ec-db-lg:gap-0 gap-4">
							<label
								className="text-ec-body font-inter font-normal text-base leading-4 w-[180px]"
								htmlFor="easycommerce-shipping-name">
								{ __( "Regions", "easycommerce" ) }
							</label>
							<div className="w-full flex gap-6">
								<div className="w-full flex border border-ec-border py-4 px-[9px] rounded-lg">
									<table className="w-full">
										<thead>
											<tr>
												<th className="w-[22%] text-start text-ec-body text-sm font-normal font-inter pl-[7px]">
													{ __( "Country", "easycommerce" ) }
												</th>
												<th className="w-[22%] text-start text-ec-body text-sm font-normal leading-4 font-inter pl-[7px]">
													{ __( "State", "easycommerce" ) }
												</th>
												<th className="w-[22%] text-start text-ec-body text-sm font-normal leading-4 font-inter pl-[7px]">
													{ __( "City", "easycommerce" ) }
												</th>
												<th className="w-[22%] text-start text-ec-body text-sm font-normal leading-4 font-inter pl-[7px]">
													{ __( "Zip Code", "easycommerce" ) }
												</th>
												<th className="w-[22%]"></th>
											</tr>
										</thead>
										<tbody>
											{shippingRegionList.map(
												(region, index) => (
													<tr key={index}>
														<td className="px-[7px]">
															<div className="h-ec-input mb-[14px]">
																<Dropdown
																	options={[
																		{ label: __( "Select Country", "easycommerce" ), value: "" }, 
																		...shippingCountries,
																	]}
																	placeholder={__( "Select Country", "easycommerce" )}
																	value={region.country}
																	onChange={(option) =>
																		handleShippingRegionChange(option.value, index, "country")
																	}
																/>
															</div>
														</td>
														<td className="px-[7px]">
															<div className="h-ec-input mb-[14px]">
																<Dropdown
																	options={[
																		{ label: __( "Select State", "easycommerce" ), value: "" }, 
																		...(states[index]?.map((state) => ({
																		label: state,
																		value: state,
																		})) || [])
																	]}
																	placeholder={__( "Select State", "easycommerce" )}
																	value={region.state}
																	onChange={(option) =>
																		handleShippingRegionChange(option.value, index, "state")
																	}
																/>
															</div>
														</td>
														<td className="px-[7px]">
															<div className="h-ec-input mb-[14px]">
																<Dropdown
																	options={[
																		{ label: __( "Select City", "easycommerce" ), value: "" },
																		...(cities[index]?.map((city) => ({
																		label: city,
																		value: city,
																		})) || [])
																	]}
																	placeholder={__( "Select City", "easycommerce" )}
																	value={region.city || ""}
																	onChange={(option) =>
																		handleShippingRegionChange(option.value, index, "city")
																	}
																/>
															</div>
														</td>
														<td className="px-[7px]">
															<input
																name={`easycommerce-shipping-${index}-zip`}
																id={`easycommerce-shipping-${index}-zip`}
																className="h-ec-input w-full mb-[16px] p-3 rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out disabled:cursor-not-allowed disabled:bg-ec-table-stock disabled:hover:border-ec-table-stock disabled:focus:border-ec-table-stock"
																type="text"
																onChange={(e) => {
																	const updatedRegions = [...shippingRegionList];
																	updatedRegions[index].zip = e.target.value;
																	setShippingRegionList(updatedRegions);
																}}
																value={region.zip}
																placeholder={__( "Write here", "easycommerce" )}
															/>
														</td>
														<td className="flex items-center justify-center">
															<button
																type="button"
																onClick={() =>
																	handleAddRegionRow(
																		index
																	)
																}
																className="w-10 h-10 border border-ec-table-stock rounded-full mx-2 flex items-center justify-center">
																<img
																	src={plusIcon}
																	alt=""
																/>
															</button>
															<button
																type="button"
																onClick={() =>
																	handleDeleteRegion(
																		index
																	)
																}
																className="w-10 h-10 border border-ec-table-stock rounded-full flex items-center justify-center group hover:bg-[#FF3A521A]">
																<img
																	className="w-[14px] h-[16px] block group-hover:hidden"
																	src={deleteIcon}
																	alt="delete-pricing"
																/>
																<img
																	className="w-[14px] h-[16px] hidden group-hover:block"
																	src={
																		deletehoverIcon
																	}
																	alt="delete-pricing"
																/>
															</button>
														</td>
													</tr>
												)
											)}
										</tbody>
									</table>
								</div>
							</div>
						</p>
						<p className="flex ec-db-lg:items-center items-start py-4 ec-db-lg:flex-row flex-col ec-db-lg:gap-0 gap-4">
							<label
								className="text-ec-body font-inter font-normal text-base leading-4 w-[180px]"
								htmlFor="easycommerce-shipping-calculcution-base">
								{ __( "Calculation Base", "easycommerce" ) }
							</label>
							<div className="w-full">
								<div className="w-full h-ec-input">
									<Dropdown
										options={[
											{ label: __( "Select Base", "easycommerce" ), value: "" }, 
											{ label: __( "Cart Total", "easycommerce" ), value: "price" },
											{ label: __( "Total Weight", "easycommerce" ), value: "weight" },
											{ label: __( "Item Count", "easycommerce" ), value: "quantity" }
										]}
										placeholder={__( "Select Base", "easycommerce" )}
										value={shippingPlan.calculation_base || ""}
										onChange={(option) =>
											setShippingPlan({
												...shippingPlan,
												calculation_base: option.value,
											})
										}
									/>
								</div>
							</div>
						</p>
						<p className="flex items-center py-4">
							<label
								className="text-ec-body font-inter font-normal text-base leading-4 w-[180px]"
								htmlFor="easycommerce-shipping-description">
								{ __( "Taxable", "easycommerce" ) }
							</label>
							<div className="w-full">
								<div className="flex items-center gap-3">
									<label className={`easycommerce-switch ${hasUntaxedCountry ? 'opacity-50 cursor-not-allowed' : ''}`}>
										<input
											type="checkbox"
											checked={hasUntaxedCountry ? false : shippingPlan.taxable}
											disabled={hasUntaxedCountry}
											onChange={(e) => {
												if (!hasUntaxedCountry) {
													setShippingPlan({
														...shippingPlan,
														taxable: e.target.checked,
													});
												}
											}}
										/>
										<span className="easycommerce-slider easycommerce-round"></span>
									</label>
									{hasUntaxedCountry && (
										<span className="text-sm text-red-500 font-inter">
											{ __( "No tax rate configured for this country.", "easycommerce" ) }
										</span>
									)}
								</div>
							</div>
						</p>
						<p className="flex items-start py-4 ec-db-lg:flex-row flex-col ec-db-lg:gap-0 gap-4">
							<label
								className="text-ec-body font-inter font-normal text-base leading-4 w-[180px]"
								htmlFor="easycommerce-shipping-name">
								{ __( "Methods", "easycommerce" ) }
							</label>
							<div className="w-full flex">
								<div className="w-full border border-ec-border py-4 px-[9px] rounded-lg">
									<table className="w-full">
										<thead>
											<tr>
												<th className="w-[22%] text-start text-ec-body pb-1 text-sm font-normal leading-4 font-inter pl-[7px]">
													{ __( "Method Name", "easycommerce" ) }
												</th>
												<th className="w-[22%] text-start text-ec-body pb-1 text-sm font-normal leading-4 font-inter pl-[7px]">
													{labelMap[shippingPlan.calculation_base]?.min || labelMap.default.min}
												</th>
												<th className="w-[22%] text-start text-ec-body pb-1 text-sm font-normal leading-4 font-inter pl-[7px]">
													{labelMap[shippingPlan.calculation_base]?.max || labelMap.default.max}
												</th>
												<th className="w-[22%] text-start text-ec-body pb-1 text-sm font-normal leading-4 font-inter pl-[7px]">
													{sprintf( __( "Shipping Fee (%s)", "easycommerce" ), EASYCOMMERCE.currency_symbol || '$' )}
												</th>
												<th></th>
											</tr>
										</thead>
										<tbody>
											{shippingMethodList.map(
												(method, index) => (
													<tr key={index}>
														<td className="px-[7px]">
															<input
																className="h-ec-input p-3 mb-[14px] rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out w-full disabled:cursor-not-allowed disabled:bg-ec-table-stock disabled:hover:border-ec-table-stock disabled:focus:border-ec-table-stock"
																type="text"
																onChange={(e) =>
																	handleMethodItemChange(
																		e.target.value,
																		index,
																		"name"
																	)
																}
																value={method.name}
																placeholder={__( "Write name here", "easycommerce" )}
															/>
														</td>
														<td className="px-[7px]">
															<div className="flex grow-[1] h-ec-input rounded-lg font-inter text-[14px] mb-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus-within:border-ec-primary focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out">
																<input
																	className="p-4 rounded-lg font-inter text-[14px] leading-[20px] border-0 placeholder-ec-placeholder focus:border-0 focus:outline-none shadow-none transition-colors duration-300 ease-in-out w-full"
																	type="number"
																	min={0}
																	step={1}
																	onChange={(e) =>
																		handleMethodItemChange(
																			e.target.value,
																			index,
																			"min"
																		)
																	}
																	value={method.min}
																	placeholder={__( "Write min weight", "easycommerce" )}
																/>
																{shippingPlan.calculation_base === "weight" && (
																	<div className="relative" ref={(el) => (minDropdownRefs.current[index] = el)}>
																		<button
																			type="button"
																			className="w-[88px] h-full p-4 flex items-center justify-between font-inter text-base leading-6 text-ec-title bg-[#F9F9F9] border-r rounded-r-lg border-solid border-ec-table-stock"
																			onClick={() => {
																				const newDropdowns = [...minWeightDropdowns];
																				newDropdowns[index] = !newDropdowns[index];
																				setMinWeightDropdowns(newDropdowns);
																			}}
																			onBlur={() => {
																				const newDropdowns = [...minWeightDropdowns];
																				newDropdowns[index] = false;
																				setMinWeightDropdowns(newDropdowns);
																			}}
																		>
																			{method.min_unit}
																			<svg
																				xmlns="http://www.w3.org/2000/svg"
																				width="11"
																				height="6"
																				viewBox="0 0 11 6"
																				fill="none"
																			>
																				<path
																					d="M9.87109 1.71094L5.71484 5.62109C5.56901 5.7487 5.41406 5.8125 5.25 5.8125C5.08594 5.8125 4.9401 5.7487 4.8125 5.62109L0.65625 1.71094C0.382812 1.40104 0.373698 1.09115 0.628906 0.78125C0.920573 0.507812 1.23047 0.498698 1.55859 0.753906L5.25 4.25391L8.96875 0.753906C9.27865 0.498698 9.57943 0.498698 9.87109 0.753906C10.1263 1.08203 10.1263 1.40104 9.87109 1.71094Z"
																					fill="#3C3C42"
																				/>
																			</svg>
																		</button>
																		{minWeightDropdowns[index] &&
																			createPortal(
																				<ul
																					className="absolute border bg-white border-ec-border rounded-lg shadow-2xl z-[99] min-w-[160px]"
																					style={{
																						top: minDropdownPositions[index]?.top || 0,
																						left: minDropdownPositions[index]?.left || 0,
																					}}
																				>
																					{weightUnits.map((unit, unitIndex) => (
																						<li
																							key={unitIndex}
																							className="px-4 py-3 text-[14px] text-ec-body font-normal leading-[26px] hover:bg-[#F8F8F8] cursor-pointer rounded-[4px] m-0"
																							onMouseDown={() => {
																								handleUnitChange(unit.value, index, 'min');
																								const newDropdowns = [...minWeightDropdowns];
																								newDropdowns[index] = false;
																								setMinWeightDropdowns(newDropdowns);
																							}}
																						>
																							{unit.value}
																						</li>
																					))}
																				</ul>,
																				document.body
																			)}
																	</div>
																)}
															</div>
														</td>
														<td className="px-[7px]">
															<div className="flex grow-[1] h-ec-input rounded-lg font-inter text-[14px] mb-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus-within:border-ec-primary focus-within:outline-none focus-within:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out">
																<input
																	className="p-4 rounded-lg font-inter text-[14px] leading-[20px] border-0 placeholder-ec-placeholder focus:border-0 focus:outline-none shadow-none transition-colors duration-300 ease-in-out w-full"
																	type="number"
																	min={0}
																	step={1}
																	onChange={(e) =>
																		handleMethodItemChange(
																			e.target.value,
																			index,
																			"max"
																		)
																	}
																	value={method.max ?? ''}
																	placeholder={__( "Write max weight", "easycommerce" )}
																/>
																{shippingPlan.calculation_base === "weight" && (
																	<div className="relative" ref={(el) => (maxDropdownRefs.current[index] = el)}>
																		<button
																			type="button"
																			className="w-[88px] h-full p-4 flex items-center justify-between font-inter text-base leading-6 text-ec-title bg-[#F9F9F9] border-r rounded-r-lg border-solid border-ec-table-stock"
																			onClick={() => {
																				const newDropdowns = [...maxWeightDropdowns];
																				newDropdowns[index] = !newDropdowns[index];
																				setMaxWeightDropdowns(newDropdowns);
																			}}
																			onBlur={() => {
																				const newDropdowns = [...maxWeightDropdowns];
																				newDropdowns[index] = false;
																				setMaxWeightDropdowns(newDropdowns);
																			}}
																		>
																			{method.max_unit}
																			<svg
																				xmlns="http://www.w3.org/2000/svg"
																				width="11"
																				height="6"
																				viewBox="0 0 11 6"
																				fill="none"
																			>
																				<path
																					d="M9.87109 1.71094L5.71484 5.62109C5.56901 5.7487 5.41406 5.8125 5.25 5.8125C5.08594 5.8125 4.9401 5.7487 4.8125 5.62109L0.65625 1.71094C0.382812 1.40104 0.373698 1.09115 0.628906 0.78125C0.920573 0.507812 1.23047 0.498698 1.55859 0.753906L5.25 4.25391L8.96875 0.753906C9.27865 0.498698 9.57943 0.498698 9.87109 0.753906C10.1263 1.08203 10.1263 1.40104 9.87109 1.71094Z"
																					fill="#3C3C42"
																				/>
																			</svg>
																		</button>
																		{maxWeightDropdowns[index] &&
																			createPortal(
																				<ul
																					className="absolute border bg-white border-ec-border rounded-lg shadow-2xl z-[99] min-w-[160px]"
																					style={{
																						top: maxDropdownPositions[index]?.top || 0,
																						left: maxDropdownPositions[index]?.left || 0,
																					}}
																				>
																					{weightUnits.map((unit, unitIndex) => (
																						<li
																							key={unitIndex}
																							className="px-4 py-3 text-[14px] text-ec-body font-normal leading-[26px] hover:bg-[#F8F8F8] cursor-pointer rounded-[4px] m-0"
																							onMouseDown={() => {
																								handleUnitChange(unit.value, index, 'max');
																								const newDropdowns = [...maxWeightDropdowns];
																								newDropdowns[index] = false;
																								setMaxWeightDropdowns(newDropdowns);
																							}}
																						>
																							{unit.value}
																						</li>
																					))}
																				</ul>,
																				document.body
																			)}
																	</div>
																)}
															</div>
														</td>
														<td className="px-[7px]">
															<input
																className="h-ec-input p-3 mb-[14px] rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out w-full disabled:cursor-not-allowed disabled:bg-ec-table-stock disabled:hover:border-ec-table-stock disabled:focus:border-ec-table-stock"
																type="number"
																min={0}
																step={1}
																onChange={(e) =>
																	handleMethodItemChange(
																		e.target.value,
																		index,
																		"cost"
																	)
																}
																value={method.cost}
																placeholder={__( "Write your fee", "easycommerce" )}
															/>
														</td>
														<td className="flex items-center justify-center">
															<button
																onClick={() =>
																	handleAddMethodRow(
																		index
																	)
																}
																className="w-10 h-10 border border-ec-table-stock rounded-full mx-2 flex 
																items-center justify-center">
																<img
																	src={plusIcon}
																	alt=""
																/>
															</button>
															<button
																onClick={() =>
																	handleDeleteMethod(
																		index
																	)
																}
																className="w-10 h-10 border border-ec-table-stock rounded-full 
																flex items-center justify-center group hover:bg-[#FF3A521A]">
																<img
																	className="w-[14px] h-[16px] block group-hover:hidden"
																	src={deleteIcon}
																	alt="delete-pricing"
																/>
																<img
																	className="w-[14px] h-[16px] hidden group-hover:block"
																	src={deletehoverIcon}
																	alt="delete-pricing"
																/>
															</button>
														</td>
													</tr>
												)
											)}
										</tbody>
									</table>
								</div>
							</div>
						</p>
						<p className="mt-12 w-full flex justify-end items-center gap-[30px]">
							<button
								id="easycommerce-reset-settings"
								className="flex justify-center items-center font-inter border-b border-ec-body  text-ec-body focus:shadow-none focus:ec-body text-base"
								data-option_key="easycommerce-general-pages"
								onClick={handleCancel}>
								{ __( "Cancel", "easycommerce" ) }
							</button>
							<button
								onClick={
									ShippingPlanId
										? handleUpdateShipping
										: handleSaveShipping
								}
								id="easycommerce-save-settings"
								className="py-[12px] flex justify-center items-center gap-[8px] font-inter bg-white group 
								border border-ec-primary px-4 rounded-lg text-ec-primary hover:text-white 
								hover:bg-ec-primary focus:shadow-none focus:text-white focus:bg-ec-secondary 
								lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out duration-500 leading-[26px]">
								{ShippingPlanId
									? __( "Update Shipping Plan", "easycommerce" )
									: __( "Save Shipping Plan", "easycommerce" )}
							</button>
						</p>
					</div>
				</div>
			) : (
				<ShippingSkeleton />
			)}
		</>
	);
};

export default AddShipping;