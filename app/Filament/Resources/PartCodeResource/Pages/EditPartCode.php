<?php

namespace App\Filament\Resources\PartCodeResource\Pages;

use App\Filament\Resources\PartCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPartCode extends EditRecord
{
    protected static string $resource = PartCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
