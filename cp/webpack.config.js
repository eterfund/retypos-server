const path = require("path");
const webpack = require("webpack");

module.exports = {
    entry: [
        // Application code
        "./javascript/src/typos.jsx",
    ],

    output: {
        filename: "typos-page.js",
        path: path.resolve(__dirname, "javascript/dist")
    },

    resolve: {
        extensions: [".js", ".jsx"]
    },

    mode: "development",

    // Fix an error about 'fs'
    node: {
        fs: "empty"
    },

    module: {
        rules: [
            {
                loader: "babel-loader",

                exclude: /node_modules/,

                test: /\.js[x]?$/,
            },

            {
                test: /\.css$/,
                use: [
                    { loader: "style-loader" },
                    { loader: "css-loader" }
                ]
            }
        ]
    }
};

