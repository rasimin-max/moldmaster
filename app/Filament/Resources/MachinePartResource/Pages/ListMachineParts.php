<?php

namespace App\Filament\Resources\MachinePartResource\Pages;

use App\Filament\Resources\MachinePartResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMachineParts extends ListRecords
{
    protected static string $resource = MachinePartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
