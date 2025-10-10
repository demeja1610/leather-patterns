<?php

namespace App\Filament\Resources\PatternAuthors\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PatternAuthors\PatternAuthorResource;

class CreatePatternAuthor extends CreateRecord
{
    protected static string $resource = PatternAuthorResource::class;
}
