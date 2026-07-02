export const addImagesToGallery = (state, action) => {
    const uniqueNewImages = action.payload.filter(
        (newImg) => !state.meta.gallery.some((img) => img.id === newImg.id)
    );

    state.meta.gallery = [...state.meta.gallery, ...uniqueNewImages];
};

export const deleteImageFromGallery = (state, action) => {
    state.meta.gallery = state.meta.gallery.filter(
        (image) => image.id !== action.payload
    );

    // If the deleted image was the thumbnail then reset the thumbnail
    if (state.thumbnail && state.thumbnail.id === action.payload) {
        state.thumbnail = {
            id: "",
            url: "",
        };
    }
};

export const setImageAsThumbnail = (state, action) => {
    const { id } = action.payload;
    if (state.thumbnail && state.thumbnail.id === id) {
        state.thumbnail = {
            id: "",
            url: "",
        };
    } else {
        state.thumbnail = action.payload;
    }
};
