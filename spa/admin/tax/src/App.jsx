import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

import './assets/css/tax.css';
import TaxList from './components/TaxList';
import TaxDetails from './components/TaxDetails';
import Dropdown from '../../common/components/inputs/Dropdown';
import { __, sprintf } from '@wordpress/i18n';

const App = () => {
    const [addNew, setAddNew] = useState(false);
    const [TaxIdToEdit, setTaxIdToEdit] = useState(null);

    const [availableCountries, setAvailableCountries] = useState([]);
    const [dropdownOptions, setDropdownOptions] = useState([]);
    const [showDropdown, setShowDropdown] = useState(false);
    const [selectedValue, setSelectedValue] = useState("");

    const [countryRates, setCountryRates] = useState([]); // Stores state + combined rate

    // Get available countries from global EASYCOMMERCE object
    useEffect(() => {
        if (typeof EASYCOMMERCE !== 'undefined' && EASYCOMMERCE.tax?.countries) {
            const countries = Object.keys(EASYCOMMERCE.tax.countries);
            setAvailableCountries(countries);

            // Fetch matched CSV files from backend
            fetch(`${EASYCOMMERCE.rest_base}/taxes/tax-files`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-WP-Nonce": EASYCOMMERCE.nonce,
                },
                credentials: "same-origin",
                body: JSON.stringify({ countries }),
            })
            .then(res => res.json())
            .then(data => {
                const files = data.success ? data.data : [];
                if (Array.isArray(files) && files.length > 0) {
                    const options = files.map(code => ({
                        value: code.toLowerCase(),
                        label: code.toUpperCase(),
                    }));
                    setDropdownOptions(options);
                    setShowDropdown(true);
                }
            })
            .catch(err => console.error("Error fetching tax files:", err));
        }
    }, []);

    // Handle dropdown selection
    const handleDropdownChange = (option) => {
        setSelectedValue(option.value);

        fetch(`${EASYCOMMERCE.rest_base}/taxes/rates?country=${option.value}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            credentials: "same-origin",
        })
        .then(res => res.json())
        .then(data => {
            const rates = data.success ? data.data : [];

            setCountryRates({
                name: `${option.label}`,
                // translators: %s: country name.
                description: sprintf( __( 'Tax rates for all areas of %s', 'easycommerce' ), option.label ),
                rates: rates,
            });
        })
        .catch(err => console.error("Error fetching country rates:", err));
    };

    const handleEdit = (id) => {
        setAddNew(true);
        setTaxIdToEdit(id);
    };

    const handleAddNew = () => {
        setAddNew(true);
    };

    return (
        <>
            <div className="flex items-center gap-8">
                <p className="text-ec-body font-medium font-inter lg:text-xl md:text-lg leading-8 flex-grow whitespace-nowrap">
                    {addNew ? __( 'Add Tax Class', 'easycommerce' ) : ''}
                </p>
                {addNew && showDropdown && (
                    <Dropdown
                        options={dropdownOptions}
                        placeholder={__( 'Populate Tax Rates', 'easycommerce' )}
                        value={selectedValue}
                        onChange={handleDropdownChange}
                        minWidthClass="min-w-[180px]"
                    />
                )}
            </div>

            {!addNew && (
                <TaxList handleEdit={handleEdit} handleAddNew={handleAddNew} />
            )}

            {addNew && (
                <TaxDetails
                    hideAddNew={() => setAddNew(false)}
                    taxId={TaxIdToEdit}
                    preloadedData={countryRates}
                />
            )}

            <ToastContainer />
        </>
    );
};

const container = document.getElementById('easycommerce-tax-classes');
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}

export default App;
