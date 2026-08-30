<?php

namespace App\Filament\Resources\MachineOperationRecordResource\Pages;

use App\Filament\Resources\MachineOperationRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMachineOperationRecord extends EditRecord
{
    protected static string $resource = MachineOperationRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
