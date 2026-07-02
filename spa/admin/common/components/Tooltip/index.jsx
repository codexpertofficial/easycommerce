import React from "react";
import { Tooltip as Tltp } from "react-tooltip";

import "./style.css";

const Tooltip = ({ text = null }) => {
    if (!text) return null;

    return (
        <div className="ec-tooltip-container h-[18px]">
            <button type="button" data-tooltip-id="ec-tooltip" data-tooltip-content={text}>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 18 18" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M1.58824 9C1.58824 13.0937 4.90632 16.4118 9 16.4118C13.0937 16.4118 16.4118 13.0937 16.4118 9C16.4118 4.90632 13.0937 1.58824 9 1.58824C4.90632 1.58824 1.58824 4.90632 1.58824 9ZM9 0C4.02962 0 0 4.02962 0 9C0 13.9704 4.02962 18 9 18C13.9704 18 18 13.9704 18 9C18 4.02962 13.9704 0 9 0ZM8.20588 7.94118C8.20588 7.50277 8.56159 7.14706 9 7.14706C9.43841 7.14706 9.79412 7.50277 9.79412 7.94118V12.7059C9.79412 13.1443 9.43841 13.5 9 13.5C8.56159 13.5 8.20588 13.1443 8.20588 12.7059V7.94118ZM9 4.5C8.56159 4.5 8.20588 4.85571 8.20588 5.29412C8.20588 5.73252 8.56159 6.08824 9 6.08824C9.43841 6.08824 9.79412 5.73252 9.79412 5.29412C9.79412 4.85571 9.43841 4.5 9 4.5Z" fill="#3C3C42"/>
                </svg>
            </button>

            <Tltp id="ec-tooltip" className="ec-tooltip">
                <p>{text}</p>
            </Tltp>
        </div>
    );
};

export default Tooltip;
