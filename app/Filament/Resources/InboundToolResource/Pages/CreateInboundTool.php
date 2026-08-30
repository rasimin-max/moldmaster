<?php

namespace App\Filament\Resources\InboundToolResource\Pages;

use App\Filament\Resources\InboundToolResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInboundTool extends CreateRecord
{
    protected static string $resource = InboundToolResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_by'] = auth()->id() ?? 1;
        if (!isset($data['status'])) {
            $data['status'] = 'approved';
        }
        return $data;
    }
}
