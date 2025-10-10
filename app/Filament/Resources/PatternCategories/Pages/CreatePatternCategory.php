<?php

namespace App\Filament\Resources\PatternCategories\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PatternCategories\PatternCategoryResource;

class CreatePatternCategory extends CreateRecord
{
    protected static string $resource = PatternCategoryResource::class;
}
