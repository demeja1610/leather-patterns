import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/assets/scss/app.scss",
                "resources/assets/js/app.js",
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            "@styles": "/resources/assets/scss",
            "@node_modules": "/node_modules",
        },
    },
});
