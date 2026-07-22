import React from "react";
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
const bannerImage = `${EASYCOMMERCE.assets}admin/img/icons/default-banner.svg`;

const Welcome = ({ handleNext }) => {
    /**
     * Filters the default page features list.
     *
     * @since 1.0.0
     * @param {Array} features The features array.
     */
    const features = applyFilters('easycommerce.wizard.default.features', [
        {
            label: __("Add Your Business", "easycommerce"),
            description: __("Tell us your store name, location, and contact details.", "easycommerce")
        },
        {
            label: __("Build Your Store", "easycommerce"),
            description: __("Get your Shop, Cart, and Checkout pages ready in one click.", "easycommerce")
        },
        {
            label: __("Get Paid", "easycommerce"),
            description: __("Connect a payment gateway and start taking orders today.", "easycommerce")
        }
    ]);

    return (
        <div className="pt-8 w-full bg-ec-main-bg">
            <div
                className="mb-12 w-[870px] px-[70px] pt-[120px] pb-[80px] mx-auto object-contain bg-white rounded-[20px]"
                style={{ backgroundImage: `url(${bannerImage})`, backgroundRepeat: "no-repeat" }}
            >
                {/* Updated headline with outcome-focused messaging */}
                <h2 className="text-ec-title text-[32px] leading-[42px] text-center font-semibold font-inter">
                    {__("Let’s Set Up Your Store", "easycommerce")}
                </h2>

                {/* Benefit-driven subheading with quantifiable promise */}
                <p className="text-ec-body text-base text-center font-normal mt-4 font-inter max-w-[650px] mx-auto">
                    {__("You’re minutes away from your first sale. Just 3 quick steps to go:", "easycommerce")}
                </p>

                {/* Benefit-driven subheading with quantifiable promise */}
                <div className="mt-6 max-w-[600px] mx-auto">
                    <ul className="mt-4 gap-y-2">
                        {features.map((item, key) => (
                            <li id={key} key={key} className="flex items-start">
                                <span className="h-5 w-5 mt-0.5 mr-2 flex-shrink-0 text-ec-primary border border-2 border-ec-primary rounded-full flex items-center justify-center text-xs font-semibold">
                                    {key + 1}
                                </span>
                                <span className="text-ec-body text-base">
                                    <strong>{item.label}</strong> - {item.description}
                                </span>
                            </li>
                        ))}
                    </ul>
                </div>

                {/* Consolidated CTAs with visual hierarchy */}
                <div className="flex flex-col items-center mt-[39px]">
                    {/* Primary CTA as single focus point */}
                    <button
                        onClick={handleNext}
                        type="button"
                        className="w-[230px] h-14 font-inter bg-ec-primary group border border-ec-primary 
                        rounded-lg text-white font-medium hover:text-white hover:bg-ec-secondary 
                        focus:shadow-none text-lg transition-all ease-in-out duration-300 
                        leading-[26px] flex items-center justify-center gap-2 shadow-md hover:shadow-lg"
                    >
                        {__("Get Started Now", "easycommerce")}
                        <img 
                            src={`${EASYCOMMERCE.assets}admin/img/icons/wizard-arrow-right.png`} 
                            alt="" 
                            className="ml-2 w-4 h-[11px] object-contain"
                        />
                    </button>
                </div>
            </div>
        </div>
    );
};

export default Welcome;