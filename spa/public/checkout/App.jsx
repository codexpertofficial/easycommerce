import React, { useEffect, useState } from "react";
import { createRoot } from "react-dom/client";
import { SlotFillProvider } from '@wordpress/components';
import Templates from "./templates";

// import "./style.css";

const App = () => {
    const [activeTemplate, setActiveTemplate] = useState("");
    const templates = ["template-1", "template-2", "template-3"];

    useEffect(() => {
        const container = document.getElementById(
            "easycommerce_checkout_render"
        );

        // get clasname from container
        const className = container.className;

        if (templates.includes(className)) {
            setActiveTemplate(className);
        }
    }, []);

    return (
        <>
            {activeTemplate ? (
                <Templates activeTemplate={activeTemplate} />
            ) : (
                <p>Loading...</p>
            )}
        </>
    );
};

const container = document.getElementById("easycommerce_checkout_render");
const root = createRoot(container);
root.render(
	<SlotFillProvider>
		<App />
	</SlotFillProvider>
);
