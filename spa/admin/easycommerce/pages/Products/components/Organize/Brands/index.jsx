import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';

const Brands = ({ active, brands, selected, setSelected }) => {

    // Validate brands prop
    const safeBrands = Array.isArray(brands) ? brands : [];

    const renderBrands = (brandsToRender) => {
        // Validate input - return null if invalid
        if (!Array.isArray(brandsToRender) || brandsToRender.length === 0) {
            return null;
        }

        // Filter out invalid brands
        const validBrands = brandsToRender.filter(
            (brand) => brand && typeof brand === 'object' && brand.id && brand.name
        );

        if (validBrands.length === 0) {
            return null;
        }

        return validBrands.map((brand) => {
            // Safely check if brand has children
            const hasChildren = Array.isArray(brand.children) && brand.children.length > 0;

            return (
                <div key={brand.id} className="flex flex-col gap-2 ml-0">
                    <div className="flex justify-between items-center">
                        <div>
                            <input
                                type="checkbox"
                                className="easycommerce-input-checkoutbox"
                                id={brand.id}
                                checked={selected.includes(brand.id)}
                                onChange={() => {
                                    setSelected((prev) => {
                                        // Ensure prev is always an array
                                        const safePrev = Array.isArray(prev) ? prev : [];
                                        
                                        if (safePrev.includes(brand.id)) {
                                            return safePrev.filter(item => item !== brand.id);
                                        } else {
                                            return [...safePrev, brand.id];
                                        }
                                    });
                                }}
                            />
                            <label
                                htmlFor={brand.id}
                                className="text-base font-inter ml-2 text-ec-title rtl:mr-2"
                            >
                                {brand.name}
                            </label>
                        </div>
                    </div>
                    {hasChildren && (
                        <div className="ml-6 flex flex-col gap-6">
                            {renderBrands(brand.children)}
                        </div>
                    )}
                </div>
            );
        });
    };

    // Log warning if brands prop is invalid
    useEffect(() => {
        if (!Array.isArray(brands)) {
            console.warn('Brands component: brands prop is not an array', brands);
        }
    }, [brands]);

    return (
        <div className={active ? "" : "hidden"}>
            <div className="easycommerce-term-container flex flex-col gap-3 mt-9">
                {safeBrands.length > 0 ? (
                    renderBrands(safeBrands)
                ) : (
                    <div className="text-gray-500 text-sm">{__('No brands available', 'easycommerce')}</div>
                )}
            </div>
            <input
                type="hidden"
                name="product_brands"
                value={Array.isArray(selected) ? selected.join(',') : ''}
            />
        </div>
    );
};

export default Brands;