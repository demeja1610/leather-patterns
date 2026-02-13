import V1ApiClient from "@js/clients/api/v1-api-client";

export default function patternFilters() {
    const filters = document.querySelectorAll(
        ".filter-item-category,.filter-item-tag,.filter-item-author",
    );

    if (filters.length === 0) {
        return;
    }

    filters.forEach((filter) => {
        const loadMoreEl = filter.querySelector("[data-load-more]");

        if (loadMoreEl === null) {
            return;
        }

        const loader = loadMoreEl.querySelector(".loader");
        const text = loadMoreEl.querySelector(".text");

        loadMoreEl.addEventListener("click", async (e) => {
            e.preventDefault();

            if (loadMoreEl.classList.contains("loading")) {
                return;
            }

            loadMoreEl.classList.add("loading");
            loader.classList.remove("dn");
            text.classList.add("dn");

            let loaded = false;

            try {
                const client = new V1ApiClient();

                let data;

                if (filter.classList.contains("filter-item-category")) {
                    data = await client.getAllPatternCategories();
                } else if (filter.classList.contains("filter-item-tag")) {
                    data = await client.getAllPatternTags();
                } else if (filter.classList.contains("filter-item-author")) {
                    data = await client.getAllPatternAuthors();
                } else {
                    throw new Error("Unknown filter element");
                }

                const template = filter.querySelector("template");
                const list = filter.querySelector("ul");

                const alreadyShowedValues = [
                    ...list.querySelectorAll('input[type="checkbox"]'),
                ].map((item) => parseInt(item.value));

                data.data.forEach((patternCategory) => {
                    if (alreadyShowedValues.includes(patternCategory.id)) {
                        return;
                    }

                    const templateContent = template.content.cloneNode(true);

                    const checkbox = templateContent.querySelector(
                        'input[type="checkbox"]',
                    );

                    if (checkbox !== null) {
                        checkbox.value = patternCategory.id;
                    }

                    const label = templateContent.querySelector("label");

                    if (label !== null) {
                        label.insertAdjacentText(
                            "beforeend",
                            patternCategory.name,
                        );
                    }

                    list.insertBefore(templateContent, loadMoreEl.parentNode);

                    loaded = true;
                });
            } catch (error) {
                console.log(error);
            } finally {
                if (loaded === false) {
                    loadMoreEl.classList.remove("loading");
                    loader.classList.add("dn");
                    text.classList.remove("dn");
                } else {
                    loadMoreEl.parentElement.remove();
                }
            }
        });
    });
}
