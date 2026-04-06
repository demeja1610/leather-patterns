@extends('layouts.admin.list', [
    'paginator' => $duplicates,
    'title' => __('pattern_file.duplicates'),
    'showFilters' => $activeFilters !== [],
    'filterUrl' => route('admin.page.pattern-files.duplicates'),
    'resetUrl' => route('admin.page.pattern-files.duplicates'),
    'classes' => 'admin-page-pattern-files-duplicates',
])

@section('page-filters')
    <x-input-text.input-text>
        <x-input-text.label for="duplicates_count">
            {{ __('filter.duplicates_count') }}
        </x-input-text.label>

        <x-input-text.input
            id="duplicates_count"
            name="duplicates_count"
            type="number"
            :value="$activeFilters['duplicates_count'] ?? null"
            :title="__('filter.duplicates_count')"
        />
    </x-input-text.input-text>
@endsection

@section('page')
    @if ($duplicates->isEmpty())
        <x-table.empty>
            {{ __('phrases.nothing_found') }}
        </x-table.empty>
    @else
        <x-table.overflow-x-container>
            <x-table.table>
                <x-slot:header>
                    <x-table.head>
                        <x-table.th>
                            {{ __('pattern_file.hash') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_file.duplicates_count') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_file.public_pattern_links') }}
                        </x-table.th>

                        <x-table.th>
                            {{ __('pattern_file.admin_pattern_links') }}
                        </x-table.th>
                    </x-table.head>
                </x-slot:header>

                <x-slot:rows>
                    @foreach ($duplicates as $duplicate)
                        <x-table.tr>
                            <x-table.td>
                                <x-button.copy :copyValue="$duplicate->getHash()"/>
                            </x-table.td>

                            <x-table.td>
                                {{ $duplicate->getDuplicatesCount() }}
                            </x-table.td>

                            <x-table.td>
                                <div class="admin-page-pattern-files-duplicates__public-links">
                                    @foreach ($duplicate->getPatternIds() as $patternId)
                                        <x-link.button-ghost
                                            :href="route('page.pattern.single', ['id' => $patternId])"
                                            target="_blank"
                                        >
                                            {{ $patternId }}

                                            <x-icon.svg name="external-link" />
                                        </x-link.button-ghost>
                                    @endforeach
                                </div>
                            </x-table.td>

                            <x-table.td>
                                <div class="admin-page-pattern-files-duplicates__admin-links">
                                    @foreach ($duplicate->getPatternIds() as $patternId)
                                        <x-link.button-default
                                            :href="route('admin.page.patterns.list', ['id' => $patternId])"
                                            target="_blank"
                                        >
                                            {{ $patternId }}

                                            <x-icon.svg name="external-link" />
                                        </x-link.button-default>
                                    @endforeach
                                </div>
                            </x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot:rows>
            </x-table.table>
        </x-table.overflow-x-container>
    @endif
@endsection
