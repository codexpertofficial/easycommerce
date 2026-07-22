import React, { useCallback, useEffect, useState } from "react";
import { createRoot } from "react-dom/client";
import domReady from "@wordpress/dom-ready";

import Login from "./screens/Login";
import Register from "./screens/Register";
import ResetRequest from "./screens/ResetRequest";
import ResetConfirm from "./screens/ResetConfirm";

const SCREENS = ["login", "register", "reset", "reset-confirm"];

/**
 * Resolve the screen to show first:
 *  1. A reset email link (?action=ecrp&key=&login=) forces reset-confirm.
 *  2. A deep link hash (#/register) wins next.
 *  3. Otherwise fall back to the container's data-screen (set by the shortcode).
 */
const resolveInitialScreen = (container, query) => {
    if (query.get("action") === "ecrp" && query.get("key") && query.get("login")) {
        return "reset-confirm";
    }

    const hash = (window.location.hash || "").replace(/^#\/?/, "");
    if (SCREENS.includes(hash)) {
        return hash;
    }

    const dataScreen = container.getAttribute("data-screen");
    return SCREENS.includes(dataScreen) ? dataScreen : "login";
};

const App = ({ container }) => {
    const query = new URLSearchParams(window.location.search);
    const auth = (window.EASYCOMMERCE && window.EASYCOMMERCE.auth) || {};

    const [screen, setScreen] = useState(() => resolveInitialScreen(container, query));
    const [notice, setNotice] = useState("");

    const navigate = useCallback((next, opts = {}) => {
        setNotice(opts.notice || "");
        setScreen(next);
        if (SCREENS.includes(next) && next !== "reset-confirm") {
            window.location.hash = `#/${next}`;
        }
        window.scrollTo({ top: 0, behavior: "smooth" });
    }, []);

    // Keep in sync with browser back/forward on the hash.
    useEffect(() => {
        const onHashChange = () => {
            const hash = (window.location.hash || "").replace(/^#\/?/, "");
            if (SCREENS.includes(hash) && hash !== screen) {
                setNotice("");
                setScreen(hash);
            }
        };
        window.addEventListener("hashchange", onHashChange);
        return () => window.removeEventListener("hashchange", onHashChange);
    }, [screen]);

    switch (screen) {
        case "register":
            return <Register navigate={navigate} auth={auth} />;
        case "reset":
            return <ResetRequest navigate={navigate} />;
        case "reset-confirm":
            return (
                <ResetConfirm
                    navigate={navigate}
                    resetKey={query.get("key") || ""}
                    login={query.get("login") || ""}
                />
            );
        case "login":
        default:
            return <Login navigate={navigate} notice={notice} />;
    }
};

domReady(() => {
    const container = document.getElementById("easycommerce_auth_render");
    if (!container) {
        return;
    }

    const root = createRoot(container);
    root.render(<App container={container} />);
});
