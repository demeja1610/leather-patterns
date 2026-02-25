@extends('layouts.admin.default')

@section('content')
    <div
        class="admin-page admin-page-single {{ $classes ?? '' }}"
        @isset($xData)
            x-data="{{ $xData }}"
        @endisset
    >
        <x-admin.page-header.header
            class="admin-page-single__header"
            :title="$title ?? null"
        >
            @yield('header-content')

            <x-slot:actions>
                @yield('header-actions')
            </x-slot:actions>
        </x-admin.page-header.header>

        <div class="admin-page-single__content">
            @yield('page')
        </div>
    </div>
@endsection
