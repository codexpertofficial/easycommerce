const path = require("path");
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const CopyPlugin = require('copy-webpack-plugin');

module.exports = (env, argv) => {
    return {
        mode: argv.mode || "development",
        entry: {
            easycommerce: path.resolve(__dirname, "spa/admin/easycommerce/App.jsx"),
            settings: path.resolve(__dirname, "spa/admin/settings/src/App.jsx"),
            shipping: path.resolve(__dirname, "spa/admin/shipping/src/App.jsx"),
            tax: path.resolve(__dirname, "spa/admin/tax/src/App.jsx"),
            wizard: path.resolve(__dirname, "spa/admin/wizard/src/App.jsx"),
            checkout: path.resolve(__dirname, "spa/public/checkout/App.jsx"),
            dashboard: path.resolve(__dirname, "spa/public/dashboard/App.jsx"),
            auth: path.resolve(__dirname, "spa/public/auth/App.jsx"),
            blocks: path.resolve(__dirname, "blocks/index.js"),
            tailwind: path.resolve(__dirname, "assets/common/css/tailwind.css"),
            editor: path.resolve(__dirname, "spa/admin/editor/index.js"),
        },
        output: {
            filename: "[name].bundle.js",
            path: path.resolve(__dirname, "build"),
        },
        module: {
            rules: [
                {
                    test: /\.jsx?$/,
                    exclude: /node_modules/,
                    use: {
                        loader: "babel-loader",
                        options: {
                            presets: [
                                "@babel/preset-react",
                                "@babel/preset-env",
                            ],
                        },
                    },
                },
                {
                    test: /\.css$/,
                    use: [
                        "style-loader",
                        "css-loader",
                        {
                            loader: "postcss-loader",
                            options: {
                                postcssOptions: {
                                    ident: "postcss",
                                    plugins: [
                                        require("tailwindcss"),
                                        require("autoprefixer"),
                                    ],
                                },
                            },
                        },
                    ],
                },
            ],
        },
        resolve: {
            extensions: [".js", ".jsx"],
        },
        externals: {
            react: "React",
            "react-dom"               : "ReactDOM",
            '@wordpress/hooks'        : [ 'wp', 'hooks' ],
            "@wordpress/blocks"       : [ "wp", "blocks" ],
            "@wordpress/block-editor" : [ "wp", "blockEditor" ],
            '@wordpress/element'      : [ 'wp', 'element' ],
            '@wordpress/components'   : [ 'wp', 'components' ],
            '@wordpress/plugins'      : [ 'wp', 'plugins' ],
            '@wordpress/i18n'         : [ 'wp', 'i18n' ],
            '@wordpress/data'         : [ 'wp', 'data' ],
            '@wordpress/api-fetch'    : [ 'wp', 'apiFetch' ],
            '@wordpress/dom-ready'    : [ 'wp', 'domReady' ],
        },
        plugins: [
            new CleanWebpackPlugin(),
            new CopyPlugin({
                patterns: [
                    {
                        from: '**/*.json',
                        to: 'blocks/[path][name][ext]',
                        context: 'blocks/'
                    }
                ]
            })
        ],
        devtool: argv.mode === "production" ? false : "source-map",
    };
};
