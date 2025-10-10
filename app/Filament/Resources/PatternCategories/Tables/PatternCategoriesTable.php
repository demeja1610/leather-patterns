<?php

namespace App\Filament\Resources\PatternCategories\Tables;

use App\Filament\Filters\Date\DateFilter;
use App\Filament\Filters\Date\DateRangeFilter;
use Carbon\Carbon;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Filament\Tables\Enums\PaginationMode;
use Illuminate\Database\Eloquent\Builder;

class PatternCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->paginationMode(PaginationMode::Cursor)
            ->defaultSort(column: 'id', direction: 'desc')
            ->striped()
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('patterns_count')
                    ->counts('patterns')
                    ->sortable()
                    ->badge(),

                TextColumn::make('created_at')
                    ->sortable()
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d.m.Y H:i')),

                TextColumn::make('updated_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d.m.Y H:i')),
            ])
            ->filters([
                DateRangeFilter::make(
                    column: 'created_at',
                    fromFilterName: 'created_from',
                    toFilterName: 'created_until',
                    fromIndicatorName: 'Created From',
                    toIndicatorName: 'Created Until'
                )
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->size(Size::Medium),

                DeleteAction::make()
                    ->iconButton()
                    ->size(Size::Medium),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
