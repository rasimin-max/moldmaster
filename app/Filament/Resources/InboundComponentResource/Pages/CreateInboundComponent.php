<?php

namespace App\Filament\Resources\InboundComponentResource\Pages;

use App\Filament\Resources\InboundComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInboundComponent extends CreateRecord
{
    protected static string $resource = InboundComponentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_by'] = auth()->id() ?? 1;
        if (!isset($data['status'])) {
            $data['status'] = 'approved';
        }
        return $data;
    }
}
