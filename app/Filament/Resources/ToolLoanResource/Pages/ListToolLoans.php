<?php

namespace App\Filament\Resources\ToolLoanResource\Pages;

use App\Filament\Resources\ToolLoanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListToolLoans extends ListRecords
{
    protected static string $resource = ToolLoanResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(\App\Filament\Exports\ToolLoanExporter::class)
                ->label('Export'),
            Actions\ImportAction::make()
                ->importer(\App\Filament\Imports\ToolLoanImporter::class)
                ->label('Import'),
            Actions\CreateAction::make()->label('+ Pinjam Alat')
        ];
    }
}
