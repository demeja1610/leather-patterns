import Alpine from "alpinejs";
import focus from "@alpinejs/focus";
import toggleTheme from "./components/theme-toggler";
import imagePopups from "./components/image-popups";
import { fetchSelect } from "./components/fetch-select/single";
import { multipleFetchSelect } from "./components/fetch-select/multiple";
import { inputImage } from "./components/input-image/input-image";

document.addEventListener("DOMContentLoaded", () => {
    document.addEventListener("alpine:init", () => {
        Alpine.data("fetchSelect", fetchSelect);
        Alpine.data("multipleFetchSelect", multipleFetchSelect);
        Alpine.data("inputImage", inputImage);
    });

    Alpine.plugin(focus);

    window.Alpine = Alpine;

    Alpine.start();

    toggleTheme();

    imagePopups();
});
