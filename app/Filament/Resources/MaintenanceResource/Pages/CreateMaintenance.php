<?php

namespace App\Filament\Resources\MaintenanceResource\Pages;

use App\Filament\Resources\MaintenanceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMaintenance extends CreateRecord
{
    protected static string $resource = MaintenanceResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['reported_by'] = auth()->id();
        $data['reported_at'] = now();
        return $data;
    }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
