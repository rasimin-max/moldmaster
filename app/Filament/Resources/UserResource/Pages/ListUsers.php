<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Exports\UserExporter;
use App\Filament\Imports\UserImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
    protected function getHeaderActions(): array {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->use(\App\Imports\UsersImport::class)
                ->color('primary')
                ->label('Import User'),
            Actions\ExportAction::make()
                ->exporter(UserExporter::class)
                ->label('Export User'),
            Actions\CreateAction::make()->label('+ Tambah User')
        ];
    }
}
