<?php

namespace App\Filament\Resources\SagyoNippoResource\Pages;

use App\Filament\Resources\SagyoNippoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSagyoNippo extends EditRecord
{
    protected static string $resource = SagyoNippoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
