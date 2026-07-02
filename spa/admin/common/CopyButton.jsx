import React, { useState } from "react";

const copyIcon = `${EASYCOMMERCE.assets}admin/img/icons/copy-icon.png`;
const copiedIcon = `${EASYCOMMERCE.assets}admin/img/icons/copied-icon.png`;
const copyTooltip = `${EASYCOMMERCE.assets}admin/img/icons/copy-tooltip-icon.png`;
const copiedTooltip = `${EASYCOMMERCE.assets}admin/img/icons/copied-tooltip-icon.png`;

const CopyButton = ({ copy }) => {
    const [isCopied, setIsCopied] = useState(false);

    const copyCoupon = (text) => {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            // Modern browsers with Clipboard API support
            navigator.clipboard
                .writeText(text)
                .then(() => {
                    setIsCopied(true);
                })
                .catch((err) => {
                    fallbackCopyToClipboard(text, setIsCopied); // Use fallback in case of failure
                });
        } else {
            // Fallback for older browsers
            fallbackCopyToClipboard(text, setIsCopied);
        }
    };

    const fallbackCopyToClipboard = (text, setIsCopied) => {
        try {
            // Create a temporary <textarea> element
            const textarea = document.createElement("textarea");
            textarea.value = text;
            textarea.style.position = "fixed"; // Prevent scrolling to the bottom
            textarea.style.left = "-9999px"; // Hide the element
            document.body.appendChild(textarea);

            // Select the text and copy it
            textarea.select();
            const successful = document.execCommand("copy");

            // Remove the temporary <textarea>
            document.body.removeChild(textarea);

            if (successful) {
                setIsCopied(true);
            } 
        } catch (err) {
        }
    };

    return (
        <div className="relative w-[75px] flex justify-center items-center group">
            <img
                src={isCopied ? copiedTooltip : copyTooltip}
                className={`${
                    isCopied ? "w-[61px]" : "w-[50px]"
                } h-[33px] absolute -top-[33px] z-10 hidden group-hover:block pointer-events-none`}
            />
            <button
                className="w-[32px] h-[32px] flex justify-center items-center bg-[#7351FD08] 
                cursor-pointer rounded-[6px] border border-[#7351FD08]"
                 onClick={() => {
                    copyCoupon(copy);
                    setIsCopied(true);
                }}

                onMouseLeave={() => {
                    setTimeout(() => setIsCopied(false), 200);
                }}
             
            >
                <div className="transition-all duration-300 ease-in-out">
                    <img
                        src={isCopied ? copiedIcon : copyIcon} 
                        className="w-[18px] h-[18px] pointer-events-none"
                    />
                </div> 
            </button>
        </div>
    );
};

export default CopyButton;
