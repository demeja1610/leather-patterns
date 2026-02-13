import localSearchFilters from "@js/components/filters/local-search-filters";
import patternFilters from "@js/components/filters/pattern-filters";
import toggleTheme from "@js/components/theme-toggler";
import imagePopups from "@js/components/image-popups";

document.addEventListener("DOMContentLoaded", async () => {
    toggleTheme();

    imagePopups();

    localSearchFilters();

    patternFilters();
});
