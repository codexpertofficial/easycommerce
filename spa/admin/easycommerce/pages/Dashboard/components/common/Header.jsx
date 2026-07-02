import React, { useState, useEffect, useRef } from 'react';

const Header = ({ title, range, setRange }) => {
    const [activeDropdown, setActiveDropdown] = useState(null);
    const dropdownRef = useRef(null);
    const stickyDropdownRef = useRef(null);
    const [isScrolled, setIsScrolled] = useState(false);

    const rangeOptions = [
        { label: 'Last 7 days', value: 'last-7' },
        { label: 'Last 30 days', value: 'last-30' },
        { label: 'This week', value: 'this-week' },
        { label: 'This month', value: 'this-month' },
        { label: 'This year', value: 'this-year' },
    ];

    useEffect(() => {
        const handleClickOutside = (event) => {
            const inMain = dropdownRef.current?.contains(event.target);
            const inSticky = stickyDropdownRef.current?.contains(event.target);
            if (!inMain && !inSticky) {
                setActiveDropdown(null);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    useEffect(() => {
        const handleScroll = () => setIsScrolled(window.scrollY > 150);
        window.addEventListener('scroll', handleScroll, { passive: true });
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    const DropdownControls = () => (
        <div className="flex bg-white rounded-md text-[#272435] text-sm">
            <div className="relative">
                <button
                    className="py-2.5 px-5 flex items-center gap-5"
                    onClick={() => setActiveDropdown(activeDropdown === 'range' ? null : 'range')}
                >
                    {range.label}
                    <ChevronIcon />
                </button>
                {activeDropdown === 'range' && (
                    <DropdownMenu
                        options={rangeOptions}
                        selected={range.value}
                        onSelect={(option) => {
                            setRange({ label: option.label, value: option.value });
                            setActiveDropdown(null);
                        }}
                    />
                )}
            </div>
        </div>
    );

    return (
        <div className="flex items-center justify-between mb-6">
            <div className="product-panel-title">
                <h3>{title}</h3>
            </div>

            <div ref={dropdownRef}>
                <DropdownControls />
            </div>

            {isScrolled && (
                <div
                    className="fixed top-16 right-8 rounded-md border border-ec-primary z-[999]"
                    style={{ boxShadow: '0px 4px 4px 0px #00000040' }}
                    ref={stickyDropdownRef}
                >
                    <DropdownControls />
                </div>
            )}
        </div>
    );
};

const ChevronIcon = () => (
    <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M11 1.53125L6.25 6C6.08333 6.14583 5.90625 6.21875 5.71875 6.21875C5.53125 6.21875 5.36458 6.14583 5.21875 6L0.46875 1.53125C0.15625 1.17708 0.145833 0.822917 0.4375 0.46875C0.770833 0.15625 1.125 0.145833 1.5 0.4375L5.71875 4.4375L9.96875 0.4375C10.3229 0.145833 10.6667 0.145833 11 0.4375C11.2917 0.8125 11.2917 1.17708 11 1.53125Z"
            fill="#272435"
        />
    </svg>
);

const DropdownMenu = ({ options, selected, onSelect }) => (
    <div className="absolute z-[999] right-0 bg-white top-[calc(100%_+_5px)] w-[160px] flex flex-col border border-[#F0EDFB] shadow-[0px_4px_6px_0px_#0000001A] rounded-lg overflow-hidden">
        {options.map((option, index) => (
            <button
                key={index}
                className={`text-left text-sm flex items-center gap-2 text-[#3C3C42] px-3 py-2 hover:bg-[#F0EDFB] duration-300 ${option.value === selected ? 'bg-[#f0f0f0]' : ''}`}
                onClick={() => onSelect(option)}
            >
                {option.label}
            </button>
        ))}
    </div>
);

export default Header;