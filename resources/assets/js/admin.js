import Alpine from "alpinejs";
import toggleTheme from "./components/theme-toggler";

document.addEventListener("DOMContentLoaded", () => {
    window.Alpine = Alpine;

    Alpine.start();
    toggleTheme();
});
