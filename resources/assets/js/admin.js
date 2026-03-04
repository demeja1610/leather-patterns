import Alpine from "alpinejs";
import focus from "@alpinejs/focus";
import toggleTheme from "./components/theme-toggler";
import imagePopups from "./components/image-popups";
import { fetchSelect } from "./components/fetch-select/single";
import { previewInputFile } from "./components/input-file/preview";
import { previewInputImage } from "./components/input-image/preview";
import { multipleFetchSelect } from "./components/fetch-select/multiple";

document.addEventListener("DOMContentLoaded", () => {
    document.addEventListener("alpine:init", () => {
        Alpine.data("fetchSelect", fetchSelect);
        Alpine.data("multipleFetchSelect", multipleFetchSelect);
        Alpine.data("previewInputFile", previewInputFile);
        Alpine.data("previewInputImage", previewInputImage);
    });

    Alpine.plugin(focus);

    window.Alpine = Alpine;

    Alpine.start();

    toggleTheme();

    imagePopups();
});
