<?php

namespace App\Filament\Resources\PatternCategories\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;

class PatternCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->unique(ignoreRecord: true)
            ]);
    }
}
