import { configureStore } from "@reduxjs/toolkit";

// slices
import currentTabReducer from "./slices/currentTab";
import newProductReducer from "./slices/newProduct";
import editProductReducer from "./slices/editProduct";
import toastReducer from "./slices/toastSlice";

export const store = configureStore({
    reducer: {
        currentTab: currentTabReducer,
        newProduct: newProductReducer,
        editProduct: editProductReducer,
        toast: toastReducer,
    },
});
