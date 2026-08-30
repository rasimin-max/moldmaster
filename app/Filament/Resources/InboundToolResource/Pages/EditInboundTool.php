<?php

namespace App\Filament\Resources\InboundToolResource\Pages;

use App\Filament\Resources\InboundToolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInboundTool extends EditRecord
{
    protected static string $resource = InboundToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
