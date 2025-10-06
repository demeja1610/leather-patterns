<x-filter.filter-item
    :title="__('filter.filter_categories_title')"
    :class="'filter-item--category'"
>
    <x-filter.filter-search :placeholder="__('filter.filter_categories_search')" />

    <div class="filter-item__categories-list">
        @foreach ($categories as $category)
            <div class="filter-item__categories-list-item">
                <div class="checkbox">
                    <input
                        type="checkbox"
                        class="checkbox__input"
                        id="category-{{ $category->id }}"
                        name="category[]"
                        value="{{ $category->id }}"
                        @checked(in_array($category->id, $activeCategories))
                    />

                    <label
                        class="checkbox__label filter-item__categories-list-item-label"
                        for="category-{{ $category->id }}"
                    >
                        {{ $category->name }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>
</x-filter.filter-item>
