export default function categoryFilter() {
    const categoryFilter = document.querySelector(".filter-item--category");

    if (!categoryFilter) {
        return;
    }

    const searchInput = categoryFilter.querySelector(".filter-item--search");
    const categoriesList = categoryFilter.querySelector(
        ".filter-item__categories-list"
    );

    searchInput.addEventListener("input", (e) => {
        e.preventDefault();

        const searchValue = e.target.value.toLowerCase();
        const categories = categoriesList.querySelectorAll(
            ".filter-item__categories-list-item"
        );

        if (searchValue.length === 0) {
            categories.forEach((category) => {
                category.classList.remove("hidden");
            });

            return;
        }

        categories.forEach((category) => {
            const categoryName = category
                .querySelector(".filter-item__categories-list-item-label")
                .textContent.toLowerCase();

            if (categoryName.includes(searchValue)) {
                category.classList.remove("hidden");
            } else {
                category.classList.add("hidden");
            }
        });
    });
}
