import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';

const Tags = ({ active, tags, selected, setSelected }) => {

    // Validate tags prop
    const safeTags = Array.isArray(tags) ? tags : [];

    // Filter out invalid tags
    const validTags = safeTags.filter(
        (tag) => tag && typeof tag === 'object' && tag.id && tag.name
    );

    // Log warning if tags prop is invalid
    useEffect(() => {
        if (!Array.isArray(tags)) {
            console.warn('Tags component: tags prop is not an array', tags);
        }
    }, [tags]);

    return (
        <div className={active ? "" : "hidden"}>
            <div className="easycommerce-term-container flex flex-col gap-3 mt-9">
                {validTags.length > 0 ? (
                    validTags.map((tag) => (
                        <div key={tag.id} className="flex flex-col gap-2 ml-0">
                            <div className="flex justify-between items-center">
                                <div>
                                    <input
                                        type="checkbox"
                                        className="easycommerce-input-checkoutbox"
                                        id={tag.id}
                                        checked={selected.includes(tag.id)}
                                        onChange={() => {
                                            setSelected((prev) => {
                                                // Ensure prev is always an array
                                                const safePrev = Array.isArray(prev) ? prev : [];
                                                
                                                if (safePrev.includes(tag.id)) {
                                                    return safePrev.filter(item => item !== tag.id);
                                                } else {
                                                    return [...safePrev, tag.id];
                                                }
                                            });
                                        }}
                                    />
                                    <label
                                        htmlFor={tag.id}
                                        className="text-base font-inter ml-2 text-ec-title rtl:mr-2"
                                    >
                                        {tag.name}
                                    </label>
                                </div>
                            </div>
                        </div>
                    ))
                ) : (
                    <div className="text-gray-500 text-sm">{__('No tags available', 'easycommerce')}</div>
                )}
            </div>
            <input
                type="hidden"
                name="product_tags"
                value={Array.isArray(selected) ? selected.join(',') : ''}
            />
        </div>
    );
};

export default Tags;