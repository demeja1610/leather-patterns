export default function tagFilter() {
    const tagFilter = document.querySelector(".filter-item--tag");

    if (!tagFilter) {
        return;
    }

    const searchInput = tagFilter.querySelector(".filter-item--search");
    const tagsList = tagFilter.querySelector(".filter-item__tags-list");

    searchInput.addEventListener("input", (e) => {
        e.preventDefault();

        const searchValue = e.target.value.toLowerCase();
        const tags = tagsList.querySelectorAll(".filter-item__tags-list-item");

        if (searchValue.length === 0) {
            tags.forEach((tag) => {
                tag.classList.remove("hidden");
            });

            return;
        }

        tags.forEach((tag) => {
            const tagName = tag
                .querySelector(".filter-item__tags-list-item-label")
                .textContent.toLowerCase();

            if (tagName.includes(searchValue)) {
                tag.classList.remove("hidden");
            } else {
                tag.classList.add("hidden");
            }
        });
    });
}
