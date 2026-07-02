import { defaultPricing } from "../slices/editProduct";

export const addNewVariation = (state, action) => {
    state.variations = [
        ...state.variations,
        { ...defaultPricing, sku: action.payload }
    ];
};

export const duplicateVariation = (state, action) => {
    const { index } = action.payload;

    state.variations = [
        ...state.variations.slice(0, index + 1),
        state.variations[index],
        ...state.variations.slice(index + 1),
    ];
};

export const updateVariation = (state, action) => {
    const { index, key, value } = action.payload;

    state.variations[index][key] = value;
};

export const deleteVariation = (state, action) => {
    const { index } = action.payload;
    state.variations = state.variations.filter((_, i) => i !== index);
};

export const updateVariationAttribute = (state, action) => {
    const { index, key, value } = action.payload;

    state.variations[index].attributes = {
        ...state.variations[index].attributes,
        [key]: value,
    };
};

export const updateVariationMeta = (state, action) => {
    const { index, key, value } = action.payload;

    state.variations[index].meta[key] = value;
};

export const addVariationDownload = (state, action) => {
    const { index, download } = action.payload;
    state.variations[index].downloads = [
        ...state.variations[index].downloads,
        ...download,
    ];
};

export const updateVariationDownload = (state, action) => {
    const { index, downloadId, key, value } = action.payload;

    state.variations[index].downloads = state.variations[index].downloads.map(
        (download) => {
            if (download.media_id === downloadId) {
                return {
                    ...download,
                    [key]: value,
                };
            }

            return download;
        }
    );
};

export const deleteVariationDownload = (state, action) => {
    const { index, downloadId } = action.payload;
    state.variations[index].downloads = state.variations[
        index
    ].downloads.filter((download) => download.media_id !== downloadId);
};
