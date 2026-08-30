<?php

namespace App\Filament\Resources\OutboundComponentResource\Pages;

use App\Filament\Resources\OutboundComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOutboundComponent extends CreateRecord
{
    protected static string $resource = OutboundComponentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_by'] = auth()->id() ?? 1;
        if (!isset($data['status'])) {
            $data['status'] = 'approved';
        }
        return $data;
    }
}
