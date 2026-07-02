export const addInitialAttribute = (state) => {
    state.meta.attributes = [
        {
            name: "",
            items: [],
        },
    ];
};

export const addNewAttribute = (state) => {
    // check if last attribute is empty then dont add also if attributes array is empty then add new attribute
    if (
        state.meta.attributes[state.meta.attributes.length - 1].name === "" ||
        state.meta.attributes[state.meta.attributes.length - 1].items.length ===
            0
    ) {
        return;
    } else {
        state.meta.attributes.push({
            name: "",
            items: [],
        });
    }
};

export const updateAttributeName = (state, action) => {
    const { index, name } = action.payload;
    if (state.meta.attributes[index]) {
        state.meta.attributes[index].name = name;
    }
};

export const addItemToAttribute = (state, action) => {
    const { index, item } = action.payload;
    if (state.meta.attributes[index]) {
        state.meta.attributes[index].items.push(item);
    }
};

export const deleteAttribute = (state, action) => {
    const { index } = action.payload;
    state.meta.attributes = state.meta.attributes.filter((_, i) => i !== index);
};

export const deleteItemFromAttribute = (state, action) => {
    const { attrIndex, itemIndex } = action.payload;
    if (state.meta.attributes[attrIndex]) {
        state.meta.attributes[attrIndex].items = state.meta.attributes[
            attrIndex
        ].items.filter((_, i) => i !== itemIndex);
    }
};
