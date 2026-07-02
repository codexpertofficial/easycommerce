import { useEffect, useState } from 'react';
import { Slot } from '@wordpress/components';
import { applyFilters } from '@wordpress/hooks';
import RecursiveOptions from './RecursiveOptions';
import DualRangeSlider from './DualRangeSlider';

const Filters = () => {
    const [isAccordionOpen, setIsAccordionOpen] = useState({});
    const [categories, setCategories] = useState([]);
    const [attributes, setAttributes] = useState([]);
    const [brands, setBrands] = useState([]);
    const [priceRange, setPriceRange] = useState({ min: 0, max: 10000 });
    const [sortOption, setSortOption] = useState('');

    const arrow = EASYCOMMERCE.assets + 'common/img/blocks/shop-page/arrow.png';

    /**
     * Filters the sort options for shop filters.
     *
     * @since 1.0.0
     * @param {Object} options The sort options object.
     */
    const sortOptions = applyFilters('easycommerce.blocks.shop.filters.sort.options', {
        'low-to-high': 'Low to High',
        'high-to-low': 'High to Low',
        'newest': 'Newest',
        'oldest': 'Oldest',
        'best-selling': 'Best Selling',
        'lowest-selling': 'Lowest Selling',
        'top-rating': 'Top rating',
        'lowest-rating': 'Lowest rating',
    });

    useEffect(() => {
        const fetchData = async () => {
            try {
                const categoryResponse = await fetch(
                    EASYCOMMERCE.rest_base + '/products/categories'
                );
                const attributesResponse = await fetch(
                    EASYCOMMERCE.rest_base + '/attributes'
                );
                const brandResponse = await fetch(
                    EASYCOMMERCE.rest_base + '/products/brands'
                );

                const attributesData = await attributesResponse.json();
                const categoriesData = await categoryResponse.json();
                const brandsData = await brandResponse.json();
                setCategories(categoriesData.data.categories);
                setAttributes(attributesData.data);
                setBrands(brandsData.data.brands);
            } catch (error) {
                // Handle error
            }
        };

        fetchData();
    }, []);

    const toggleAccordion = (categoryName) => {
        setIsAccordionOpen((prevState) => ({
            ...prevState,
            [categoryName]: !prevState[categoryName],
        }));
    };

    const handleSortChange = (event) => {
        setSortOption(event.target.value);
    };

    return (
        <div className="w-full mb-10 easycommerce-shop-edit-page flex">
            <div className="w-[70%] flex items-center gap-4">
                 {/* Categories filter */}
                {categories.length > 0 && (
                    <div className="py-2 px-4 border border-ec-border rounded-full">
                        <h5
                            className="easycommerce-filter-heading text-base font-normal cursor-pointer colorec-body w-full flex justify-between items-center"
                            onClick={() => toggleAccordion('categories')}
                        >
                            Categories
                            <span
                                className={`transform transition-transform ml-2 ${
                                    isAccordionOpen.categories ? 'rotate-180' : ''
                                }`}
                            >
                                <svg className="w-4 h-4" data-slot="icon" fill="none" strokeWidth="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path strokeLinecap="round" strokeLinejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
								</svg>
                            </span>
                        </h5>
                        {isAccordionOpen.categories && (
                            <div className="relative">
                                <div className="pt-6 flex flex-col gap-5 easycommerce-categories-option">
                                    {categories &&
                                        categories.map((category) => (
                                        <RecursiveOptions
                                            key={category.id}
                                            data={category}
                                            labelKey="name"
                                            countKey="count"
                                            childrenKey="children"
                                        />
                                    ))}
                                </div>
                            </div>

                        )}
                    </div>
                )}

                {/* Brands Accordion */}
                {brands.length > 0 && (
                    <div className="py-2 px-4  border border-ec-border rounded-full">
                        <h5
                            className="easycommerce-filter-heading text-base font-normal cursor-pointer colorec-body w-full flex justify-between items-center"
                            onClick={() => toggleAccordion('brands')}
                        >
                            Brands
                            <span
                                className={`transform transition-transform ml-2 ${
                                    isAccordionOpen.brands ? 'rotate-180' : ''
                                }`}
                            >
                                <svg className="w-4 h-4" data-slot="icon" fill="none" strokeWidth="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path strokeLinecap="round" strokeLinejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
								</svg>
                            </span>
                        </h5>
                        {isAccordionOpen.brands && (
                            <div className="relative">
                                <div className="pt-6 flex flex-col gap-5 easycommerce-brands-option">
                                    {brands &&
                                        brands.map((brands) => (
                                            <RecursiveOptions
                                                key={brands.id}
                                                data={brands}
                                                labelKey="name"
                                                countKey="count"
                                                childrenKey="children"
                                            />
                                        ))}
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {/* Price Range Accordion */}
                <div className="py-2 px-4  border border-ec-border rounded-full">
                    <h5
                        className="easycommerce-filter-heading text-base font-normal cursor-pointer colorec-body w-full flex justify-between items-center"
                        onClick={() => toggleAccordion('price')}
                    >
                        Price Range
                        <span
                            className={`transform transition-transform ml-2 ${
                                isAccordionOpen.price ? 'rotate-180' : ''
                            }`}
                        >
                            <svg className="w-4 h-4" data-slot="icon" fill="none" strokeWidth="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path strokeLinecap="round" strokeLinejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
							</svg>
                        </span>
                    </h5>
                    {isAccordionOpen.price && (
                        <div className="relative">
                            <div className="pt-3 easycommerce-prices-option">
                                <DualRangeSlider
                                    range={priceRange}
                                    setRange={setPriceRange}
                                    maxValue={10000}
                                />
                            </div>
                        </div>
                    )}
                </div>

                {/* All attributes Accordion */}
                {attributes.attributes &&
                    attributes.attributes.map((attribute) => (
                        <div
                            key={attribute.id}
                            className="py-2 px-4  border border-ec-border rounded-full"
                        >
                            <h5
                                className="easycommerce-filter-heading text-base font-normal cursor-pointer colorec-body w-full flex justify-between items-center"
                                onClick={() => toggleAccordion(attribute.name)}
                            >
                                {attribute.name}
                                <span
                                    className={`transform transition-transform ml-2 ${
                                        isAccordionOpen[attribute.name]
                                            ? 'rotate-180'
                                            : ''
                                    }`}
                                >
                                    <svg className="w-4 h-4" data-slot="icon" fill="none" strokeWidth="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									    <path strokeLinecap="round" strokeLinejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
								    </svg>
                                </span>
                            </h5>

                            {isAccordionOpen[attribute.name] && (
                                <div className="relative">
                                    <div className="pt-6 flex flex-col gap-5 easycommerce-attributes-option">
                                        {Object.entries(attribute.values).map(
                                            ([valueKey, valueName]) => (
                                                <div
                                                    key={valueKey}
                                                    className="flex justify-between"
                                                >
                                                    <label className="flex items-center gap-2 ml-2 text-[#111827] text-sm font-medium leading-5">
                                                        <input
                                                            type="checkbox"
                                                            className="easycommerce-input-checkoutbox"
                                                            name={valueKey}
                                                        />
                                                        {valueName}
                                                    </label>
                                                </div>
                                            )
                                        )}
                                    </div>
                                </div>
                            )}
                        </div>
                ))}
            </div>


            <div className="w-[30%] flex items-center gap-4">
                <div className="w-[70%] easycommerce-drawer-product-search relative">
                    <img
                        src={`${EASYCOMMERCE.assets}common/img/blocks/shop-page/product-search.png`}
                        alt="Search Icon"
                        className="search-icon"
                    />
                    <input
                        type="text"
                        className="easycommerce-product-search search-text border border-ec-border pl-16 pr-2 py-1 w-full rounded-full hover:border-ec-secondary focus:border-ec-primary"
                        placeholder=" Search"
                    />
                </div>
                {/* Sorting Options */}
                <div className="w-[30%] py-2 px-4  border border-ec-border rounded-full">
                    <h5
                        className="easycommerce-filter-heading text-base font-normal cursor-pointer colorec-body w-full flex justify-between items-center"
                        onClick={() => toggleAccordion('sort')}
                    >
                        Sort by
                        <span
                            className={`transform transition-transform ${
                                isAccordionOpen.sort ? 'rotate-180' : ''
                            }`}
                        >
                            <svg className="w-4 h-4" data-slot="icon" fill="none" strokeWidth="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path strokeLinecap="round" strokeLinejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
							</svg>
                        </span>
                    </h5>
                    {isAccordionOpen.sort && (
                        <div className="relative">
                            <div className="pt-3 pb-8 flex flex-col gap-5 easycommerce-filter-option">
                                {Object.entries(sortOptions).map(([key, value]) => (
                                    <div key={key} className="flex justify-between">
                                        <label className="flex items-center gap-2">
                                            <input
                                                type="radio"
                                                className="easycommerce-shop-page-input-type-radio"
                                                name="sort"
                                                value={key}
                                                checked={sortOption === key}
                                                onChange={handleSortChange}
                                            />
                                            {value}
                                        </label>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Slot for additional filters */}
            <Slot name="easycommerce.blocks.shop.filters.extra" props={{ categories, attributes, brands, priceRange, sortOption }} />

        </div>
    );
};

export default Filters;
