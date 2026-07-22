import { __ } from "@wordpress/i18n";

const Sku = () => {
    return (
        <>
            <p className="text-ec-placeholder font-inter font-normal text-base leading-[26px] !mb-0">
                {__("SKU:", "easycommerce")} AV01-D-31
            </p>
        </>
    );
};

export default Sku;
