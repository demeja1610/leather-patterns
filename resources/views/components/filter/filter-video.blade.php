<x-filter.filter-item :class="'filter-item--video'">
    <div class="filter-item__video-list">
        <div class="checkbox">
            <input
                type="checkbox"
                class="checkbox__input"
                id="has_video_checkbox"
                name="has_video"
                value="has_video"
                @checked($checked ?? false)
            />

            <label
                class="checkbox__label filter-item__video-list-item-label"
                for="has_video_checkbox"
            >
                С видео
            </label>
        </div>
    </div>
</x-filter.filter-item>
