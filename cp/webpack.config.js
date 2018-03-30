const path = require("path");

module.exports = {
    entry: "./javascript/typos.js",
    output: {
        filename: "typos-page.js",
        path: path.resolve(__dirname, "javascript/dist")
    },
    mode: "development"
};

