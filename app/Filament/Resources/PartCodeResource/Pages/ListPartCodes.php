<?php

namespace App\Filament\Resources\PartCodeResource\Pages;

use App\Filament\Resources\PartCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPartCodes extends ListRecords
{
    protected static string $resource = PartCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
