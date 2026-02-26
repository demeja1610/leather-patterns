import Alpine from "alpinejs";
import focus from "@alpinejs/focus";
import toggleTheme from "./components/theme-toggler";
import { fetchSelect } from "./components/fetch-select/single";
import { multipleFetchSelect } from "./components/fetch-select/multiple";

document.addEventListener("DOMContentLoaded", () => {
    document.addEventListener("alpine:init", () => {
        Alpine.data("fetchSelect", fetchSelect);
        Alpine.data("multipleFetchSelect", multipleFetchSelect);
    });

    Alpine.plugin(focus);

    window.Alpine = Alpine;

    Alpine.start();

    toggleTheme();
});
