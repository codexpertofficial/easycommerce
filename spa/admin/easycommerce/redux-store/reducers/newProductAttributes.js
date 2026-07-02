export const addInitialAttribute = (state) => {
    state.attributes = [
        {
            name: "",
            items: [],
        },
    ];
};

export const addNewAttribute = (state) => {
    // check if last attribute is empty then dont add also if attributes array is empty then add new attribute
    if (
        state.attributes[state.attributes.length - 1].name === "" ||
        state.attributes[state.attributes.length - 1].items.length === 0
    ) {
        return;
    } else {
        state.attributes.push({
            name: "",
            items: [],
        });
    }
};

export const updateAttributeName = (state, action) => {
    const { index, name } = action.payload;
    if (state.attributes[index]) {
        state.attributes[index].name = name;
    }
};

export const addItemToAttribute = (state, action) => {
    const { index, item } = action.payload;
    if (state.attributes[index]) {
        state.attributes[index].items.push(item);
    }
};

export const deleteAttribute = (state, action) => {
    const { index } = action.payload;
    state.attributes = state.attributes.filter((_, i) => i !== index);
};

export const deleteItemFromAttribute = (state, action) => {
    const { attrIndex, itemIndex } = action.payload;
    if (state.attributes[attrIndex]) {
        state.attributes[attrIndex].items = state.attributes[
            attrIndex
        ].items.filter((_, i) => i !== itemIndex);
    }
};
