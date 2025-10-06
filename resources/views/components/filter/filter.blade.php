<div class="filter">
    {{ $slot }}

    <button
        type="submit"
        class="button filter__submit"
        title="{{ __('filter.apply') }}"
    >
        {{ __('filter.apply') }}
    </button>

    <a
        href="{{ $resetUrl }}"
        class="button button--ghost filter__reset"
        title="{{ __('filter.reset') }}"
    >
        {{ __('filter.reset') }}
    </a>
</div>
