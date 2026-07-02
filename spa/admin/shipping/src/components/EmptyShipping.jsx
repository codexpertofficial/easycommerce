import React from "react";
import { __ } from "@wordpress/i18n"

const icon = `${EASYCOMMERCE.assets}admin/img/nofound/no-customer.png`;

const EmptyShipping = ({ handleAddNew }) => {
    return (
        <div className="text-center py-[120px]">
            <img className="mx-auto mb-[35px] w-[95px] h-[115px]" src={icon} alt="" />
            <h2 className="font-inter text-2xl mb-[5px] font-medium leading-8 text-ec-title">
               { __( 'No shipping plans configured', 'easycommerce' ) }
            </h2>
            <p className="font-inter text-base mb-6 font-normal text-ec-light-black">
                { __( 'Set up shipping plans to define shipping rates by region. ', 'easycommerce' ) }
                <a 
                    className="font-inter text-base font-normal leading-5 text-ec-primary" 
                    href="https://easycommerce.dev/docs/settings/shipping-settings" 
                    target="_blank"
                >
                    { __( 'Read docs.', 'easycommerce' ) }
                </a>
            </p>
            <button
                onClick={handleAddNew}
                className="text-ec-primary font-inter text-base h-[45px] px-8 border border-ec-primary rounded-lg hover:text-white hover:bg-ec-primary transition-all 
                        ease-in-out duration-500"
            >
                { __( '+ Add Plan', 'easycommerce' ) }
            </button>
        </div>
    );
};

export default EmptyShipping;
