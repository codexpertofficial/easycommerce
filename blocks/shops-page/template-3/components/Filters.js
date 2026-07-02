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

    const minusIcon = (
         <svg width="17" height="3" viewBox="0 0 17 3" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15.625 2.75H1.875C1.51042 2.75 1.21094 2.63281 0.976562 2.39844C0.742188 2.16406 0.625 1.86458 0.625 1.5C0.625 1.13542 0.742188 0.835938 0.976562 0.601562C1.21094 0.367188 1.51042 0.25 1.875 0.25H15.625C15.9896 0.25 16.2891 0.367188 16.5234 0.601562C16.7578 0.835938 16.875 1.13542 16.875 1.5C16.875 1.86458 16.7578 2.16406 16.5234 2.39844C16.2891 2.63281 15.9896 2.75 15.625 2.75Z" fill="#272435"/>
        </svg>
    )
    const plusIcon = (
        <svg width="17" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M7.5 1V14M14 7.5H1" stroke="#272435" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
        </svg>
    )

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

    return (
        <>
            <div className="w-[300px] mb-10 easycommerce-shop-edit-page">
                {/* Categories filter */}
                {categories.length > 0 && (
                    <div className="pt-4 px-2">
                        <h5
                            className="easycommerce-filter-heading bg-[#F8F8F8] border border-[#EBEBEB] p-2 py-4 rounded-t-lg text-[20px] text-[#272435] font-medium cursor-pointer colorec-body w-full flex justify-between items-center"
                            onClick={() => toggleAccordion("categories")}
                        >
                            Categories
                            <span
                                className="flex items-center justify-center w-8 h-8 rounded-[4px] cursor-pointer transition-all duration-300 bg-transparent hover:bg-white"
                            >
                                {isAccordionOpen.categories ? minusIcon : plusIcon}
                            </span>
                        </h5>

                        {isAccordionOpen.categories && (
                            <div className={`flex border ${isAccordionOpen.categories ? 'border-t-0' : 'border-t'} border-[#EBEBEB] justify-between rounded-b-lg p-4`}>
                                <div className="pt-4 flex flex-col gap-5">
                                    {categories.map((category) => (
                                        <RecursiveOptions
                                            key={category.id}
                                            data={category}
                                            labelKey="name"
                                            childrenKey="children"
                                        />
                                    ))}
                                </div>
                                {/* <div className="easycommerce-accordion-line pt-4 p-[5px] w-[4px] h-[80%]"></div> */}
                            </div>
                        )}
                    </div>
                )}

                {/* Price Range Accordion */}
                <div className="pt-4 px-2 ">
                    <h5
                        className="easycommerce-filter-heading p-2 py-4 bg-[#F8F8F8] border border-[#EBEBEB] rounded-t-lg text-[20px] text-[#272435] font-medium cursor-pointer colorec-body w-full flex justify-between items-center"
                        onClick={() => toggleAccordion('price')}
                    >
                        Price Range
                        <span
                            className="flex items-center justify-center w-8 h-8 rounded-[4px] cursor-pointer transition-all duration-300 bg-transparent hover:bg-white"
                        >
                            {isAccordionOpen.price ? minusIcon : plusIcon}
                        </span>
                    </h5>
                    {isAccordionOpen.price && (
                        <div className={`pt-3 border ${isAccordionOpen.price ? 'border-t-0' : 'border-t'} border-[#EBEBEB] rounded-b-lg p-4`}>
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
                        className="pt-4 px-2 "
                    >
                        <h5
                            className="easycommerce-filter-heading p-2 py-4 bg-[#F8F8F8] border border-[#EBEBEB] rounded-t-lg text-[20px] text-[#272435] font-medium cursor-pointer colorec-body w-full flex justify-between items-center"
                            onClick={() => toggleAccordion(attribute.name)}
                        >
                            {attribute.name}
                            <span
                                className="flex items-center justify-center w-8 h-8 rounded-[4px] cursor-pointer transition-all duration-300 bg-transparent hover:bg-white"
                            >
                                {isAccordionOpen[attribute.name] ? minusIcon : plusIcon}
                            </span>
                        </h5>

                        {isAccordionOpen[attribute.name] && (
                            <div className={`flex border ${isAccordionOpen[attribute.name] ? 'border-t-0' : 'border-t'} border-[#EBEBEB] justify-between rounded-b-lg p-4`}>
                                <div className="pt-4 flex flex-col gap-5">
                                    {Object.entries(attribute.options || {}).map(([valueKey, valueName]) => (
                                        <div key={valueKey} className="flex justify-between">
                                            <label className="flex items-center gap-2 ml-2 text-[#111827] text-sm font-medium leading-5">
                                                <input
                                                    type="checkbox"
                                                    className="min-w-5 h-5 easycommerce-input-checkoutbox"
                                                    name={valueKey}
                                                />
                                                {valueName}
                                            </label>
                                        </div>
                                    ))}
                                </div>
                                {/* <div className="easycommerce-accordion-line pt-4 p-[5px] w-[4px] h-[80%]"></div> */}
                            </div>
                        )}
                    </div>
                ))}

                {/* Brands Accordion */}
                {brands.length > 0 && (
                    <div className="pt-4 px-2 ">
                        <h5
                            className="easycommerce-filter-heading p-2 py-4 bg-[#F8F8F8] rounded-t-lg text-[20px] text-[#272435] font-medium cursor-pointer border border-[#EBEBEB] colorec-body w-full flex justify-between items-center"
                            onClick={() => toggleAccordion('brands')}
                        >
                            Brands
                            <span
                                className="flex items-center justify-center w-8 h-8 rounded-[4px] cursor-pointer transition-all duration-300 bg-transparent hover:bg-white"
                            >
                                {isAccordionOpen.brands ? minusIcon : plusIcon}
                            </span>
                        </h5>

                        {isAccordionOpen.brands && (
                            <div className={`flex border border-[#EBEBEB] ${isAccordionOpen.brands ? 'border-t-0' : 'border-t'} justify-between rounded-b-lg p-4`}>
                                <div className="pt-4 flex flex-col gap-5">
                                {brands &&
                                    brands.map((brand) => (
                                    <RecursiveOptions
                                        key={brand.id}
                                        data={brand}
                                        labelKey="name"
                                        childrenKey="children"
                                    />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                )}

            </div>
        </>
    );
};

export default Filters;
