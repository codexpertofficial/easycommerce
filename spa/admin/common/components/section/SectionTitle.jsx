import React from "react";
import Tooltip from "../Tooltip";

const SectionTitle = ({ title, tooltipText = null }) => {
    return (
        <div
            className="w-full px-5 py-3 mb-6 border-b border-[#DBDBDB] 
            flex items-center justify-start gap-2"
        >
            <h4 className="text-[#5a5a5a] font-inter text-base leading-8">
                {title}
            </h4>

            <Tooltip text={tooltipText} />
        </div>
    );
};

export default SectionTitle;
