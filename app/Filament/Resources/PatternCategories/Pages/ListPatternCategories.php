<?php

namespace App\Filament\Resources\PatternCategories\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\PatternCategories\PatternCategoryResource;

class ListPatternCategories extends ListRecords
{
    protected static string $resource = PatternCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
