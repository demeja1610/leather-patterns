<x-filter.filter-item :class="'filter-item--review'">
    <div class="filter-item__review-list">
        <div class="checkbox">
            <input
                type="checkbox"
                class="checkbox__input"
                id="has_review_checkbox"
                name="has_review"
                value="has_review"
                @checked($checked ?? false)
            />

            <label
                class="checkbox__label filter-item__review-list-item-label"
                for="has_review_checkbox"
            >
                С отзывами
            </label>
        </div>
    </div>
</x-filter.filter-item>
