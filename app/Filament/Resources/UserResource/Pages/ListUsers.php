<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Exports\UserExporter;
use App\Filament\Traits\HasExcelImport;
use App\Imports\UsersImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    use HasExcelImport;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->makeImportAction('Import User', UsersImport::class, 'Import User'),
            Actions\ExportAction::make()
                ->exporter(UserExporter::class)
                ->label('Export User'),
            Actions\CreateAction::make()->label('+ Tambah User'),
        ];
    }
}
