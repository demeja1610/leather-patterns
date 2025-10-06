export default function authorFilter() {
    const authorFilter = document.querySelector(".filter-item--author");

    if (!authorFilter) {
        return;
    }

    const searchInput = authorFilter.querySelector(".filter-item--search");
    const authorsList = authorFilter.querySelector(
        ".filter-item__authors-list"
    );

    searchInput.addEventListener("input", (e) => {
        e.preventDefault();

        const searchValue = e.target.value.toLowerCase();
        const authors = authorsList.querySelectorAll(
            ".filter-item__authors-list-item"
        );

        if (searchValue.length === 0) {
            authors.forEach((author) => {
                author.classList.remove("hidden");
            });

            return;
        }

        authors.forEach((author) => {
            const authorName = author
                .querySelector(".filter-item__authors-list-item-label")
                .textContent.toLowerCase();

            if (authorName.includes(searchValue)) {
                author.classList.remove("hidden");
            } else {
                author.classList.add("hidden");
            }
        });
    });
}
