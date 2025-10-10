<?php

namespace App\Filament\Resources\PatternCategories\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PatternCategories\PatternCategoryResource;

class EditPatternCategory extends EditRecord
{
    protected static string $resource = PatternCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
