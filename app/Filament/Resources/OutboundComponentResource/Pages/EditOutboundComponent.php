<?php

namespace App\Filament\Resources\OutboundComponentResource\Pages;

use App\Filament\Resources\OutboundComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOutboundComponent extends EditRecord
{
    protected static string $resource = OutboundComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
