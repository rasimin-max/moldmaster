<?php

namespace App\Filament\Resources\ToolLoanResource\Pages;

use App\Filament\Resources\ToolLoanResource;
use App\Filament\Traits\HasExcelImport;
use App\Imports\ToolLoansImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListToolLoans extends ListRecords
{
    use HasExcelImport;

    protected static string $resource = ToolLoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(\App\Filament\Exports\ToolLoanExporter::class)
                ->label('Export'),
            $this->makeImportAction('Import', ToolLoansImport::class, 'Import Pinjam Alat'),
            Actions\CreateAction::make()->label('+ Pinjam Alat'),
        ];
    }
}
