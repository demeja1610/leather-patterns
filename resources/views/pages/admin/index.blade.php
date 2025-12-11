@extends('layouts.admin')

@section('content')
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 60vh;">
        <svg
            width="180"
            height="180"
            viewBox="0 0 180 180"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <circle
                cx="90"
                cy="90"
                r="85"
                style="stroke: var(--red-1);"
                stroke-width="4"
            />

            <circle
                cx="65"
                cy="80"
                r="12"
                style="fill: var(--red-1);"
            />
            <circle
                cx="115"
                cy="80"
                r="12"
                style="fill: var(--red-1);"
            />

            <path
                d="M70 105 Q90 125 110 105"
                style="stroke: var(--red-1);"
                stroke-width="4"
                fill="none"
                stroke-linecap="round"
            />

        </svg>
    </div>
@endsection
