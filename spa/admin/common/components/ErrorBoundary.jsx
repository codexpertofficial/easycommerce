import React from "react";
import { __ } from "@wordpress/i18n";

/**
 * Catches a render error in the tree below it and shows a visible message with
 * copyable diagnostics, instead of letting React unmount the whole admin app
 * and leave a blank page.
 *
 * Give it a `resetKey` (for example the current route) and it recovers
 * automatically when that value changes, so one broken page does not take the
 * navigation down with it.
 */
class ErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { error: null, info: null, copied: false };
    }

    static getDerivedStateFromError(error) {
        return { error };
    }

    componentDidCatch(error, info) {
        this.setState({ info });
        // Keep the original error in the console for whoever is debugging.
        // eslint-disable-next-line no-console
        console.error("EasyCommerce admin error:", error, info?.componentStack);
    }

    componentDidUpdate(prevProps) {
        if (this.state.error && prevProps.resetKey !== this.props.resetKey) {
            this.setState({ error: null, info: null, copied: false });
        }
    }

    diagnostics() {
        const { error, info } = this.state;
        const stack = (info?.componentStack || "").trim().split("\n").slice(0, 6).join("\n");

        return [
            `EasyCommerce admin error`,
            `Message: ${error?.message || String(error)}`,
            `Page: ${window.location.pathname}${window.location.search}${window.location.hash}`,
            `Browser: ${navigator.userAgent}`,
            stack ? `Component stack:\n${stack}` : "",
        ]
            .filter(Boolean)
            .join("\n");
    }

    copy = () => {
        const text = this.diagnostics();
        const done = () => this.setState({ copied: true });

        if (navigator.clipboard?.writeText) {
            navigator.clipboard.writeText(text).then(done, () => this.fallbackCopy(text, done));
        } else {
            this.fallbackCopy(text, done);
        }
    };

    fallbackCopy(text, done) {
        const area = document.createElement("textarea");
        area.value = text;
        document.body.appendChild(area);
        area.select();
        try {
            document.execCommand("copy");
            done();
        } finally {
            document.body.removeChild(area);
        }
    }

    render() {
        if (!this.state.error) {
            return this.props.children;
        }

        const title = this.props.title || __("This page couldn't be displayed", "easycommerce");

        return (
            <div role="alert" className="m-6 rounded-xl border border-ec-red bg-white p-6 text-ec-body">
                <h2 className="mt-0 mb-2 text-xl font-semibold text-ec-red">{title}</h2>
                <p className="mt-0 mb-4 text-sm">
                    {__(
                        "Something went wrong while loading this part of EasyCommerce. Your store data is not affected. Reload the page to try again; if it keeps happening, copy the details below and share them in the community.",
                        "easycommerce"
                    )}
                </p>
                <pre className="mb-4 max-h-56 overflow-auto rounded-lg bg-ec-main-bg p-3 text-xs whitespace-pre-wrap">
                    {this.diagnostics()}
                </pre>
                <div className="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        className="cursor-pointer rounded-lg border-0 bg-ec-primary px-4 py-2 text-sm font-medium text-white"
                        onClick={() => window.location.reload()}
                    >
                        {__("Reload page", "easycommerce")}
                    </button>
                    <button
                        type="button"
                        className="cursor-pointer rounded-lg border border-ec-primary bg-white px-4 py-2 text-sm font-medium text-ec-primary"
                        onClick={this.copy}
                    >
                        {this.state.copied ? __("Copied", "easycommerce") : __("Copy details", "easycommerce")}
                    </button>
                    <a
                        className="text-sm text-ec-primary underline"
                        href={window.EASYCOMMERCE?.community_url || "https://community.codexpert.io/c/space/easycommerce"}
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {__("Get help", "easycommerce")}
                    </a>
                </div>
            </div>
        );
    }
}

export default ErrorBoundary;
