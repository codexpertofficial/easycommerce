import { createSlice } from "@reduxjs/toolkit";
import {
    addImagesToGallery as addGalleryImages,
    deleteImageFromGallery as removeGalleryImage,
    setImageAsThumbnail as makeThumbnail,
} from "../reducers/editProductGallery";
import {
    addInitialAttribute as createInitialAttribute,
    addNewAttribute as createNewAttribute,
    updateAttributeName as modifyAttributeName,
    addItemToAttribute as addItemToAttributeSet,
    deleteAttribute as removeAttribute,
    deleteItemFromAttribute as removeItemFromAttributeSet,
} from "../reducers/editProductAttributes";
import {
    addNewVariation as createNewVariation,
    duplicateVariation as duplicatePricing,
    deleteVariation as deletePricing,
    updateVariation as updatePricing,
    updateVariationAttribute as updatePricingAttribute,
    updateVariationMeta as updatePricingMeta,
    addVariationDownload as addPricingDownload,
    updateVariationDownload as updatePricingDownload,
    deleteVariationDownload as deletePricingDownload,
} from "../reducers/editProductVariations";

export const defaultPricing = {
    name: "",
    type: "physical",
    status: "in_stock",
    regular_price: 0,
    sale_price: null,
    sku: '',
    stock_quantity: null,
    stock_limit: null,
    attributes: {},
    meta: {
        is_managed_stock: false,
        tax_class: "",
        thumbnail: {
            id: "",
            url: "",
        },
        width: {
            value: null,
            unit: null,
        },
        height: {
            value: null,
            unit: null,
        },
        weight: {
            value: null,
            unit: null,
        },
        length: {
            value: null,
            unit: null,
        },
    },
    downloads: [],
};

const initialState = {
    title: "",
    description: "",
    brands: [],
    slug: "",
    thumbnail: null,
    status: "publish",
    categories: [],
    attributes: [],
    variations: [],
    meta: {
        gallery: [],
        template: "",
        show_review: true,
        review_text_mandatory: false,
        hide_from_shop: false,
        noindex: false,
        publish_date: new Date().toString(),
    },
};

export const editProductSlice = createSlice({
    name: "editProduct",
    initialState: initialState,
    reducers: {
        resetProductData: () => initialState,
        setProductData: (state, action) => {
            state = action.payload;
            return state;
        },
        addTitle: (state, action) => {
            state.title = action.payload;
        },
        addDescription: (state, action) => {
            state.description = action.payload;
        },
        addBrand: (state, action) => {
            if (state.brands.includes(action.payload)) {
                // If the id is already in the array, remove it (uncheck)
                state.brands = state.brands.filter(
                    (brandId) => brandId !== action.payload
                );
            } else {
                // If the id is not in the array, add it (check)
                state.brands = [...state.brands, action.payload];
            }
        },
        removeBrand: (state, action) => {
            state.brands = state.brands.filter(
                (brandId) => brandId !== action.payload
            );
        },
        addSlug: (state, action) => {
            state.slug = action.payload;
        },
        addCategory: (state, action) => {
            if (state.categories.includes(action.payload)) {
                // If the id is already in the array, remove it (uncheck)
                state.categories = state.categories.filter(
                    (categoryId) => categoryId !== action.payload
                );
            } else {
                // If the id is not in the array, add it (check)
                state.categories = [...state.categories, action.payload];
            }
        },
        removeCategory: (state, action) => {
            state.categories = state.categories.filter(
                (categoryId) => categoryId !== action.payload
            );
        },
        updateProductStatus: (state, action) => {
            state.status = action.payload;
        },

        // Gallery Reducers
        addImagesToGallery: addGalleryImages,
        deleteImageFromGallery: removeGalleryImage,
        setImageAsThumbnail: makeThumbnail,

        // Attributes Reducers
        addInitialAttribute: createInitialAttribute,
        addNewAttribute: createNewAttribute,
        updateAttributeName: modifyAttributeName,
        addItemToAttribute: addItemToAttributeSet,
        deleteAttribute: removeAttribute,
        deleteItemFromAttribute: removeItemFromAttributeSet,

        // Product Meta Reducers
        updateProductMeta: (state, action) => {
            const { key, value } = action.payload;

            state.meta[key] = value;
        },

        // Generate Pricing based on attributes
        generatePricing: (state) => {
            function cartesianProduct(arr) {
                return arr.reduce(
                    (acc, curr) => {
                        return acc.flatMap((accItem) =>
                            curr.map((currItem) => [...accItem, currItem])
                        );
                    },
                    [[]]
                );
            }

            // Extract attribute names and values
            const attributeNames = state.meta.attributes.map((attr) =>
                attr.name
            );
            const attributeValues = state.meta.attributes.map(
                (attr) => attr.items
            );

            const combinations = cartesianProduct(attributeValues);

            const variations = combinations.map((combination, index) => {
                const variation = { ...defaultPricing, attributes: {} };
                combination.forEach((value, index) => {
                    variation.attributes[attributeNames[index]] = value;
                });
                variation.name = Object.values(variation.attributes).join(
                    " / "
                );
                variation.sku =
                    state.title + Object.values(variation.attributes).join("");
                variation.sku = variation.sku
                    .trim()
                    .replace(/[^a-zA-Z0-9]/g, "")
                    .toUpperCase();
                return variation;
            });

            state.variations = variations;
        },
        duplicateFirstVariationToAll: (state) => {
            if (state.variations.length > 1) {
                const firstVariation = state.variations[0];
                const { name, sku, attributes, ...dataToDuplicate } =
                    firstVariation;

                state.variations = state.variations.map((variation, index) => {
                    if (index === 0) return variation;

                    return {
                        ...variation,
                        ...dataToDuplicate,
                        name: variation.name,
                        sku: variation.sku,
                        attributes: variation.attributes,
                    };
                });
            }
        },

        // Variations Reducers
        addNewVariation: createNewVariation,
        duplicateVariation: duplicatePricing,
        updateVariation: updatePricing,
        deleteVariation: deletePricing,
        updateVariationAttribute: updatePricingAttribute,
        updateVariationMeta: updatePricingMeta,
        addVariationDownload: addPricingDownload,
        updateVariationDownload: updatePricingDownload,
        deleteVariationDownload: deletePricingDownload,
    },
});

export const {
    resetProductData,
    setProductData,
    addTitle,
    addDescription,
    addBrand,
    removeBrand,
    addSlug,
    addCategory,
    removeCategory,
    updateProductStatus,

    // Gallery Reducers
    addImagesToGallery,
    deleteImageFromGallery,
    setImageAsThumbnail,

    // Attributes Reducers
    addInitialAttribute,
    addNewAttribute,
    updateAttributeName,
    addItemToAttribute,
    deleteAttribute,
    deleteItemFromAttribute,

    // Product Meta Reducers
    updateProductMeta,

    // Variations Reducers
    generatePricing,
    duplicateFirstVariationToAll,
    addNewVariation,
    duplicateVariation,
    updateVariation,
    deleteVariation,
    updateVariationAttribute,
    updateVariationMeta,
    addVariationDownload,
    updateVariationDownload,
    deleteVariationDownload,
} = editProductSlice.actions;
export default editProductSlice.reducer;
