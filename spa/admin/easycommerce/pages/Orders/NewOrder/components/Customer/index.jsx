import React, { useState } from "react";
import { useDispatch } from "react-redux";

// redux slice
import { addToastData } from "../../../../../redux-store/slices/toastSlice";

// components
import CustomerName from "./CustomerName";
import ShippingFields from "./FormFields/ShippingFields";
import BillingFields from "./FormFields/BillingFields";

//Image & Icons
const placeholder = `${EASYCOMMERCE.assets}admin/img/preloader-Image.png`;
const cameraIcon = `${EASYCOMMERCE.assets}admin/img/icons/camera-icon.png`;

const Customer = ({ setCustomerId, setCustomerAddress }) => {
    const dispatch = useDispatch();
    const [showCustomerForm, setShowCustomerForm] = useState(false);
    const [isExistingCustomer, setIsExistingCustomer] = useState(false);
    const [showPasswordError, setShowPasswordError] = useState(false);
    const [customer, setCustomer] = useState({
        first_name: "",
        last_name: "",
        email: "",
        photo: "",
        password: "",
        password_again: "",
        shipping: {
            shipping_first_name: "",
            shipping_last_name: "",
            shipping_email: "",
            shipping_phone: "",
            shipping_address_1: "",
            shipping_address_2: "",
            shipping_city: "",
            shipping_state: "",
            shipping_postcode: "",
            shipping_country: "",
        },
        billing: {
            billing_first_name: "",
            billing_last_name: "",
            billing_email: "",
            billing_phone: "",
            billing_address_1: "",
            billing_address_2: "",
            billing_city: "",
            billing_state: "",
            billing_postcode: "",
            billing_country: "",
        },
    });

    function restructureCustomerObject(customerData) {
        const newCustomer = {
            ...customerData,
            meta: {
                shipping_address: customerData.shipping,
                billing_address: customerData.billing,
            },
        };

        delete newCustomer.shipping;
        delete newCustomer.billing;

        return newCustomer;
    }

    const handleSubmit = (e) => {
        e.preventDefault();

        if (customer.password !== customer.password_again) {
            setShowPasswordError(true);

            dispatch(
                addToastData({
                    type: "error",
                    message: "Passwords do not match",
                })
            );

            return;
        }

        const customerData = restructureCustomerObject(customer);

        easycommerce_modal(true);

        fetch(`${EASYCOMMERCE.rest_base}/customers`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
            body: JSON.stringify(customerData),
        })
            .then((res) => res.json())
            .then((data) => {
                easycommerce_modal(false);

                if (data.success) {
                    setCustomerId(data.data.customer_id);
                    setCustomerAddress(customer.billing, customer.shipping);

                    dispatch(
                        addToastData({
                            type: "success",
                            message: "Customer created successfully",
                        })
                    );
                } else {
                    dispatch(
                        addToastData({
                            type: "error",
                            message: "Failed to create customer",
                        })
                    );
                }
            });
    };

    const openMediaLibrary = () => {
        if (isExistingCustomer) return;

        // Create the media frame.
        const frame = wp.media({
            title: "Select a Image",
            button: {
                text: "Use selected image",
            },
            multiple: false,
        });

        // When an image is selected in the media frame...
        frame.on("select", () => {
            const attachments = frame.state().get("selection").toJSON();
            setCustomer({
                ...customer,
                photo: attachments[0].url,
            });
        });

        // Finally, open the modal on click
        frame.open();
    };

    const fetchCustomerById = (id) => {
        fetch(`${EASYCOMMERCE.rest_base}/customers/${id}`,{
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": EASYCOMMERCE.nonce,
            },
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success && data.data?.customer) {
                    setCustomer(data.data.customer);
                    setIsExistingCustomer(true);
                    setShowCustomerForm(true);

                    setCustomerId(id);
                    setCustomerAddress(
                        data.data.customer.billing,
                        data.data.customer.shipping
                    );
                }
            });
    };

    return (
        <div className="w-1/4 bg-white min-h-svh">
            <div className="w-full px-4 py-6 mb-6 border-b border-[#DBDBDB] flex flex-col justify-center">
                <h3 className="text-ec-body font-inter font-semibold text-xl leading-8">
                    Customer Details
                </h3>
            </div>

            <div className="pt-6 px-4 pb-10">
                {!showCustomerForm && (
                    <div className="mb-4">
                        <CustomerName
                            showForm={() => setShowCustomerForm(true)}
                            fetchCustomerById={fetchCustomerById}
                            setCustomerId={setCustomerId}
                        />
                    </div>
                )}

                {showCustomerForm && (
                    <form onSubmit={handleSubmit}>
                        <div className="py-4 px-3 rounded-md border border-ec-border">
                            <h4 className="text-ec-body font-inter font-semibold text-base leading-[26px] text-center mb-4">
                                Add New Customer
                            </h4>
                            <div className="flex justify-center mb-4">
                                <button
                                    type="button"
                                    onClick={openMediaLibrary}
                                    className={`w-[70px] h-[70px] relative rounded-full ${
                                        customer.photo
                                            ? ""
                                            : "border border-[#DBDBDB]"
                                    }`}
                                >
                                    <img
                                        src={customer.photo || placeholder}
                                        className={`w-full h-full rounded-full pointer-events-none`}
                                        alt="image"
                                    />
                                    <span class="w-7 h-7 flex justify-center items-center  backdrop-blur-sm rounded-full absolute bottom-0 -right-1 border border-[#EEEEEE]">
                                        <img
                                            src={cameraIcon}
                                            alt="camera"
                                            class="w-[12px] h-[12px] pointer-events-none"
                                        />
                                    </span>
                                </button>
                            </div>
                            <div>
                                <label
                                    htmlFor="easycommerce-new-order-first-name"
                                    className="block mb-4"
                                >
                                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                                        First Name
                                    </span>
                                    <input
                                        id="easycommerce-new-order-first-name"
                                        type="text"
                                        name="first_name"
                                        placeholder="Your first name"
                                        value={customer.first_name}
                                        onChange={(e) =>
                                            setCustomer({
                                                ...customer,
                                                first_name: e.target.value,
                                            })
                                        }
                                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm 
                                        placeholder:text-sm font-inter text-sm text-ec-body"
                                    />
                                </label>
                                <label
                                    htmlFor="easycommerce-new-order-last-name"
                                    className="block mb-4"
                                >
                                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                                        Last Name
                                    </span>
                                    <input
                                        id="easycommerce-new-order-last-name"
                                        type="text"
                                        name="last_name"
                                        placeholder="Your last name"
                                        value={customer.last_name}
                                        onChange={(e) =>
                                            setCustomer({
                                                ...customer,
                                                last_name: e.target.value,
                                            })
                                        }
                                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm 
                                        placeholder:text-sm font-inter text-sm text-ec-body"
                                    />
                                </label>
                                <label
                                    htmlFor="easycommerce-new-order-email"
                                    className="block mb-4"
                                >
                                    <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                                        Email
                                    </span>
                                    <input
                                        id="easycommerce-new-order-email"
                                        type="email"
                                        name="email"
                                        placeholder="Your Email"
                                        autocomplete="off"
                                        value={customer.email}
                                        onChange={(e) =>
                                            setCustomer({
                                                ...customer,
                                                email: e.target.value,
                                            })
                                        }
                                        className="easycommerce-order-input w-full h-[42px] border border-ec-border rounded-sm 
                                        placeholder:text-sm font-inter text-sm text-ec-body"
                                    />
                                </label>
                                {!isExistingCustomer && (
                                    <>
                                        <label
                                            htmlFor="easycommerce-new-order-password"
                                            className="block mb-4"
                                        >
                                            <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                                                Password
                                            </span>
                                            <input
                                                id="easycommerce-new-order-password"
                                                type="password"
                                                name="password"
                                                placeholder="Write your password"
                                                autocomplete="off"
                                                value={customer.password}
                                                minLength={6}
                                                onChange={(e) => {
                                                    setCustomer({
                                                        ...customer,
                                                        password:
                                                            e.target.value,
                                                    });
                                                    setShowPasswordError(false);
                                                }}
                                                className={`easycommerce-order-input ${
                                                    showPasswordError
                                                        ? "error"
                                                        : ""
                                                } w-full h-[42px] border border-ec-border rounded-sm 
                                                placeholder:text-sm font-inter text-sm text-ec-body 
                                                `}
                                            />
                                        </label>
                                        <label
                                            htmlFor="easycommerce-new-order-password-again"
                                            className="block mb-4"
                                        >
                                            <span className="block mb-1 text-ec-body text-base font-inter leading-8">
                                                Password again
                                            </span>
                                            <input
                                                id="easycommerce-new-order-password-again"
                                                type="password"
                                                name="password_again"
                                                placeholder="Write your password again"
                                                autocomplete="off"
                                                minLength={6}
                                                value={customer.password_again}
                                                onChange={(e) => {
                                                    setCustomer({
                                                        ...customer,
                                                        password_again:
                                                            e.target.value,
                                                    });
                                                    setShowPasswordError(false);
                                                }}
                                                className={`easycommerce-order-input ${
                                                    showPasswordError
                                                        ? "error"
                                                        : ""
                                                } w-full h-[42px] border border-ec-border rounded-sm 
                                                placeholder:text-sm font-inter text-sm text-ec-body 
                                                `}
                                            />
                                        </label>
                                    </>
                                )}

                                <ShippingFields
                                    shippingData={customer.shipping}
                                    updateShippingData={(key, value) =>
                                        setCustomer({
                                            ...customer,
                                            shipping: {
                                                ...customer.shipping,
                                                [key]: value,
                                            },
                                        })
                                    }
                                />

                                <BillingFields
                                    billingData={customer.billing}
                                    updateBillingData={(key, value) =>
                                        setCustomer({
                                            ...customer,
                                            billing: {
                                                ...customer.billing,
                                                [key]: value,
                                            },
                                        })
                                    }
                                    setBillingAsShipping={() => {
                                        setCustomer((prevState) => {
                                            const billing = Object.entries(
                                                prevState.shipping
                                            ).reduce((acc, [key, value]) => {
                                                const billingKey = key.replace(
                                                    "shipping_",
                                                    "billing_"
                                                );

                                                acc[billingKey] = value;
                                                return acc;
                                            }, {});

                                            return {
                                                ...prevState,
                                                billing,
                                            };
                                        });
                                    }}
                                />
                            </div>
                        </div>

                        {!isExistingCustomer && (
                            <button
                                type="submit"
                                className="float-right font-inter font-normal text-base leading-[26px] border border-ec-body rounded-lg
                                my-4 py-2 px-[18px] text-ec-body hover:text-white hover:bg-ec-primary hover:border-ec-primary transition duration-300"
                            >
                                Confirm
                            </button>
                        )}
                    </form>
                )}
            </div>
        </div>
    );
};

export default Customer;
