<?php

namespace App\Filament\Resources\InboundComponentResource\Pages;

use App\Filament\Resources\InboundComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInboundComponent extends EditRecord
{
    protected static string $resource = InboundComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
