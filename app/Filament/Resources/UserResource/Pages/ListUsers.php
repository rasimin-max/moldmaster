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
            \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make('export_excel')
                ->label('Export User')
                ->exports([
                    \pxlrbt\FilamentExcel\Exports\ExcelExport::make('export')
                        ->withColumns([
                            \pxlrbt\FilamentExcel\Columns\Column::make('avatar')->heading('Avatar'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('employee_id')->heading('ID'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('name')->heading('Nama'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('email')->heading('Email'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('roles.name')->heading('Role'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('area')->heading('Area'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('is_active')->heading('Aktif'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('password')->heading('Password'),
                        ])
                ]),
            Actions\CreateAction::make()->label('+ Tambah User'),
        ];
    }
}
