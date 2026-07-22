import React from "react";
import { __ } from "@wordpress/i18n";
import "./assets/style.css";

const logo = `${EASYCOMMERCE.assets}common/img/ec-logo.png`;

const LicenseScreen = ({
    onClose,
    addon,
    switchVariationModalTab,
}) => {
    const handleBuyLicense = () => {
        if (addon.status == "buyable") {
			window.open(addon.url, "_blank");
			return;
		}
    };
    return (
        <div className="fixed z-20 top-0 left-0 w-screen h-screen flex justify-center items-center bg-[#0000003B] backdrop-blur-sm">
            <div className="relative w-[548px] py-[32px] rounded-xl bg-white">
                <button
                    className="absolute top-[-18px] right-[-23px] group w-6 h-6 rounded-full bg-white hover:bg-[#fa4109] transition-colors duration-200 flex items-center justify-center"
                    onClick={onClose}
                    aria-label={ __( 'Close', 'easycommerce' ) }
                >
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        className="w-4 h-4"
                    >
                        <path
                            fillRule="evenodd"
                            clipRule="evenodd"
                            d="M7.26042 7.26042C7.60764 6.91319 8.17015 6.91319 8.51731 7.26042L12 10.7431L15.4827 7.26042C15.8299 6.91319 16.3924 6.91319 16.7396 7.26042C17.0868 7.60764 17.0868 8.17015 16.7396 8.51731L13.2569 12L16.7396 15.4827C17.0868 15.8299 17.0868 16.3924 16.7396 16.7396C16.3924 17.0868 15.8299 17.0868 15.4827 16.7396L12 13.2569L8.51731 16.7396C8.17009 17.0868 7.60759 17.0868 7.26042 16.7396C6.91325 16.3924 6.91319 15.8299 7.26042 15.4827L10.7431 12L7.26042 8.51731C6.91319 8.17009 6.91319 7.60759 7.26042 7.26042Z"
                            className="fill-[#3C3C42] group-hover:fill-white transition-colors duration-300"
                        />
                    </svg>
                </button>

                <div className="flex flex-col items-center gap-6">
                    <img
                        src={logo}
                        alt="easycommerce"
                        className="pointer-events-none w-[92px]"
                    />

                    <div className="flex flex-col items-center gap-[30px]">
                        <div className="flex flex-col items-center gap-3">
                            <h2 className="text-ec-title text-2xl font-inter font-medium">
                                {addon.name}
                            </h2>
                            <p className="w-[74%] mx-auto text-center text-ec-body font-inter font-normal text-base">
                                { __( 'If you already purchased the', 'easycommerce' ) } <strong>{addon.name}</strong> { __( 'addon, enter your license key below to activate it, or purchase a license now to start using its features.', 'easycommerce' ) }
                            </p>
                        </div>

                        <div className="w-[90%] flex justify-between items-center gap-4">
                            <button
                                className="w-full h-12 flex flrx-1 justify-center items-center font-medium font-inter text-ec-primary 
                                hover:text-white text-base leading-[26px] bg-white hover:bg-ec-primary rounded-lg border 
                                border-ec-primary transition-all ease-in-out duration-300"
                                onClick={switchVariationModalTab}
                            >
                                { __( 'Activate License', 'easycommerce' ) }
                            </button>
                            <button
                                className="w-full h-12 flex flrx-1 justify-center items-center font-medium font-inter text-base 
                                leading-[26px] text-white bg-ec-primary rounded-lg hover:bg-ec-secondary transition-all 
                                ease-in-out duration-300"
                                onClick={handleBuyLicense}
                            >
                                { __( 'Get Your License Now', 'easycommerce' ) }
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default LicenseScreen;
