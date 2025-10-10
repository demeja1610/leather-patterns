<?php

namespace App\Filament\Resources\PatternAuthors\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\PatternAuthors\PatternAuthorResource;

class ListPatternAuthors extends ListRecords
{
    protected static string $resource = PatternAuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
