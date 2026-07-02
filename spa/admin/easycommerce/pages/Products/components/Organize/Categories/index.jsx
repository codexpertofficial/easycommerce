import React, { useState, useEffect } from 'react';

const Categories = ({ active, categories, selected, setSelected }) => {

    // Validate categories prop
    const safeCategories = Array.isArray(categories) ? categories : [];

    const renderCategories = (categoriesToRender) => {
        // Validate input - return empty array if invalid
        if (!Array.isArray(categoriesToRender) || categoriesToRender.length === 0) {
            return null;
        }

        // Filter out invalid categories
        const validCategories = categoriesToRender.filter(
            (category) => category && typeof category === 'object' && category.id && category.name
        );

        if (validCategories.length === 0) {
            return null;
        }

        return validCategories.map((category) => {
            // Safely check if category has children
            const hasChildren = Array.isArray(category.children) && category.children.length > 0;

            return (
                <div key={category.id} className="flex flex-col gap-2 ml-0">
                    <div className="flex justify-between items-center">
                        <div>
                            <input
                                type="checkbox"
                                className="easycommerce-input-checkoutbox"
                                id={category.id}
                                checked={selected.includes(category.id)}
                                onChange={() => {
                                    setSelected((prev) => {
                                        // Ensure prev is always an array
                                        const safePrev = Array.isArray(prev) ? prev : [];

                                        if (safePrev.includes(category.id)) {
                                            return safePrev.filter(item => item !== category.id);
                                        } else {
                                            return [...safePrev, category.id];
                                        }
                                    });
                                }}
                            />
                            <label
                                htmlFor={category.id}
                                className="text-base font-inter ml-2 text-ec-title rtl:mr-2"
                            >
                                {category.name}
                            </label>
                        </div>
                    </div>
                    {hasChildren && (
                        <div className="ml-6 flex flex-col gap-3">
                            {renderCategories(category.children)}
                        </div>
                    )}
                </div>
            );
        });
    };

    // Log warning if categories prop is invalid
    useEffect(() => {
        if (!Array.isArray(categories)) {
            console.warn('Categories component: categories prop is not an array', categories);
        }
    }, [categories]);

    return (
        <div className={active ? "" : "hidden"}>
            <div className="easycommerce-term-container flex flex-col gap-3 mt-9">
                {safeCategories.length > 0 ? (
                    renderCategories(safeCategories)
                ) : (
                    <div className="text-gray-500 text-sm">No categories available</div>
                )}
            </div>
            <input
                type="hidden"
                name="product_categories"
                value={Array.isArray(selected) ? selected.join(',') : ''}
            />
        </div>
    );
};

export default Categories;