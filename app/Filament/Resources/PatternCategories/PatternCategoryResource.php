<?php

namespace App\Filament\Resources\PatternCategories;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\PatternCategory;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\PatternCategories\Pages\EditPatternCategory;
use App\Filament\Resources\PatternCategories\Pages\CreatePatternCategory;
use App\Filament\Resources\PatternCategories\Pages\ListPatternCategories;
use App\Filament\Resources\PatternCategories\Schemas\PatternCategoryForm;
use App\Filament\Resources\PatternCategories\Tables\PatternCategoriesTable;

class PatternCategoryResource extends Resource
{
    protected static ?string $model = PatternCategory::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Category'; // Also can use getModelLabel() method

    protected static ?string $pluralModelLabel = 'Categories'; // Also can use getPluralModelLabel() method

    protected static ?string $navigationLabel = 'Categories'; // Also can use getNavigationLabel() method

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet; // Also can use getNavigationIcon() method

    protected static ?int $navigationSort = 10; // Also can use getNavigationSort() method

    public static function form(Schema $schema): Schema
    {
        return PatternCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PatternCategoriesTable::configure($table);
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
            'index' => ListPatternCategories::route('/'),
            'create' => CreatePatternCategory::route('/create'),
            'edit' => EditPatternCategory::route('/{record}/edit'),
        ];
    }
}
