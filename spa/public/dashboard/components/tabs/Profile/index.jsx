import React, { useEffect, useState } from "react";
import UserImage from "./elements/UserImage";

// toastify
import { Bounce, toast } from "react-toastify";
import ProfileSkeleton from "./elements/ProfileSkeleton";

const Profile = () => {
    const [userImage, setUserImage] = useState({});
    const [user, setUser] = useState(null);
    const [isLoading, setIsLoading] = useState(true);

    const handleFormSubmit = async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);

        const payload = {
            fields: {
                first_name: data.fname,
                last_name: data.lname,
                email: data.email,
                phone: data.phone,
                company_name: data.cname,
                tax_id: data.taxid,
                country: data.country,
                business_type: data.businessType,
                photo: userImage.url,
            },
        };

        try {
            easycommerce_modal(true);
            const response = await fetch(`${EASYCOMMERCE.rest_base}/me`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-WP-Nonce": EASYCOMMERCE.nonce,
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                throw new Error("Network response was not ok");
            }

            const result = await response.json();
            setUser((prevUser) => ({ ...prevUser, ...payload.fields }));
            easycommerce_modal(false);
            toast.success("Profile updated successfully", {
                position: "top-right",
                style: {
                    margin: "30px 0 0 0",
                    fontSize: "16px",
                    fontWeight: "500",
                    lineHeight: "26px",
                    color: "#fff",
                },
                autoClose: 3000,
                hideProgressBar: false,
                closeOnClick: true,
                pauseOnHover: false,
                draggable: true,
                progress: undefined,
                theme: "colored",
                transition: Bounce,
            });
        } catch (error) {
            toast.error("Profile update failed", {
                position: "top-right",
                style: {
                    margin: "30px 0 0 0",
                    fontSize: "16px",
                    fontWeight: "500",
                    lineHeight: "26px",
                    color: "#fff",
                },
                autoClose: 3000,
                hideProgressBar: false,
                closeOnClick: true,
                pauseOnHover: false,
                draggable: true,
                progress: undefined,
                theme: "colored",
                transition: Bounce,
            });
            easycommerce_modal(false);
        }
    };

    useEffect(() => {
        const params = new URLSearchParams({
            fields: [
                "first_name",
                "last_name",
                "email",
                "phone",
                "company_name",
                "tax_id",
                "country",
                "business_type",
                "photo",
            ],
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

                if (data.success && data.data?.customer) {
                    setUser(data.data.customer);
                    if (data.data.customer.photo) {
                        setUserImage({ url: data.data?.customer.photo });
                    }
                }
            });
    }, []);

    return (
        <>
            {!isLoading ? (
                <>
                    <div className="flex justify-center flex-col align-center gap-4 pb-8 border-b border-b-ec-border">
                        <div className="h-12">
                            <h3 className="easycommerce-dashboard-section-title !text-lg sm:!text-2xl">
                                Your profile
                            </h3>
                        </div>
                        <UserImage
                            userImage={userImage}
                            setUserImage={setUserImage}
                        />
                    </div>

                    <form
                        className="pt-[60px] flex flex-col gap-12"
                        onSubmit={handleFormSubmit}
                    >
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div className="col-span-1 flex flex-col gap-2 items-start">
                                <label
                                    htmlFor="fname"
                                    className="font-inter font-medium text-base leading-[26px] text-ec-body"
                                >
                                    First Name
                                </label>
                                <input
                                    type="text"
                                    name="fname"
                                    id="fname"
                                    className="easycommerce-dashboard-input"
                                    placeholder="Enter your first name"
                                    defaultValue={user?.first_name || ""}
                                />
                            </div>
                            <div className="col-span-1 flex flex-col gap-2 items-start">
                                <label
                                    htmlFor="lname"
                                    className="font-inter font-medium text-base leading-[26px] text-ec-body"
                                >
                                    Last Name
                                </label>
                                <input
                                    type="text"
                                    name="lname"
                                    id="lname"
                                    className="easycommerce-dashboard-input"
                                    placeholder="Enter your last name"
                                    defaultValue={user?.last_name || ""}
                                />
                            </div>
                            <div className="col-span-1 flex flex-col gap-2 items-start">
                                <label
                                    htmlFor="email"
                                    className="font-inter font-medium text-base leading-[26px] text-ec-body"
                                >
                                    Your Email
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    className="easycommerce-dashboard-input bg-gray-100"
                                    placeholder="Enter your email"
                                    defaultValue={user?.email || ""}
                                    disabled
                                />
                            </div>
                            <div className="col-span-1 flex flex-col gap-2 items-start">
                                <label
                                    htmlFor="phone"
                                    className="font-inter font-medium text-base leading-[26px] text-ec-body"
                                >
                                    Phone Number
                                </label>
                                <input
                                    type="text"
                                    name="phone"
                                    id="phone"
                                    className="easycommerce-dashboard-input"
                                    placeholder="Enter your phone number"
                                    defaultValue={user?.phone || ""}
                                />
                            </div>
                            <div className="col-span-1 flex flex-col gap-2 items-start">
                                <label
                                    htmlFor="cname"
                                    className="font-inter font-medium text-base leading-[26px] text-ec-body"
                                >
                                    Company Name
                                </label>
                                <input
                                    type="text"
                                    name="cname"
                                    id="cname"
                                    className="easycommerce-dashboard-input"
                                    placeholder="Enter your company name"
                                    defaultValue={user?.company_name || ""}
                                />
                            </div>
                            <div className="col-span-1 flex flex-col gap-2 items-start">
                                <label
                                    htmlFor="taxid"
                                    className="font-inter font-medium text-base leading-[26px] text-ec-body"
                                >
                                    Tax ID
                                </label>
                                <input
                                    type="text"
                                    name="taxid"
                                    id="taxid"
                                    className="easycommerce-dashboard-input"
                                    placeholder="Enter your tax ID"
                                    defaultValue={user?.tax_id || ""}
                                />
                            </div>
                            <div className="col-span-1 flex flex-col gap-2 items-start">
                                <label
                                    htmlFor="country"
                                    className="font-inter font-medium text-base leading-[26px] text-ec-body"
                                >
                                    Country
                                </label>
                                <select
                                    name="country"
                                    id="country"
                                    className="easycommerce-dashboard-input-select"
                                    defaultValue={user?.country || ""}
                                >
                                    <option value="">
                                        Select your country
                                    </option>
                                    {EASYCOMMERCE.countries &&
                                        Object.entries(
                                            EASYCOMMERCE.countries
                                        ).map(([code, countryName]) => (
                                            <option key={code} value={code}>
                                                {countryName}
                                            </option>
                                        ))}
                                </select>
                            </div>

                            <div className="col-span-1 flex flex-col gap-2 items-start">
                                <label
                                    htmlFor="businessType"
                                    className="font-inter font-medium text-base leading-[26px] text-ec-body"
                                >
                                    Business Type
                                </label>
                                <select
                                    name="businessType"
                                    id="businessType"
                                    className="easycommerce-dashboard-input-select"
                                    defaultValue={user?.business_type || ""}
                                >
                                    <option value="">
                                        Select your Business Type
                                    </option>
                                    <option value="manufacturing">
                                        Manufacturing
                                    </option>
                                    <option value="finance">
                                        Finance and Insurance
                                    </option>
                                    <option value="healthcare">
                                        Healthcare
                                    </option>
                                    <option value="realestate">
                                        Real Estate
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div className="easycommerce-dashboard-form-submit flex justify-end items-center gap-[17px]">
                            <button
                                className="easycommerce-dashboard-form-btn"
                                type="button"
                            >
                                Cancel
                            </button>
                            <button
                                className="easycommerce-dashboard-form-btn save"
                                type="submit"
                            >
                                Update Profile
                            </button>
                        </div>
                    </form>
                </>
            ) : (
                <ProfileSkeleton />
            )}
        </>
    );
};

export default Profile;
