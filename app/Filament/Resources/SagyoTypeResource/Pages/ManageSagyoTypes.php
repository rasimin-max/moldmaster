<?php

namespace App\Filament\Resources\SagyoTypeResource\Pages;

use App\Filament\Resources\SagyoTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSagyoTypes extends ManageRecords
{
    protected static string $resource = SagyoTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
