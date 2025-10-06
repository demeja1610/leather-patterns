import filters from "./components/filters/filters";
import imagePopups from "./components/image-popups";
import toggleTheme from "./components/theme-toggler";

document.addEventListener("DOMContentLoaded", () => {
    toggleTheme();
    imagePopups();
    filters();

    document.querySelectorAll(".filter label").forEach((label) => {
        label.addEventListener("click", (e) => {
            e.preventDefault();

            const labelFor = label.getAttribute("for");

            if (!labelFor) {
                return;
            }

            const input = document.getElementById(labelFor);

            if (!input) {
                return;
            }

            input.click();
        });
    });
});
