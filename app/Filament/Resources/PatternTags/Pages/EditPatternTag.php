<?php

namespace App\Filament\Resources\PatternTags\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PatternTags\PatternTagResource;

class EditPatternTag extends EditRecord
{
    protected static string $resource = PatternTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
