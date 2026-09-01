<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Imports\ProjectsImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make()
                ->exports([
                    \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                        ->fromTable()
                        ->withNamesAsHeadings()
                        ->ignoreFormatting()
                        ->withFilename('Template-Project-' . date('Y-m-d')),
                ])
                ->label('Export / Template')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray'),
            Actions\Action::make('import')
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    FileUpload::make('file')
                        ->label('Projects Excel Data')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $file = $data['file'];
                    if ($file instanceof TemporaryUploadedFile) {
                        $path = $file->getRealPath();
                    } else {
                        $path = storage_path('app/public/' . $file);
                    }
                    $importer = new ProjectsImport();
                    Excel::import($importer, $path);
                    Notification::make()
                        ->title('Import Selesai')
                        ->body("Berhasil: {$importer->importedCount} | Diperbarui: {$importer->updatedCount} | Dilewati: {$importer->skippedCount}")
                        ->success()
                        ->send();
                })
                ->modalHeading('Import Excel')
                ->modalDescription('Import data into database from Excel file')
                ->modalSubmitActionLabel('Submit'),
            Actions\CreateAction::make(),
        ];
    }
}
