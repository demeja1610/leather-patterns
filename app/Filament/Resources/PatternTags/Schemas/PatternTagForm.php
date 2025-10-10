<?php

namespace App\Filament\Resources\PatternTags\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;

class PatternTagForm
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
