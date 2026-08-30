<?php

namespace App\Filament\Resources\MachineOperationRecordResource\Pages;

use App\Filament\Resources\MachineOperationRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMachineOperationRecords extends ListRecords
{
    protected static string $resource = MachineOperationRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color('primary'),
            Actions\CreateAction::make(),
        ];
    }
}
