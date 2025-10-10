<?php

namespace App\Filament\Resources\PatternAuthors\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PatternAuthors\PatternAuthorResource;

class EditPatternAuthor extends EditRecord
{
    protected static string $resource = PatternAuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
