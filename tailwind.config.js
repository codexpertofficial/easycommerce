const plugin = require("tailwindcss/plugin");

module.exports = {
    important: true,
    content: [
        "./app/**/*.php",
        "./views/**/*.php",
        "./spa/**/*.{js,jsx,ts,tsx}",
        "./blocks/**/*.php",
    ],
    theme: {
        extend: {
            height: {
                'ec-input': '42px',
                'ec-attribute': '48px'
            },
            boxShadow: {
                "settings-shadow": "0px 30px 34px -2px #0000001F",
                "search-box-shadow": "0px 9px 51.5px 0px #00000014",
                "duplicate-btn": "0px 4px 8.9px 0px #00000040",
                "refund-shadow": "0px -2px 0px 0px #7351FD",
            },
            keyframes: {
                spin: {
                    from: { transform: "rotate(0deg)" },
                    to: { transform: "rotate(360deg)" },
                },
            },
            animation: {
                spin: "spin 2s linear infinite",
            },
            backgroundImage: {
                "dashboard-total-sales":
                    "linear-gradient(137.15deg, #FFF9F9 0%, #FFEDF8 98.53%)",
                "dashboard-total-orders":
                    "linear-gradient(136.77deg, #FFF5E2 0%, #DEDDFF 96.45%)",
                "dashboard-products-sold":
                    "linear-gradient(136.98deg, #DDFFE9 0%, #FAF1FF 98.79%);",
                "dashboard-new-customers":
                    "linear-gradient(138.57deg, #F1E4FF 1.43%, #EEF8FF 97.79%)",
                "dashboard-gross-profit":
                    "linear-gradient(137.64deg, #D2EAFF 0%, #FBEEFF 97.75%)",

            },
            fontFamily: {
                inter: ['"Inter"', "sans-serif"],
            },
            screens: {
                "ec-db-md": "874px",
                "ec-db-lg": "1620px",
                "ec-db-xl": "1920px",
            },
            colors: {
                "ec-title": "#121216",
                "ec-body" : "#3C3C42",
                "ec-light-black" : "#7F7F98",
                "ec-red" : "#FF3A52",
                "ec-red-bg" : "#FF3A521A",
                "ec-green" : "#009D68",
                "ec-wizard" : "#737791",
                "ec-draft" : "#3748FA",
                "ec-yellow" : "#E8A700",
                "ec-yellow-bg" : "#E8A7000D",
                "ec-table-stock" : "#F0EDFB",
                "ec-table-bg" : "#00000008",
                'ec-main-bg' : "#EEF0FF",

                "ec-primary": "#7351FD", // 100%
                "ec-secondary": "#7351FDCC", // 80%
                "ec-tertiary": "#7351FD40", // 25%
                "ec-accent": "#7351FD0D", // 5%
                "ec-border": "#ebebeb",
                "ec-shadow": '#00000014',
                "ec-modal": "#00000008",
                "ec-placeholder": "#9B9BB5",
                "ec-active": "#F8F6FF",
                "ec-shop3": "#272435",

                "ec-allText" : "#121216",
                "ec-allBg" : "#1212161A",

                // product status colors
                "ec-liveText": "#00A900",
                "ec-liveBg": "#00A9001A",
                "ec-liveBorder": "#00a9003D",
                "ec-trashText": "#FF3A52",
                "ec-trashBg": "#FF3A521A",
                "ec-trashBorder": "#ff3a523D",
                "ec-draftText": "#E8A700",
                "ec-draftBg": "#E8A7001A",
                'ec-draftBorder': '#e8a7003D',

                // order status colors
                "ec-completedText": "#00A900",
                "ec-completedBg": "#00A9001A",
                "ec-completedBorder": "#00a9003D",
                "ec-pendingText": "#FFB310",
                "ec-pendingBg": "#FFB3101A",
                "ec-pendingBorder": "#ffb3103D",
                "ec-processingText": "#1495FF",
                "ec-processingBg": "#1495FF1A",
                "ec-processingBorder": "#1495ff3D",
                "ec-refundedText": "#FF001F",
                "ec-refundedBg": "#FF001F1A",
                "ec-refundedBorder": "#ff001f3D",
                "ec-partiallyRefundedText": "#F89102",
                "ec-partiallyRefundedBg": "#F891021A",
                "ec-partiallyRefundedBorder": "#F89102CC",
                "ec-cancelledText": "#FF1F78",
                "ec-cancelledBg": "#FF1F781A",
                "ec-cancelledBorder": "#ff1f783D",
                "ec-onHoldText": "#555DFF",
                "ec-onHoldBg": "#555DFF1A",
                "ec-onHoldBorder": "#555dff3D",
                "ec-failedText": "#f80317f6",
                "ec-failedBg": "#f8031717",
                "ec-failedBorder": "#f803173D",

                //fullfillment status colors
                "ec-fullfillText" : "#00A900",
                "ec-fullfillBg" : "#00A9001A",
                "ec-fullfillBorder" : "#00a9003D",
                "ec-partiallyFullfilledText" : "#FFB310",
                "ec-partiallyFullfilledBg" : "#FFB3101A",
                "ec-partiallyFullfilledBorder" : "#ffb3103D",
                "ec-shippedText" : "#1495FF",
                "ec-shippedBg" : "#1495FF1A",
                "ec-shippedBorder" : "#1495ff3D",
                "ec-returnedText" : "#FF001F",
                "ec-returnedBg" : "#FF001F1A",
                "ec-returnedBorder" : "#ff001f3D",
                "ec-unfullfilledText" : "#FF1F78",
                "ec-unfullfilledBg" : "#FF1F781A",
                "ec-unfullfilledBorder" : "#ff1f783D",
                "ec-deliveredText" : "#555DFF",
                "ec-deliveredBg" : "#555DFF1A",
                "ec-deliveredBorder" : "#555dff3D",

                //abandonedCart Status colors
                "ec-notContractedText": "#FF3A52",
                "ec-notContractedBg": "#FF3A521A",
                "ec-notContractedBorder": "#ff3a523D",
                "ec-contractedText": "#7351FD",
                "ec-contractedBg": "#7351FD1A",
                "ec-contractedBorder": "#7351fd3D",
                "ec-recoveredText": "#009D68",
                "ec-recoveredBg": "#009D681A",
                "ec-recoveredBorder": "#009d683D",

                //transaction type colors
                "ec-paymentText": "#009D68",
                "ec-paymentBg": "#009D681A",
                "ec-paymentBorder": "#009d683D",
                "ec-refundText": "#FF3A52",
                "ec-refundBg": "#FF3A521A",
                "ec-refundBorder": "#ff3a523D",
                "ec-adjustmentText": "#7351FD",
                "ec-adjustmentBg": "#7351FD1A",
                "ec-adjustmentBorder": "#7351fd3D",

                //customers type colors
                "ec-oneTimeText": "#009D68",
                "ec-oneTimeBg": "#009D681A",
                "ec-oneTimeBorder": "#009d683D",
                "ec-recurringText": "#FF3A52",
                "ec-recurringBg": "#FF3A521A",
                "ec-recurringBorder": "#ff3a523D",

                //coupon status colors
                "ec-activeText": "#009D68",
                "ec-activeBg": "#009D681A",
                "ec-activeBorder": "#009d683D",
                "ec-inactiveText": "#FF3A52",
                "ec-inactiveBg": "#FF3A521A",
                "ec-inactiveBorder": "#ff3a523D",
            },
            gradientColorStops: (theme) => {
                const colors = theme("colors");
                const stops = {};

                // Loop through colors to generate gradientColorStops
                for (const [key, value] of Object.entries(colors)) {
                    if (typeof value === "string") {
                        stops[key] = value;
                    }
                }

                return stops;
            },
        },
    },
    plugins: [
        plugin(function ({ addBase, theme }) {
            const colors = theme("colors");
            const customProps = {};

            // Create CSS variables from color definitions
            for (const [key, value] of Object.entries(colors)) {
                if (typeof value === "string") {
                    customProps[`--color-${key}`] = value;
                }
            }
            //height
            const height = theme("height");
            for (const [key, value] of Object.entries(height)) {
                customProps[`--height-${key}`] = value;
            }
            // Add CSS variables to :root
            addBase({
                ":root": customProps,
            });
        }),
    ],
};
