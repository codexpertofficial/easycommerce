import React from "react";
import { __ } from "@wordpress/i18n";

const CleanAbandonedCartModal = ({
  isVisible,
  closePopup,
  cleanInvalidAbandonedCarts,
  cleanAbandonedCarts,
  abandonedBg,
}) => {
  return (
    isVisible && (
      <div className="fixed top-0 left-0 w-screen h-screen flex items-center justify-center bg-[#00000082] backdrop-blur-sm z-[999999] p-4">
        <div className="relative w-full max-w-[468px] bg-white rounded-[22px] pb-[20px] sm:pb-[40px]">
          <div className="relative">
            <img
              className="rounded-t-[22px] w-full"
              src={abandonedBg}
              alt={__("Abandoned Cart", "easycommerce")}
            />
            <button
              onClick={closePopup}
              className="group absolute w-[24px] h-[24px] top-[-12px] right-[-12px] sm:top-[-20px] sm:right-[-15px] bg-white rounded-full hover:bg-ec-red flex items-center justify-center transition-colors duration-200"
            >
              <svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M0.260418 1.10798C0.607642 0.768042 1.17015 0.768042 1.51731 1.10798L5 4.5176L8.48269 1.10798C8.82991 0.768042 9.39241 0.768042 9.73958 1.10798C10.0868 1.44792 10.0868 1.99862 9.73958 2.33851L6.2569 5.74813L9.73958 9.15775C10.0868 9.49769 10.0868 10.0484 9.73958 10.3883C9.39236 10.7282 8.82985 10.7282 8.48269 10.3883L5 6.97866L1.51731 10.3883C1.17009 10.7282 0.607587 10.7282 0.260418 10.3883C-0.0867505 10.0483 -0.086806 9.49764 0.260418 9.15775L3.7431 5.74813L0.260418 2.33851C-0.086806 1.99857 -0.086806 1.44787 0.260418 1.10798Z" fill="#3C3C42" className="transition-colors duration-200 group-hover:fill-white"/>
              </svg>
            </button>
          </div>

          <div className="flex flex-col justify-center items-center mt-4 mb-4 sm:mb-6 px-4">
            <h3 className="font-inter font-medium text-lg sm:text-xl text-ec-title mb-2 text-center">
              {__("Clean Abandoned Carts", "easycommerce")}
            </h3>
            <p className="w-full sm:w-9/12 mx-auto text-center font-inter font-normal text-sm sm:text-base text-ec-body">
              {__("Choose whether to clean all abandoned carts or only invalid ones.", "easycommerce")}
            </p>
          </div>

          <div className="flex flex-col sm:flex-row justify-center items-center gap-3 sm:gap-[14px] px-4">
            <button
              className="w-full sm:w-[181px] h-[45px] font-inter font-normal text-sm sm:text-base rounded-lg px-4 sm:px-10 py-[10px] border bg-ec-red border-ec-red hover:bg-[#FF3A52CC] hover:border-[#FF3A52CC] text-white transition"
              onClick={cleanAbandonedCarts}
              type="button"
            >
              {__("Clean All", "easycommerce")}
            </button>
            <button
              className="w-full sm:w-[181px] h-[45px] font-inter font-normal text-sm sm:text-base border bg-white text-ec-title border-ec-title rounded-lg py-[10px]"
              onClick={cleanInvalidAbandonedCarts}
            >
              {__("Clean Invalid", "easycommerce")}
            </button>
          </div>
        </div>
      </div>
    )
  );
};

export default CleanAbandonedCartModal;