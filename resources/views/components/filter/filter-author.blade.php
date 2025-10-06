<x-filter.filter-item
    :title="__('filter.filter_authors_title')"
    :class="'filter-item--author'"
>
    <x-filter.filter-search :placeholder="__('filter.filter_authors_search')" />

    <div class="filter-item__authors-list">
        @foreach ($authors as $author)
            <div class="filter-item__authors-list-item">
                <div class="checkbox">
                    <input
                        type="checkbox"
                        class="checkbox__input"
                        id="author-{{ $author->id }}"
                        name="author[]"
                        value="{{ $author->id }}"
                        @checked(in_array($author->id, $activeAuthors))
                    />

                    <label
                        class="checkbox__label filter-item__authors-list-item-label"
                        for="author-{{ $author->id }}"
                    >
                        {{ $author->name }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>
</x-filter.filter-item>
