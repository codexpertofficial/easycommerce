import React from "react";

// components
import DropdownField from "./DropdownField";
import TextField from "../../../../common/components/inputs/TextField";

const ProductTableFilter = ({
    categories,
    formState,
    productsFiltered,
    setFormState,
    filterProducts,
    resetFilter,
}) => {
    const categoryOptions = categories.map((category) => ({
        value: category.slug,
        label: category.name,
    }));

    const sortByOptions = Object.keys(EASYCOMMERCE.sort_options).map((key) => ({
        value: key,
        label: EASYCOMMERCE.sort_options[key],
    }));

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormState((prevState) => ({
            ...prevState,
            [name]: value,
        }));
    };

    const handleDropdownChange = (field, value) => {
        if (field === "category") {
            setFormState((prevState) => ({
                ...prevState,
                [field]: [value],
            }));
        } else {
            setFormState((prevState) => ({
                ...prevState,
                [field]: value,
            }));
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        filterProducts();
    };

    return (
        <div className="flex justify-between items-center gap-6">
            {productsFiltered && (
                <button
                    className={`font-inter text-base leading-[26px] font-normal text-ec-secondary border-b border-[#737991]`}
                    onClick={resetFilter}
                >
                    Reset
                </button>
            )}

            <form
                className="flex items-center justify-between gap-3"
                onSubmit={handleSubmit}
            >
                <div className="easycommerce-search-container relative flex items-center">
                    {/* <img
                        src={searchBar}
                        alt="Search Icon"
                        className="absolute w-4 h-4 ml-3 left-0"
                    /> */}
                    {/* <input
                        type="text"
                        name="search"
                        value={formState.search}
                        onChange={handleInputChange}
                        placeholder="Search ..."
                        className="easycommerce-search-input placeholder:text-ec-placeholder !text-ec-body 
                        w-[200px] min-[1400px]:w-[320px] font-normal text-sm leading-[26px] font-inter h-[48px] border 
                        border-ec-border !rounded-lg transition-all ease-in-out duration-300"
                    /> */}
                    <TextField
                        className="w-[120px] lg:w-[120px] min-[1440px]:w-[190px] h-ec-input px-3 py-2"
                        name="search"
                        value={formState.search}
                        onChange={handleInputChange}
                        placeholder="Search"
                        />
                </div>
                <div className="easycommerce-select-container relative flex items-center gap-3">
                    <DropdownField
                        currentValue={formState.category[0] || ""}
                        options={categoryOptions}
                        placeholder="Category"
                        width="auto"
                        menuWidth="150px"
                        onChange={(value) =>
                            handleDropdownChange("category", value)
                        }
                    />
                    <div className="easycommerce-select-container relative flex items-center">
                        <DropdownField
                            currentValue={formState.sortBy || ""}
                            options={sortByOptions}
                            placeholder="Sort by"
                            width="120px"
                            menuWidth="160px"
                            onChange={(value) =>
                                handleDropdownChange("sortBy", value)
                            }
                        />
                    </div>
                </div>
                <button
                    type="submit"
                    className="w-[38px] h-ec-input flex justify-center ease-in-out hover:bg-ec-secondary transition duration-300 
                    items-center bg-ec-primary rounded-lg"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="17"
                        height="13"
                        viewBox="0 0 17 16"
                        fill="none"
                    >
                        <path
                            d="M15.75 14.7188C16.0625 15.0729 16.0729 15.4271 15.7812 15.7812C15.6354 15.9271 15.4583 
                            16 15.25 16C15.0625 16 14.875 15.9271 14.6875 15.7812L10.5 11.5938C9.375 12.5104 8.03125 12.9792 
                            6.46875 13C4.63542 12.9583 3.11458 12.3229 1.90625 11.0938C0.677083 9.86458 0.0416667 8.33333 0 
                            6.5C0.0416667 4.66667 0.666667 3.13542 1.875 1.90625C3.10417 0.677083 4.63542 0.0416667 6.46875 
                            0C8.30208 0.0416667 9.83333 0.677083 11.0625 1.90625C12.2917 3.13542 12.9271 4.66667 12.9688 
                            6.5C12.9479 8.04167 12.4792 9.38542 11.5625 10.5312L15.75 14.7188ZM1.5 6.5C1.54167 7.91667 2.03125 
                            9.09375 2.96875 10.0312C3.90625 10.9688 5.08333 11.4583 6.5 11.5C7.91667 11.4583 9.09375 10.9688 
                            10.0312 10.0312C10.9688 9.09375 11.4583 7.91667 11.5 6.5C11.4583 5.08333 10.9688 3.90625 10.0312 
                            2.96875C9.09375 2.03125 7.91667 1.54167 6.5 1.5C5.08333 1.54167 3.90625 2.03125 2.96875 2.96875C2.03125 
                            3.90625 1.54167 5.08333 1.5 6.5Z"
                            fill="white"
                        />
                    </svg>
                </button>
            </form>
        </div>
    );
};

export default ProductTableFilter;