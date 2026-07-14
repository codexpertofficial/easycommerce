import React from "react";

/**
 * Friendly empty-state block for the dashboard tables/lists.
 *
 * @param {string}      title   Headline (e.g. "No orders yet").
 * @param {string}      message Supporting line.
 * @param {JSX.Element} icon    Optional icon node; a default box icon is used otherwise.
 * @param {JSX.Element} action  Optional CTA node (button/link).
 */
const EmptyState = ({ title, message, icon, action }) => {
    return (
        <div className="flex flex-col items-center justify-center text-center gap-3 py-14 px-6">
            <div className="w-16 h-16 flex items-center justify-center rounded-full bg-ec-accent text-ec-primary">
                {icon || (
                    <svg
                        className="w-8 h-8"
                        data-slot="icon"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="1.5"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
                        />
                    </svg>
                )}
            </div>
            <h4 className="font-inter font-semibold text-lg text-ec-title m-0">
                {title}
            </h4>
            {message && (
                <p className="font-inter text-sm text-ec-placeholder max-w-sm m-0">
                    {message}
                </p>
            )}
            {action && <div className="mt-1">{action}</div>}
        </div>
    );
};

export default EmptyState;
