module.exports = {
    moduleproduction_css: {
        options: {
            seperator: ";"
        },
        files: {
            "../out/src/css/adyen.css": [
                "../out/src/css/adyen.css",
                "node_modules/@adyen/adyen-web/dist/adyen.css"
            ]
        }
    },
    moduledevelopment_js: {
        options: {
            seperator: ";"
        },
        files: {
            "../out/src/js/adyen.min.js": [
                "build/js/adyen.js"
            ]
        }
    }
};