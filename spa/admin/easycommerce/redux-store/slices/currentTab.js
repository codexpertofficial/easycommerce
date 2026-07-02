import { createSlice } from "@reduxjs/toolkit";

export const currentTabSlice = createSlice({
    name: "currentTab",
    initialState: {
        active: "/dashboard",
    },
    reducers: {
        setCurrentTab: (state, action) => {
            state.active = action.payload;
        },
    },
});

export const { setCurrentTab } = currentTabSlice.actions;
export default currentTabSlice.reducer;
