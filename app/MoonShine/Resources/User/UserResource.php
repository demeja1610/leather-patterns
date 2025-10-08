<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\User;

use App\Models\User;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\PasswordRepeat;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Resources\ModelResource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Support\ListOf;

/**
 * @extends ModelResource<User>
 */
class UserResource extends ModelResource
{
    protected string $model = User::class;

    protected string $title = 'Users';

    protected string $column = 'email';

    protected ?string $alias = 'users';

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
            Email::make('Email', 'email')->sortable(),
            Date::make('Created At', 'created_at')->sortable(),
            Text::make('Review Count', 'reviews_count')->sortable(),
        ];
    }

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        /**
         * @var \Illuminate\Database\Eloquent\Builder $builder
         */
        return $builder->withCount('reviews');
    }

    protected function filters(): iterable
    {
        return [
            Text::make('Name', 'name'),
            Email::make('Email', 'email'),
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
                Email::make('Email', 'email')->required(),
                Password::make('Password', 'password'),
                PasswordRepeat::make('Repeat Password', 'password_confirmation')
            ])
        ];
    }

    /**
     * @param User $item
     *
     * @return array<string, string[]|string>
     */
    protected function rules(mixed $item): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'min:2'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $item->id],
            'password' => $item->exists
                ? ['sometimes', 'nullable', 'string', 'min:8', 'required_with:password_confirmation', 'same:password_confirmation']
                : ['required', 'string', 'min:8', 'required_with:password_confirmation', 'same:password_confirmation'],
        ];
    }
}
