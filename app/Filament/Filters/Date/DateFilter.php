<?php

namespace App\Filament\Filters\Date;

use Carbon\Carbon;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Forms\Components\DatePicker;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as BuilderImplementation;

class DateFilter
{
    public static function make(string $column, string $filterName, string $indicatorName, string $operator = '>='): Filter
    {
        return Filter::make($column)
            ->schema([
                DatePicker::make($filterName),
            ])
            ->query(function (Builder $query, array $data) use (&$column, &$filterName, &$operator): Builder {
                /**
                 * @var BuilderImplementation $query
                 */
                return $query
                    ->when(
                        value: $data[$filterName],
                        callback: function (Builder $query, $date) use (&$column, &$operator): Builder {
                            /**
                             * @var BuilderImplementation $query
                             */
                            return $query->whereDate($column, $operator, $date);
                        },
                    );
            })
            ->indicateUsing(function (array $data) use (&$filterName, &$indicatorName): array {
                $indicators = [];

                if ($data[$filterName] ?? null) {
                    $indicators[] = Indicator::make("{$indicatorName}: " . Carbon::parse($data[$filterName])->toFormattedDateString())
                        ->removeField($filterName);
                }

                return $indicators;
            });
    }
}
