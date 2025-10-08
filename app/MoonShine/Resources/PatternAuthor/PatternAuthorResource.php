<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\PatternAuthor;

use MoonShine\UI\Fields\ID;
use App\Models\PatternAuthor;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Text;
use MoonShine\Support\Enums\Color;
use MoonShine\Laravel\Enums\Action;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\UI\ActionButtonContract;
use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * @extends ModelResource<PatternAuthor>
 */
class PatternAuthorResource extends ModelResource
{
    protected string $model = PatternAuthor::class;

    protected string $title = 'Pattern Authors';

    protected string $column = 'name';

    protected ?string $alias = 'pattern_authors';

     protected function activeActions(): ListOf
    {
        return parent::activeActions()
            ->except(
                Action::VIEW
            );
    }


    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),

            Text::make('Name', 'name')->sortable(),

            Text::make('Patterns Count', 'patterns_count')->sortable()->badge(color: Color::INFO),

            Date::make('Created At', 'created_at')->sortable(),
        ];
    }

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        /**
         * @var \Illuminate\Database\Eloquent\Builder $builder
         */
        return $builder->withCount('patterns');
    }

    // protected function modifyDeleteButton(ActionButtonContract $button): ActionButtonContract
    // {
    //     return $button->canSee(fn(PatternAuthor $item) => $item->patterns_count === 0);
    // }

    protected function filters(): iterable
    {
        return [
            Text::make('Name', 'name'),
            Date::make('Created At', 'created_at'),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make(),

                Text::make('Name', 'name')->required(),
            ])
        ];
    }

    /**
     * @param PatternTag $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'min:2', 'unique:pattern_tags,name,' . $item->id],
        ];
    }
}
