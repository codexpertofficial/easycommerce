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
    if (state.thumbnail && state.thumbnail === action.payload) {
        state.thumbnail = null;
    }
};

export const setImageAsThumbnail = (state, action) => {
    if (state.thumbnail === action.payload) {
        state.thumbnail = null;
    } else {
        state.thumbnail = action.payload;
    }
};
