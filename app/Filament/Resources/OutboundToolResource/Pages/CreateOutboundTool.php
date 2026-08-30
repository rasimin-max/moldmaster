<?php

namespace App\Filament\Resources\OutboundToolResource\Pages;

use App\Filament\Resources\OutboundToolResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOutboundTool extends CreateRecord
{
    protected static string $resource = OutboundToolResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_by'] = auth()->id() ?? 1;
        if (!isset($data['status'])) {
            $data['status'] = 'approved';
        }
        return $data;
    }
}
