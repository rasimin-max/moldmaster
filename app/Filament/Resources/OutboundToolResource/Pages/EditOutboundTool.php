<?php

namespace App\Filament\Resources\OutboundToolResource\Pages;

use App\Filament\Resources\OutboundToolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOutboundTool extends EditRecord
{
    protected static string $resource = OutboundToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
