import Alpine from "alpinejs";
import focus from "@alpinejs/focus";
import toggleTheme from "./components/theme-toggler";
import { fetchSelect } from "./components/fetch-select/single";

document.addEventListener("DOMContentLoaded", () => {
    document.addEventListener("alpine:init", () => {
        Alpine.data("fetchSelect", fetchSelect);
    });

    Alpine.plugin(focus);

    window.Alpine = Alpine;

    Alpine.start();

    toggleTheme();
});
