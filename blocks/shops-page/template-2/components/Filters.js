import { useEffect, useState } from 'react';
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

    const sortOptions = {
        'low-to-high': 'Low to High',
        'high-to-low': 'High to Low',
        'newest': 'Newest',
        'oldest': 'Oldest',
        'best-selling': 'Best Selling',
        'lowest-selling': 'Lowest Selling',
        'top-rating': 'Top rating',
        'lowest-rating': 'Lowest rating',
    };

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
        <div className="w-[300px] mb-10 easycommerce-shop-edit-page">
            <div className="easycommerce-drawer-product-search relative">
                <img
                    src={`${EASYCOMMERCE.assets}common/img/blocks/shop-page/product-search.png`}
                    alt="Search Icon"
                    className="absolute top-3 left-5"
                />
                <input
                    type="text"
                    className="easycommerce-product-search border border-ec-border pl-14 pr-2 py-1 w-full rounded-lg hover:border-ec-secondary focus:border-ec-primary"
                    placeholder=" Search"
                />
            </div>
            {/* Sorting Options */}
            <div className="py-6 px-2 border-b-[1px] border-ec-border">
                <h5
                    className="easycommerce-filter-heading text-[16px] font-semibold cursor-pointer colorec-body w-full flex justify-between items-center"
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
                    <div className="pt-3 pb-8 flex flex-col gap-5">
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
                )}
            </div>
            {/* Categories filter */}
            {categories.length > 0 && (
                <div className="py-6 px-2 border-y-[1px] border-ec-border">
                    <h5
                        className="easycommerce-filter-heading text-[16px] font-semibold cursor-pointer colorec-body w-full flex justify-between items-center"
                        onClick={() => toggleAccordion('categories')}
                    >
                        Categories
                        <span
                            className={`transform transition-transform ${
                                isAccordionOpen.categories ? 'rotate-180' : ''
                            }`}
                        >
                            <svg className="w-4 h-4" data-slot="icon" fill="none" strokeWidth="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path strokeLinecap="round" strokeLinejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
							</svg>
                        </span>
                    </h5>
                    {isAccordionOpen.categories && (
                        <div className="pt-6 flex flex-col gap-5">
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
                    )}
                </div>
            )}

            {/* Price Range Accordion */}
            <div className="py-6 px-2 border-b-[1px] border-ec-border">
                <h5
                    className="easycommerce-filter-heading text-[16px] font-semibold cursor-pointer colorec-body w-full flex justify-between items-center"
                    onClick={() => toggleAccordion('price')}
                >
                    Price Range
                    <span
                        className={`transform transition-transform ${
                            isAccordionOpen.price ? 'rotate-180' : ''
                        }`}
                    >
                        <svg className="w-4 h-4" data-slot="icon" fill="none" strokeWidth="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path strokeLinecap="round" strokeLinejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
						</svg>
                    </span>
                </h5>
                {isAccordionOpen.price && (
                    <div className="pt-3">
                        <DualRangeSlider
                            range={priceRange}
                            setRange={setPriceRange}
                            maxValue={10000}
                        />
                    </div>
                )}
            </div>

            {/* All attributes Accordion */}
            {attributes.attributes &&
                attributes.attributes.map((attribute) => (
                    <div
                        key={attribute.id}
                        className="py-6 px-2 border-b-[1px] border-ec-border"
                    >
                        <h5
                            className="easycommerce-filter-heading text-[16px] font-semibold cursor-pointer colorec-body w-full flex justify-between items-center"
                            onClick={() => toggleAccordion(attribute.name)}
                        >
                            {attribute.name}
                            <span
                                className={`transform transition-transform ${
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
                            <div className="pt-6 flex flex-col gap-5">
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
                        )}
                    </div>
                ))}

            {/* Brands Accordion */}
            {brands.length > 0 && (
                <div className="py-6 px-2 border-b-[1px] border-ec-border">
                    <h5
                        className="easycommerce-filter-heading text-[16px] font-semibold cursor-pointer colorec-body w-full flex justify-between items-center"
                        onClick={() => toggleAccordion('brands')}
                    >
                        Brands
                        <span
                            className={`transform transition-transform ${
                                isAccordionOpen.brands ? 'rotate-180' : ''
                            }`}
                        >
                            <svg className="w-4 h-4" data-slot="icon" fill="none" strokeWidth="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path strokeLinecap="round" strokeLinejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
							</svg>
                        </span>
                    </h5>
                    {isAccordionOpen.brands && (
                        <div className="pt-6 flex flex-col gap-5">
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
                    )}
                </div>
            )}
        </div>
    );
};

export default Filters;
