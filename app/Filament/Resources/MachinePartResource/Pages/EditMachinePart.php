<?php

namespace App\Filament\Resources\MachinePartResource\Pages;

use App\Filament\Resources\MachinePartResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMachinePart extends EditRecord
{
    protected static string $resource = MachinePartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
