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

    public function getTabs(): array
    {
        return [
            'active' => \Filament\Resources\Components\Tab::make('Active Jobs')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', '!=', 'completed')),
            'completed' => \Filament\Resources\Components\Tab::make('Laporan (Completed)')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'completed')),
        ];
    }
}
