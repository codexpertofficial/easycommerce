import React, { useEffect, useState } from "react";

// Components
import Regions from "./elements/Regions";
import TaxFormTopInputs from "./elements/TaxFormTopInputs";
import SaveButtons from "./elements/SaveButtons";
import { Bounce, toast } from "react-toastify";
import TaxSkeleton from "../TaxSkeleton";
import TableSkeleton from "../../../../common/TableSkeleton";

const initialRates = {
    country: "",
    state: "",
    city: "",
    rate: "",
    compound: true,
};

const TaxDetails = ({ hideAddNew, taxId, preloadedData = [] }) => {
    const [states, setStates] = useState([]);
    const [cities, setCities] = useState([]);
    const [taxData, setTaxData] = useState({
        name: "",
        description: "",
        status: true,
        rates: [{ ...initialRates }],
    });
    const [isLoading, setIsLoading] = useState(false);
    const [ratesLoader, setRatesLoader] = useState(false);

    useEffect(() => {
        if (!preloadedData || !preloadedData.rates) return;

        const { name, description, rates } = preloadedData;

        const populatePreloadedRates = async () => {
            setIsLoading(true);

            const updatedStates = [];
            const updatedCities = [];

            await Promise.all(
                rates.map(async (rate, index) => {
                    const country = rate.country || "US";
                    let statesList = [];
                    let citiesList = [];

                    const stateRes = await fetch(`${EASYCOMMERCE.rest_base}/geo/states/${country}`, {
                        headers: { "X-WP-Nonce": EASYCOMMERCE.nonce },
                    });
                    const stateData = await stateRes.json();
                    statesList = stateData.data?.states || [];
                    updatedStates[index] = statesList;

                    const selectedState = statesList.includes(rate.state) ? rate.state : "";

                    if (selectedState) {
                        const cityRes = await fetch(`${EASYCOMMERCE.rest_base}/geo/cities/${country}?state=${selectedState}`, {
                            headers: { "X-WP-Nonce": EASYCOMMERCE.nonce },
                        });
                        const cityData = await cityRes.json();
                        citiesList = cityData.data?.cities || [];
                    }
                    updatedCities[index] = citiesList;

                    rates[index] = {
                        country,
                        state: selectedState,
                        city: citiesList.includes(rate.city) ? rate.city : "",
                        rate: rate.combined_rate || "",
                        compound: true,
                    };
                })
            );

            setStates([...updatedStates]);
            setCities([...updatedCities]);

            // Only override name/description if provided (from dropdown)
            setTaxData((prev) => ({
                ...prev,
                name: name || prev.name,
                description: description || prev.description,
                rates: [...rates],
            }));

            setIsLoading(false);
        };

        populatePreloadedRates();
    }, [preloadedData]);
    
    const showToast = (type, message) => {
        toast[type](message, {
            position: "top-right",
            style: {
                margin: "30px 0 0 0",
                fontSize: "16px",
                fontWeight: "500",
                lineHeight: "26px",
                color: "#fff",
            },
            autoClose: 1500,
            hideProgressBar: false,
            closeOnClick: true,
            pauseOnHover: true,
            draggable: false,
            progress: undefined,
            theme: "colored",
            transition: Bounce,
        });
    };

    const handleTaxDataChange = (key, value) => {
        setTaxData((prevData) => ({ ...prevData, [key]: value }));
    };

    const handleAddRegion = (index) => {
        const rates = taxData.rates.map((rate) => ({ ...rate }));
        rates.splice(index + 1, 0, { ...initialRates });

        const updatedStates = [...states];
        const updatedCities = [...cities];

        // Add an empty array at the specified index for both states and cities
        updatedStates.splice(index + 1, 0, []);
        updatedCities.splice(index + 1, 0, []);

        setTaxData((prevTaxData) => ({
            ...prevTaxData,
            rates,
        }));

        setStates(updatedStates);
        setCities(updatedCities);
    };

    const handleRemoveRegion = (index) => {
        const rates = taxData.rates.map((region) => ({ ...region }));

        if (rates.length <= 1) {
            return;
        }

        rates.splice(index, 1);

        const updatedStates = [...states];
        const updatedCities = [...cities];

        // Remove the state and city at the specific index
        updatedStates.splice(index, 1);
        updatedCities.splice(index, 1);

        setTaxData((prevTaxData) => ({
            ...prevTaxData,
            rates,
        }));

        setStates(updatedStates);
        setCities(updatedCities);
    };

    const handleRegionChange = (index, key, value) => {
        const rates = taxData.rates.map((rate) => ({ ...rate }));

        if (key === "country") {
            if (value === "") {
                const updatedStates = [...states];
                const updatedCities = [...cities];

                updatedStates[index] = [];
                updatedCities[index] = [];

                setStates(updatedStates);
                setCities(updatedCities);
            }

            fetchAndStoreStates(value, index);

            // reset state and city value in regionList
            rates[index].state = "";
            rates[index].city = "";
        } else if (key === "state") {
            if (value === "") {
                const updatedCities = [...cities];

                updatedCities[index] = [];

                setCities(updatedCities);
            }

            fetchAndStoreCities(rates[index].country, value, index);

            // reset city value in regionList
            rates[index].city = "";
        }

        rates[index][key] = value;
        setTaxData((prevTaxData) => ({ ...prevTaxData, rates }));
    };

    const fetchAndStoreStates = (country, index) => {
        if (!country) return;

        fetch(`${EASYCOMMERCE.rest_base}/geo/states/${country}`,{
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

                    // reset citylist in the same index in cities
                    const updatedCities = [...cities];
                    updatedCities[index] = [];
                    setCities(updatedCities);
                }
            });
    };

    const fetchAndStoreCities = (country, state, index) => {
        if (!country || !state) return;

        fetch(`${EASYCOMMERCE.rest_base}/geo/cities/${country}?state=${state}`,{
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
                }
            });
    };

    const handleSave = () => {
        easycommerce_modal(true);

        const formData = {
            ...taxData,
            rates: taxData.rates.map((rate) => ({
                ...rate,
                compound: rate.compound === true ? 1 : 0,
            })),
        };

        fetch(`${EASYCOMMERCE.rest_base}/taxes`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify(formData),
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success && data.data?.id) {
                    showToast("success", data.data.message);
                    hideAddNew();
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showToast("error", data.data.message);
                }
            })
    };

    const handleUpdate = () => {
        easycommerce_modal(true);

        const formData = {
            ...taxData,
            rates: taxData.rates.map((rate) => ({
                ...rate,
                compound: rate.compound === true ? 1 : 0,
            })),
        };

        fetch(`${EASYCOMMERCE.rest_base}/taxes/${taxId}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify(formData),
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success && data.data?.id) {
                    showToast("success", data.data.message);
                    hideAddNew();
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showToast("error", data.data.message);
                }
            })
    };

    const updateStateAndCityLists = async (rates) => {
        const updatedStates = [];
        const updatedCities = [];

        // Track the total number of fetches
        const fetchPromises = rates.map((rate, index) => {
            // Fetch states for the rate
            return fetch(`${EASYCOMMERCE.rest_base}/geo/states/${rate.country}`,{
                headers: {
                    "Content-Type": "application/json",
                    "X-WP-Nonce": EASYCOMMERCE.nonce,
                }
            })
                .then((res) => res.json())
                .then((data) => {
                    if (data.success && data.data?.states) {
                        updatedStates[index] = data.data.states;

                        // If the rate has a state, fetch cities
                        if (
                            rate.state &&
                            data.data.states.includes(rate.state)
                        ) {
                            return fetch(
                                `${EASYCOMMERCE.rest_base}/geo/cities/${rate.country}?state=${rate.state}`,{
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

        // Wait for all fetches to complete
        await Promise.all(fetchPromises);

        // Update states and cities
        setStates(updatedStates);
        setCities(updatedCities);
    };

    useEffect(() => {
        if (!taxId) return;

        setIsLoading(true);

        fetch(`${EASYCOMMERCE.rest_base}/taxes/${taxId}`,{
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            }
        })
            .then((res) => res.json())
            .then(async (data) => {
                setTaxData(data.data.class);
                setIsLoading(false);
                const rates = data.data.class.rates;
                // Update states and cities
                setRatesLoader(true);
                await updateStateAndCityLists(rates);
                setRatesLoader(false);
            });
    }, [taxId]);

    return (
        <>
            {!isLoading ? (
                <div className="bg-white mt-8">
                    <div>
                        <TaxFormTopInputs
                            name={taxData.name}
                            description={taxData.description}
                            status={taxData.status}
                            onChange={handleTaxDataChange}
                        />

                        <p className="flex items-start py-4">
                            <label
                                className="text-ec-body font-inter font-normal text-base leading-4 w-[180px]"
                                htmlFor="easycommerce-tax-regions"
                            >
                                Rates
                            </label>
                            {!ratesLoader ? (
                                <Regions
                                    rates={taxData.rates}
                                    states={states}
                                    cities={cities}
                                    handleAddRegion={handleAddRegion}
                                    handleRemoveRegion={handleRemoveRegion}
                                    handleRegionChange={handleRegionChange}
                                />
                            ) : (
                                <TableSkeleton
                                    numberOfRows={5}
                                    SkeletonHeight={15}
                                />
                            )}
                        </p>

                        <SaveButtons
                            onCancel={hideAddNew}
                            onSave={taxId ? handleUpdate : handleSave}
                            taxId={taxId}
                        />
                    </div>
                </div>
            ) : (
                <TaxSkeleton />
            )}
        </>
    );
};

export default TaxDetails;