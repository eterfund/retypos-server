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

    mode: "development",

    module: {
        rules: [
            {
                loader: "babel-loader",

                exclude: /node_modules/,

                test: /\.js[x]?$/,
            }
        ]
    }
};

