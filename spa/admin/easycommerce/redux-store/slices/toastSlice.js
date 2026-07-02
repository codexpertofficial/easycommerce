import { createSlice } from "@reduxjs/toolkit";

export const toastSlice = createSlice({
    name: "toast",
    initialState: {},
    reducers: {
        addToastData: (state, action) => {
            const {
                type,
                message = "",
                position = "top-right",
                autoClose = 1000,
            } = action.payload;

            state["type"] = type;
            state["position"] = position;
            state["message"] = message;
            state["autoClose"] = autoClose;
        },
        clearToast: (state) => {
            state = {};
            return state;
        },
    },
});

export const { addToastData, clearToast } = toastSlice.actions;
export default toastSlice.reducer;
