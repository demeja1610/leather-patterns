<x-filter.filter-item
    :title="__('filter.filter_tags_title')"
    :class="'filter-item--tag'"
>
    <x-filter.filter-search :placeholder="__('filter.filter_tags_search')" />

    <div class="filter-item__tags-list">
        @foreach ($tags as $tag)
            <div class="filter-item__tags-list-item">
                <div class="checkbox">
                    <input
                        type="checkbox"
                        class="checkbox__input"
                        id="tag-{{ $tag->id }}"
                        name="tag[]"
                        value="{{ $tag->id }}"
                        @checked(in_array($tag->id, $activeTags))
                    />

                    <label
                        class="checkbox__label filter-item__tags-list-item-label"
                        for="tag-{{ $tag->id }}"
                    >
                        {{ $tag->name }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>
</x-filter.filter-item>
