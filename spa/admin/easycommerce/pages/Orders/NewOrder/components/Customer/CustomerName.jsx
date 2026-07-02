import React, { useState } from "react";

const CustomerName = ({ showForm, fetchCustomerById }) => {
    const [showSuggestion, setShowSuggestion] = useState(false);
    const [customers, setCustomers] = useState([]);

    const handleChange = (e) => {
        const customerQuery = e.target.value;

        if (customerQuery.length < 3) {
            return;
        }

        fetch(`${EASYCOMMERCE.rest_base}/customers?s=${customerQuery}`,{
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success && data.data?.customers) {
                    setCustomers(data.data.customers);
                }
            });
    };

    const handleClick = (id) => {
        setShowSuggestion(false);

        // fetch customer by id
        fetchCustomerById(id);
    };

    return (
        <div className="relative">
            <h5 className="text-ec-body font-inter font-medium text-base leading-8">
                Customer name
            </h5>
            <input
                type="text"
                placeholder="Customer Name"
                onFocus={() => setShowSuggestion(true)}
                onBlur={() => setTimeout(() => setShowSuggestion(false), 200)}
                onChange={handleChange}
                className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm placeholder:text-sm 
                font-inter text-sm text-ec-body"
            />
            {showSuggestion && (
                <ul className="absolute right-0 top-20 min-w-48 bg-white border border-ec-border rounded-xl">
                    {customers.length > 0 &&
                        customers.map((customer, index) => (
                            <li
                                key={index}
                                onClick={() => handleClick(customer.id)}
                                className="p-3 font-inter font-normal text-base leading-[26px] hover:bg-[#F8F8F8] rounded-[4px] 
                                cursor-pointer mb-0"
                            >
                                {customer.name}
                            </li>
                        ))}

                    <li
                        onClick={showForm}
                        className="p-3 font-inter font-normal text-base leading-[26px] hover:bg-[#F8F8F8] rounded-[4px] cursor-pointer mb-0"
                    >
                        Add New Customer
                    </li>
                </ul>
            )}
        </div>
    );
};

export default CustomerName;
