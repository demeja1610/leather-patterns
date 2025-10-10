<?php

namespace App\Filament\Resources\PatternAuthors;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\PatternAuthor;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\PatternAuthors\Pages\EditPatternAuthor;
use App\Filament\Resources\PatternAuthors\Pages\ListPatternAuthors;
use App\Filament\Resources\PatternAuthors\Pages\CreatePatternAuthor;
use App\Filament\Resources\PatternAuthors\Schemas\PatternAuthorForm;
use App\Filament\Resources\PatternAuthors\Tables\PatternAuthorsTable;

class PatternAuthorResource extends Resource
{
    protected static ?string $model = PatternAuthor::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Author'; // Also can use getModelLabel() method

    protected static ?string $pluralModelLabel = 'Authors'; // Also can use getPluralModelLabel() method

    protected static ?string $navigationLabel = 'Authors'; // Also can use getNavigationLabel() method

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup; // Also can use getNavigationIcon() method

    protected static ?int $navigationSort = 10; // Also can use getNavigationSort() method

    public static function form(Schema $schema): Schema
    {
        return PatternAuthorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PatternAuthorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPatternAuthors::route('/'),
            'create' => CreatePatternAuthor::route('/create'),
            'edit' => EditPatternAuthor::route('/{record}/edit'),
        ];
    }
}
