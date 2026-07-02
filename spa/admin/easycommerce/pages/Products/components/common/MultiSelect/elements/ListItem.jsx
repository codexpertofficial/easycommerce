import React from "react";

const ListItem = ({ text, handleDelete }) => {
    return (
        <li className="py-[6px] px-3 m-0 border border-ec-light-black bg-white flex items-center gap-2 rounded-md duration-300 group hover:bg-ec-red hover:border-ec-red">
            <span className="text-[14px] font-inter leading-5 text-ec-body group-hover:text-white">{text}</span>

            <button type='button' onClick={handleDelete}>
                <svg
                    width="9"
                    height="9"
                    viewBox="0 0 9 9"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path className="fill-ec-light-black group-hover:fill-white"
                        d="M8.3125 7.28906C8.53125 7.55469 8.53125 7.82031 8.3125 8.08594C8.04688 8.30469 7.78125 
                        8.30469 7.51562 8.08594L4.75 5.29688L1.96094 8.08594C1.69531 8.30469 1.42969 8.30469 1.16406 
                        8.08594C0.945312 7.82031 0.945312 7.55469 1.16406 7.28906L3.95312 4.5L1.16406 1.6875C0.945312 
                        1.42188 0.945312 1.15625 1.16406 0.890625C1.42969 0.671875 1.69531 0.671875 1.96094 0.890625L4.75 
                        3.70312L7.53906 0.914062C7.80469 0.695313 8.07031 0.695313 8.33594 0.914062C8.55469 1.17969 8.55469 
                        1.44531 8.33594 1.71094L5.54688 4.5L8.3125 7.28906Z"
                    ></path>
                </svg>
            </button>
        </li>
    );
};

export default ListItem;
