const path = require("path");

module.exports = {
    entry: [
        // Application code
        "./javascript/src/typos.js",
    ],

    output: {
        filename: "typos-page.js",
        path: path.resolve(__dirname, "javascript/dist")
    },

    mode: "development",
};

module.loaders = [
    {
        loader: "babel-loader",

        include: [
            path.resolve(__dirname, "javascript/src")
        ],

        test: "/\.js$/",

        query: {
            presets: ['es2015']
        }
    }
];

