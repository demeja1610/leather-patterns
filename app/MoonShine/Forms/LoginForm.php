<?php

declare(strict_types=1);

namespace App\MoonShine\Forms;

use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Fields\Switcher;
use MoonShine\Support\Traits\Makeable;
use MoonShine\Contracts\UI\FormContract;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\Contracts\UI\FormBuilderContract;

final class LoginForm implements FormContract
{
    use Makeable;

    public function __invoke(): FormBuilderContract
    {
        return FormBuilder::make()
            ->class('authentication-form')
            ->action(moonshineRouter()->to('authenticate'))
            ->fields([
                Text::make(__('moonshine::ui.login.username'), 'username')
                    ->required()
                    ->customAttributes([
                        'autofocus' => true,
                        'autocomplete' => 'username',
                    ]),

                Password::make(__('moonshine::ui.login.password'), 'password')
                    ->required(),

                Switcher::make(__('moonshine::ui.login.remember_me'), 'remember'),
            ])
            ->submit(__('moonshine::ui.login.login'), [
                'class' => 'btn-primary btn-lg w-full',
            ]);
    }
}
