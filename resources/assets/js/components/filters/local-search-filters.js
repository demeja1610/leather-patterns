export default function localSearchFilters() {
    const filters = document.querySelectorAll("[data-filter]");

    if (filters.length === 0) {
        return;
    }

    filters.forEach((filter) => {
        let searchInput = filter.querySelector("[data-filter-input]");

        if (searchInput.tagName !== "INPUT") {
            searchInput = searchInput.querySelector("input");
        }

        if (searchInput === null) {
            return;
        }

        searchInput.addEventListener("input", (e) => {
            e.preventDefault();

            const searchValue = e.target.value.toLowerCase();
            const filterItems = filter.querySelectorAll("[data-filter-item]");

            if (filterItems.length === 0) {
                return;
            }

            if (searchValue.length === 0) {
                filterItems.forEach((filterItem) => {
                    filterItem.classList.remove("hidden");
                });

                toggleFilterEmpty(filter, filterItems);

                return;
            }

            filterItems.forEach((filterItem) => {
                const filterItemText = filterItem
                    .querySelector("[data-filter-text]")
                    .textContent.toLowerCase();

                if (filterItemText.includes(searchValue)) {
                    filterItem.classList.remove("hidden");
                } else {
                    filterItem.classList.add("hidden");
                }
            });

            toggleFilterEmpty(filter, filterItems);
        });
    });
}

function toggleFilterEmpty(filter, filterItems) {
    const hiddenItems = filter.querySelectorAll("[data-filter-item].hidden");

    if (hiddenItems.length === filterItems.length) {
        filter.classList.add("empty");
    } else {
        filter.classList.remove("empty");
    }
}
