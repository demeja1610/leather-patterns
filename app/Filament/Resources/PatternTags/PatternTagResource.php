<?php

namespace App\Filament\Resources\PatternTags;

use BackedEnum;
use App\Models\PatternTag;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\PatternTags\Pages\EditPatternTag;
use App\Filament\Resources\PatternTags\Pages\ListPatternTags;
use App\Filament\Resources\PatternTags\Pages\CreatePatternTag;
use App\Filament\Resources\PatternTags\Schemas\PatternTagForm;
use App\Filament\Resources\PatternTags\Tables\PatternTagsTable;

class PatternTagResource extends Resource
{
    protected static ?string $model = PatternTag::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Tag'; // Also can use getModelLabel() method

    protected static ?string $pluralModelLabel = 'Tags'; // Also can use getPluralModelLabel() method

    protected static ?string $navigationLabel = 'Tags'; // Also can use getNavigationLabel() method

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag; // Also can use getNavigationIcon() method

    protected static ?int $navigationSort = 30; // Also can use getNavigationSort() method

    public static function form(Schema $schema): Schema
    {
        return PatternTagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PatternTagsTable::configure($table);
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
            'index' => ListPatternTags::route('/'),
            'create' => CreatePatternTag::route('/create'),
            'edit' => EditPatternTag::route('/{record}/edit'),
        ];
    }
}
