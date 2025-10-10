<?php

namespace App\Filament\Filters\Date;

use Carbon\Carbon;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Forms\Components\DatePicker;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as BuilderImplementation;

class DateRangeFilter
{
    public static function make(
        string $column,
        string $fromFilterName,
        string $toFilterName,
        string $fromIndicatorName,
        string $toIndicatorName
    ): Filter {
        return Filter::make($column)
            ->schema([
                DatePicker::make($fromFilterName),
                DatePicker::make($toFilterName),
            ])
            ->query(function (Builder $query, array $data) use (&$column, &$fromFilterName, &$toFilterName): Builder {
                /**
                 * @var BuilderImplementation $query
                 */
                return $query
                    ->when(
                        value: $data[$fromFilterName],
                        callback: function (Builder $query, $date) use (&$column): Builder {
                            /**
                             * @var BuilderImplementation $query
                             */
                            return $query->whereDate($column, '>=', $date);
                        },
                    )
                    ->when(
                        value: $data[$toFilterName],
                        callback: function (Builder $query, $date) use (&$column): Builder {
                            /**
                             * @var BuilderImplementation $query
                             */
                            return $query->whereDate($column, '<=', $date);
                        },
                    );
            })
            ->indicateUsing(function (array $data) use (&$fromFilterName, &$toFilterName, &$fromIndicatorName, &$toIndicatorName): array {
                $indicators = [];

                if ($data[$fromFilterName] ?? null) {
                    $indicators[] = Indicator::make("{$fromIndicatorName}: " . Carbon::parse($data[$fromFilterName])->toFormattedDateString())
                        ->removeField($fromFilterName);
                }

                if ($data[$toFilterName] ?? null) {
                    $indicators[] = Indicator::make("{$toIndicatorName}: " . Carbon::parse($data[$toFilterName])->toFormattedDateString())
                        ->removeField($toFilterName);
                }

                return $indicators;
            });
    }
}
