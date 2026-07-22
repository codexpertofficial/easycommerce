import React, { useState } from 'react';
import { __, sprintf } from '@wordpress/i18n';


const DropdownField = ({
    options,
    placeholder,
    width = "",
    menuWidth = "",
    onChange,
}) => {
    const [isOpen, setIsOpen] = useState(false);
    const [selected, setSelected] = useState(placeholder);


    const arrowDown = `${EASYCOMMERCE.assets}admin/img/icons/arrowDown.png`;

    const handleOptionClick = (option) => {
        setSelected(option.label);
        setIsOpen(false);
        if (onChange) {
            onChange(option.value);
        }
    };

    return (
        <div className="relative flex items-center justify-center">
            <button
                type="button"
                onClick={() => setIsOpen(!isOpen)}
                onBlur={() => setTimeout(() => setIsOpen(false), 200)}
                className="easycommerce-dropdown-field h-[40px] rounded-[4px] text-sm font-inter text-ec-placeholder bg-white border 
                border-[#EBEBEB] text-left pl-4 pr-10 py-2 focus:text-ec-body focus:border-ec-primary hover:border-ec-secondary 
                transition-all ease-in-out duration-500"
                style={{
                    width,
                    boxShadow: isOpen
                        ? "0px 2px 4.5px 0px var(--color-ec-tertiary)"
                        : "",
                }}
            >
                {selected}
            </button>
            {isOpen && (
                <ul
                    className="absolute top-16 p-3 right-[-18px] border bg-white border-ec-border rounded-[12px] shadow-2xl z-[99]"
                    style={{ width: menuWidth || width }}
                >
                    {options.map((option) => (
                        <li
                            key={option.value}
                            className="px-4 py-2 text-sm text-ec-body font-normal leading-[26px] hover:bg-ec-modal cursor-pointer rounded-[4px]"
                            onMouseDown={() => handleOptionClick(option)}
                        >
                            {option.label}
                        </li>
                    ))}
                </ul>
            )}
            <img
                src={arrowDown}
                alt={__('Dropdown Icon', 'easycommerce')}
                className={`easycommerce-select-icon absolute w-3 ml-0 right-3 transition-transform duration-300 ${
                    isOpen ? 'rotate-180' : ''
                }`}
            />
        </div>
    );
};

const ShortBy = ({ viewType, setViewType, perPage = 9, totalProducts = 0, showPagination = true }) => {
    const [sortOption, setSortOption] = useState('');

    const sortOptions = [
        { value: 'low-to-high', label: __('Low to High', 'easycommerce') },
        { value: 'high-to-low', label: __('High to Low', 'easycommerce') },
        { value: 'newest', label: __('Newest', 'easycommerce') },
        { value: 'oldest', label: __('Oldest', 'easycommerce') },
        { value: 'best-selling', label: __('Best Selling', 'easycommerce') },
        { value: 'lowest-selling', label: __('Lowest Selling', 'easycommerce') },
        { value: 'top-rating', label: __('Top rating', 'easycommerce') },
        { value: 'lowest-rating', label: __('Lowest rating', 'easycommerce') },
    ];

    const handleSortChange = (value) => {
        setSortOption(value);
    };

    const start = 1;
    const end = Math.min(perPage, totalProducts);
    const showingText = sprintf( __( 'Showing %1$d-%2$d of %3$d Results', 'easycommerce' ), start, end, totalProducts );

    const gridView = (
        <svg width="43" height="40" viewBox="0 0 43 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="0.5" y="0.5" width="42" height="39" rx="3.5" fill={viewType === 'grid' ? '#272435' : '#FFFFFF'}/>
            <rect x="0.5" y="0.5" width="42" height="39" rx="3.5" stroke={viewType === 'grid' ? '#272435' : '#EBEBEB'}/>
            <path d="M12.25 13.625C12.276 13.1042 12.4583 12.6615 12.7969 12.2969C13.1615 11.9583 13.6042 11.776 14.125 11.75H17.875C18.3958 11.776 18.8385 11.9583 19.2031 12.2969C19.5417 12.6615 19.724 13.1042 19.75 13.625V17.375C19.724 17.8958 19.5417 18.3385 19.2031 18.7031C18.8385 19.0417 18.3958 19.224 17.875 19.25H14.125C13.6042 19.224 13.1615 19.0417 12.7969 18.7031C12.4583 18.3385 12.276 17.8958 12.25 17.375V13.625ZM14.125 17.375H17.875V13.625H14.125V17.375ZM12.25 23.625C12.276 23.1042 12.4583 22.6615 12.7969 22.2969C13.1615 21.9583 13.6042 21.776 14.125 21.75H17.875C18.3958 21.776 18.8385 21.9583 19.2031 22.2969C19.5417 22.6615 19.724 23.1042 19.75 23.625V27.375C19.724 27.8958 19.5417 28.3385 19.2031 28.7031C18.8385 29.0417 18.3958 29.224 17.875 29.25H14.125C13.6042 29.224 13.1615 29.0417 12.7969 28.7031C12.4583 28.3385 12.276 27.8958 12.25 27.375V23.625ZM14.125 27.375H17.875V23.625H14.125V27.375ZM27.875 11.75C28.3958 11.776 28.8385 11.9583 29.2031 12.2969C29.5417 12.6615 29.724 13.1042 29.75 13.625V17.375C29.724 17.8958 29.5417 18.3385 29.2031 18.7031C28.8385 19.0417 28.3958 19.224 27.875 19.25H24.125C23.6042 19.224 23.1615 19.0417 22.7969 18.7031C22.4583 18.3385 22.276 17.8958 22.25 17.375V13.625C22.276 13.1042 22.4583 12.6615 22.7969 12.2969C23.1615 11.9583 23.6042 11.776 24.125 11.75H27.875ZM27.875 13.625H24.125V17.375H27.875V13.625ZM22.25 23.625C22.276 23.1042 22.4583 22.6615 22.7969 22.2969C23.1615 21.9583 23.6042 21.776 24.125 21.75H27.875C28.3958 21.776 28.8385 21.9583 29.2031 22.2969C29.5417 22.6615 29.724 23.1042 29.75 23.625V27.375C29.724 27.8958 29.5417 28.3385 29.2031 28.7031C28.8385 29.0417 28.3958 29.224 27.875 29.25H24.125C23.6042 29.224 23.1615 29.0417 22.7969 28.7031C22.4583 28.3385 22.276 27.8958 22.25 27.375V23.625ZM24.125 27.375H27.875V23.625H24.125V27.375Z" fill={viewType === 'grid' ? '#FFFFFF' : '#272435'}/>
        </svg>
    );

    const listView = (
        <svg width="43" height="40" viewBox="0 0 43 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="0.5" y="0.5" width="42" height="39" rx="3.5" fill={viewType === 'list' ? '#272435' : '#FFFFFF'}/>
            <rect x="0.5" y="0.5" width="42" height="39" rx="3.5" stroke={viewType === 'list' ? '#272435' : '#EBEBEB'}/>
            <path d="M13.5 13C13.8646 13 14.1641 13.1172 14.3984 13.3516C14.6328 13.5859 14.75 13.8854 14.75 14.25C14.75 14.6146 14.6328 14.9141 14.3984 15.1484C14.1641 15.3828 13.8646 15.5 13.5 15.5C13.1354 15.5 12.8359 15.3828 12.6016 15.1484C12.3672 14.9141 12.25 14.6146 12.25 14.25C12.25 13.8854 12.3672 13.5859 12.6016 13.3516C12.8359 13.1172 13.1354 13 13.5 13ZM30.0625 13.3125C30.6354 13.3646 30.9479 13.6771 31 14.25C30.9479 14.8229 30.6354 15.1354 30.0625 15.1875H18.1875C17.6146 15.1354 17.3021 14.8229 17.25 14.25C17.3021 13.6771 17.6146 13.3646 18.1875 13.3125H30.0625ZM30.0625 19.5625C30.6354 19.6146 30.9479 19.9271 31 20.5C30.9479 21.0729 30.6354 21.3854 30.0625 21.4375H18.1875C17.6146 21.3854 17.3021 21.0729 17.25 20.5C17.3021 19.9271 17.6146 19.6146 18.1875 19.5625H30.0625ZM30.0625 25.8125C30.6354 25.8646 30.9479 26.1771 31 26.75C30.9479 27.3229 30.6354 27.6354 30.0625 27.6875H18.1875C17.6146 27.6354 17.3021 27.3229 17.25 26.75C17.3021 26.1771 17.6146 25.8646 18.1875 25.8125H30.0625ZM13.5 21.75C13.1354 21.75 12.8359 21.6328 12.6016 21.3984C12.3672 21.1641 12.25 20.8646 12.25 20.5C12.25 20.1354 12.3672 19.8359 12.6016 19.6016C12.8359 19.3672 13.1354 19.25 13.5 19.25C13.8646 19.25 14.1641 19.3672 14.3984 19.6016C14.6328 19.8359 14.75 20.1354 14.75 20.5C14.75 20.8646 14.6328 21.1641 14.3984 21.3984C14.1641 21.6328 13.8646 21.75 13.5 21.75ZM13.5 25.5C13.8646 25.5 14.1641 25.6172 14.3984 25.8516C14.6328 26.0859 14.75 26.3854 14.75 26.75C14.75 27.1146 14.6328 27.4141 14.3984 27.6484C14.1641 27.8828 13.8646 28 13.5 28C13.1354 28 12.8359 27.8828 12.6016 27.6484C12.3672 27.4141 12.25 27.1146 12.25 26.75C12.25 26.3854 12.3672 26.0859 12.6016 25.8516C12.8359 25.6172 13.1354 25.5 13.5 25.5Z" fill={viewType === 'list' ? '#FFFFFF' : '#272435'}/>
        </svg>
    );
    
    return (
        <div className="flex items-center justify-between pt-6">
            <div>
                {showPagination && (
                    <h2 className="text-[#272435] font-inter text-base font-medium">
                        {showingText}
                    </h2>
                )}
            </div>

            <div className="flex items-center gap-4">
                <span className="text-[#737791] font-inter text-base font-normal">
                    {__('Sort by', 'easycommerce')}
                </span>
                <DropdownField
                    options={sortOptions}
                    placeholder={__('Default Sorter', 'easycommerce')}
                    width="200px"
                    menuWidth="200px"
                    onChange={handleSortChange}
                />
                <div className="flex items-center gap-2">
                    <button
                        type="button"
                        onClick={() => setViewType('grid')}
                        className=""
                    >
                        {gridView}
                    </button>
                    <button
                        type="button"
                        onClick={() => setViewType('list')}
                        className=""
                    >
                        {listView}
                    </button>
                </div>
            </div>
        </div>
    );
};

export default ShortBy;
