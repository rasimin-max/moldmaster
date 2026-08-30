<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_by'] = auth()->id();
        return $data;
    }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
