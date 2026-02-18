@props(['notifications' => null])

@if ($notifications !== null)
    <div {{ $attributes->merge(['class' => 'page-notifications']) }}>
        @foreach ($notifications as $notification)
            <div class="page-notifications__item page-notifications__item--{{ $notification->getType()->value }}">
                <p class="page-notifications__item-text">
                    {{ $notification->getText() }}
                </p>

                <button
                    class="page-notifications__item-remove"
                    onclick="this.closest('div').remove()"
                ></button>
            </div>
        @endforeach
    </div>
@endif
