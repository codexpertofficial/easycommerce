import React from "react";
import { __ } from '@wordpress/i18n';

const deletePopUpClose = `${EASYCOMMERCE.assets}admin/img/icons/delete-popup-close.png`;
const deleteWarningBg = `${EASYCOMMERCE.assets}admin/img/delete-warning-bg.png`;
const deleteIcon = `${EASYCOMMERCE.assets}admin/img/common-delete.png`;

/**
 * Popup component displays a modal confirmation dialog for replacing variants.
 *
 * @component
 * @param {Object} props - Component props.
 * @param {Function} props.onClose - Callback function to close the popup.
 * @param {Function} props.onConfirm - Callback function to confirm the replacement action.
 * @param {string} [props.itemName=''] - Name of the item to be replaced, displayed in the message.
 * @param {string} [props.popupImage=deleteIcon] - Image URL or import to display in the popup.
 * @returns {JSX.Element} The rendered Popup component.
 */
const Popup = ({
    onClose,    
    onConfirm,
    itemName = '',
    popupImage = deleteIcon,
}) => {
    return (
        <div className="fixed top-0 left-0 w-screen h-screen flex items-center justify-center bg-[#00000082] backdrop-blur-sm z-[999999]">
            <div className="relative w-[451px] flex flex-col justify-center items-center bg-white rounded-xl pb-[40px]">
                <div
                    className="w-[451px] pt-[30px] pb-[30px] flex flex-col justify-center items-center mb-[24px] rounded-t-xl bg-cover bg-center bg-no-repeat"
                    style={{ backgroundImage: `url(${deleteWarningBg})` }}
                >
                    <div
                        className="w-[120px] h-[120px] bg-white flex justify-center items-center rounded-xl shadow-[0px_18px_22.2px_0px_#DBD3FF"
                    >
                        <img src={popupImage} alt="delete-attribute" className="w-[80px] h-[80px]" />
                    </div>
                    <button
                        onClick={onClose}
                        className="absolute top-4 right-6"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="10"
                            height="10"
                            viewBox="0 0 10 10"
                            fill="none"
                        >
                            <path
                                fillRule="evenodd"
                                clipRule="evenodd"
                                d="M0.260418 0.260418C0.607642 -0.086806 1.17015 -0.086806 1.51731 0.260418L5 3.7431L8.48269 0.260418C8.82991 -0.086806 9.39241 -0.086806 9.73958 0.260418C10.0868 0.607642 10.0868 1.17015 9.73958 1.51731L6.2569 5L9.73958 8.48269C10.0868 8.82991 10.0868 9.39241 9.73958 9.73958C9.39236 10.0868 8.82985 10.0868 8.48269 9.73958L5 6.2569L1.51731 9.73958C1.17009 10.0868 0.607587 10.0868 0.260418 9.73958C-0.0867505 9.39236 -0.086806 8.82985 0.260418 8.48269L3.7431 5L0.260418 1.51731C-0.086806 1.17009 -0.086806 0.607587 0.260418 0.260418Z"
                                fill="#7F7F98"
                            />
                        </svg>
                    </button>
                </div>

                <div className="flex flex-col justify-center items-center mb-6">
                    <h3 className="font-inter font-medium text-xl text-ec-title mb-2">
                        {__('Are you sure you want to replace?', 'easycommerce')}
                    </h3>
                    <p className="w-9/12 mx-auto text-center font-inter font-normal text-base text-ec-body">
                        {__('Auto generating variants will replace all existing variants for', 'easycommerce')} <strong>{itemName}</strong>.
                    </p>
                </div>

                <div className="flex justify-between items-center gap-[14px]">
                    <button
                        className="w-[181px] h-[45px] font-inter font-normal text-base border bg-white text-ec-title border-ec-title rounded-lg px-10 py-[10px]"
                        onClick={onClose}
                    >
                        {__('No, Keep', 'easycommerce')}
                    </button>
                    <button
                        className="w-[181px] h-[45px] font-inter font-normal text-base rounded-lg px-10 py-[10px] border bg-ec-red border-ec-red hover:bg-[#FF3A52CC] hover:border-[#FF3A52CC] text-white transition"
                        onClick={onConfirm}
                        type="button"
                    >
                        {__('Yes, Replace', 'easycommerce')}
                    </button>
                </div>
            </div>
        </div>
    );
};

export default Popup;
