<?php

namespace App\Filament\Resources\PatternTags\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\PatternTags\PatternTagResource;

class ListPatternTags extends ListRecords
{
    protected static string $resource = PatternTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
