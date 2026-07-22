import React, { useEffect, useState } from "react";
import { __ } from "@wordpress/i18n";
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
            toast.success(__( "Profile updated successfully", "easycommerce" ), {
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
            toast.error(__( "Profile update failed", "easycommerce" ), {
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
                                {__( "Your profile", "easycommerce" )}
                            </h3>
                        </div>
                        <UserImage
                            userImage={userImage}
                            setUserImage={setUserImage}
                        />
                    </div>

                    <form
                        className="pt-10 flex flex-col gap-8"
                        onSubmit={handleFormSubmit}
                    >
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-5 bg-white border border-ec-border rounded-2xl p-6 shadow-[0_2px_16px_-8px_rgba(18,3,80,0.10)]">
                            <div className="col-span-1 flex flex-col gap-2 items-start">
                                <label
                                    htmlFor="fname"
                                    className="font-inter font-medium text-base leading-[26px] text-ec-body"
                                >
                                    {__( "First Name", "easycommerce" )}
                                </label>
                                <input
                                    type="text"
                                    name="fname"
                                    id="fname"
                                    className="easycommerce-dashboard-input"
                                    placeholder={__( "Enter your first name", "easycommerce" )}
                                    defaultValue={user?.first_name || ""}
                                />
                            </div>
                            <div className="col-span-1 flex flex-col gap-2 items-start">
                                <label
                                    htmlFor="lname"
                                    className="font-inter font-medium text-base leading-[26px] text-ec-body"
                                >
                                    {__( "Last Name", "easycommerce" )}
                                </label>
                                <input
                                    type="text"
                                    name="lname"
                                    id="lname"
                                    className="easycommerce-dashboard-input"
                                    placeholder={__( "Enter your last name", "easycommerce" )}
                                    defaultValue={user?.last_name || ""}
                                />
                            </div>
                            <div className="col-span-1 flex flex-col gap-2 items-start">
                                <label
                                    htmlFor="email"
                                    className="font-inter font-medium text-base leading-[26px] text-ec-body"
                                >
                                    {__( "Your Email", "easycommerce" )}
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    className="easycommerce-dashboard-input bg-gray-100"
                                    placeholder={__( "Enter your email", "easycommerce" )}
                                    defaultValue={user?.email || ""}
                                    disabled
                                />
                            </div>
                            <div className="col-span-1 flex flex-col gap-2 items-start">
                                <label
                                    htmlFor="phone"
                                    className="font-inter font-medium text-base leading-[26px] text-ec-body"
                                >
                                    {__( "Phone Number", "easycommerce" )}
                                </label>
                                <input
                                    type="text"
                                    name="phone"
                                    id="phone"
                                    className="easycommerce-dashboard-input"
                                    placeholder={__( "Enter your phone number", "easycommerce" )}
                                    defaultValue={user?.phone || ""}
                                />
                            </div>
                            <div className="col-span-1 flex flex-col gap-2 items-start">
                                <label
                                    htmlFor="cname"
                                    className="font-inter font-medium text-base leading-[26px] text-ec-body"
                                >
                                    {__( "Company Name", "easycommerce" )}
                                </label>
                                <input
                                    type="text"
                                    name="cname"
                                    id="cname"
                                    className="easycommerce-dashboard-input"
                                    placeholder={__( "Enter your company name", "easycommerce" )}
                                    defaultValue={user?.company_name || ""}
                                />
                            </div>
                            <div className="col-span-1 flex flex-col gap-2 items-start">
                                <label
                                    htmlFor="taxid"
                                    className="font-inter font-medium text-base leading-[26px] text-ec-body"
                                >
                                    {__( "Tax ID", "easycommerce" )}
                                </label>
                                <input
                                    type="text"
                                    name="taxid"
                                    id="taxid"
                                    className="easycommerce-dashboard-input"
                                    placeholder={__( "Enter your tax ID", "easycommerce" )}
                                    defaultValue={user?.tax_id || ""}
                                />
                            </div>
                            <div className="col-span-1 flex flex-col gap-2 items-start">
                                <label
                                    htmlFor="country"
                                    className="font-inter font-medium text-base leading-[26px] text-ec-body"
                                >
                                    {__( "Country", "easycommerce" )}
                                </label>
                                <select
                                    name="country"
                                    id="country"
                                    className="easycommerce-dashboard-input-select"
                                    defaultValue={user?.country || ""}
                                >
                                    <option value="">
                                        {__( "Select your country", "easycommerce" )}
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
                                    {__( "Business Type", "easycommerce" )}
                                </label>
                                <select
                                    name="businessType"
                                    id="businessType"
                                    className="easycommerce-dashboard-input-select"
                                    defaultValue={user?.business_type || ""}
                                >
                                    <option value="">
                                        {__( "Select your Business Type", "easycommerce" )}
                                    </option>
                                    <option value="manufacturing">
                                        {__( "Manufacturing", "easycommerce" )}
                                    </option>
                                    <option value="finance">
                                        {__( "Finance and Insurance", "easycommerce" )}
                                    </option>
                                    <option value="healthcare">
                                        {__( "Healthcare", "easycommerce" )}
                                    </option>
                                    <option value="realestate">
                                        {__( "Real Estate", "easycommerce" )}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div className="flex justify-end items-center gap-3">
                            <button
                                className="easycommerce-dashboard-form-btn"
                                type="button"
                            >
                                {__( "Cancel", "easycommerce" )}
                            </button>
                            <button
                                className="easycommerce-dashboard-form-btn save"
                                type="submit"
                            >
                                {__( "Update Profile", "easycommerce" )}
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
