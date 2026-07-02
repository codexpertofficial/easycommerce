import React from "react";

const bulk = `${EASYCOMMERCE.assets}admin/img/bulk.png`;

const BulkPopup = ({
    onClose,    
    onConfirm,
    alertImage = bulk,
}) => {
    return (
        <div className="fixed top-0 left-0 w-screen h-screen flex items-center justify-center bg-[#00000082] backdrop-blur-sm z-[999999]">
            <div className="relative w-[451px] flex flex-col justify-center items-center bg-white rounded-[22px] pb-[40px]">
                <div
                    className="w-[451px] pt-[30px] pb-[30px] flex flex-col justify-center items-center rounded-t-[22px] 
                    bg-cover bg-center bg-no-repeat"
                >
                    <div
                        className="w-[120px] h-[120px] bg-white flex justify-center items-center rounded-xl 
                        shadow-[0px_18px_22.2px_0px_#DBD3FF"
                    >
                        <img src={alertImage} alt="delete-attribute" className="w-[80px] h-[80px]" />
                    </div>
                    <button 
                        onClick={onClose} 
                        className="group absolute w-[24px] h-[24px] top-[-15px] left-[446px] bg-white rounded-full
                         hover:bg-ec-primary flex items-center justify-center transition-colors duration-200"
                    >
                        <svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.260418 1.10798C0.607642 0.768042 1.17015 0.768042 1.51731 1.10798L5 4.5176L8.48269 1.10798C8.82991 0.768042 9.39241 0.768042 9.73958 1.10798C10.0868 1.44792 10.0868 1.99862 9.73958 2.33851L6.2569 5.74813L9.73958 9.15775C10.0868 9.49769 10.0868 10.0484 9.73958 10.3883C9.39236 10.7282 8.82985 10.7282 8.48269 10.3883L5 6.97866L1.51731 10.3883C1.17009 10.7282 0.607587 10.7282 0.260418 10.3883C-0.0867505 10.0483 -0.086806 9.49764 0.260418 9.15775L3.7431 5.74813L0.260418 2.33851C-0.086806 1.99857 -0.086806 1.44787 0.260418 1.10798Z" fill="#3C3C42" className="transition-colors duration-200 group-hover:fill-white"/>
                        </svg>
                        
                    </button>
                </div>

                <div className="flex flex-col justify-center items-center mb-6">
                    <h3 className="font-inter font-medium text-xl text-ec-title mb-2">
                        Are you sure you want to update?
                    </h3>
                    <p className="w-9/12 mx-auto text-center font-inter font-normal text-base text-ec-body">
                        You're going to update <span className="font-semibold">All</span> are you sure?
                    </p>
                </div>

                <div className="flex justify-between items-center gap-[14px]">
                    <button
                        className="w-[181px] h-[45px] font-inter font-normal text-base border bg-white text-ec-title
                         border-ec-title rounded-lg py-[10px]"
                        onClick={onClose}
                    >
                        No, Keep as it is
                    </button>
                    <button
                        className="w-[181px] h-[45px] font-inter font-normal text-base rounded-lg px-10 py-[10px] border
                         bg-ec-primary border-ec-primary hover:bg-primary hover:border-primary text-white transition"
                        onClick={onConfirm}
                        type="button"
                    >
                        Yes, Update
                    </button>
                </div>
            </div>
        </div>
    );
};

export default BulkPopup;
