<?php

namespace App\Filament\Traits;

use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

trait HasExcelImport
{
    protected function makeImportAction(string $label, string $importerClass, string $modalTitle = 'Import Excel'): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('import')
            ->label($label)
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->form([
                FileUpload::make('file')
                    ->label($modalTitle . ' Data')
                    ->disk('local')
                    ->directory('imports-temp')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                    ])
                    ->required(),
            ])
            ->action(function (array $data) use ($importerClass) {
                \Illuminate\Support\Facades\Log::info('Import action started. Data: ', $data);
                $relativePath = $data['file'] ?? null;
                if (!$relativePath) {
                    \Illuminate\Support\Facades\Log::error('No file found in data array');
                    Notification::make()->title('Error')->body('File kosong!')->danger()->send();
                    return;
                }

                // Try multiple possible paths
                $possiblePaths = [
                    storage_path('app/' . $relativePath),
                    storage_path('app/local/' . $relativePath),
                    storage_path('app/private/' . $relativePath),
                ];
                $fullPath = null;
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        $fullPath = $path;
                        break;
                    }
                }

                if (!$fullPath) {
                    Notification::make()
                        ->title('File Tidak Ditemukan')
                        ->body('Tidak dapat menemukan file yang diupload. Coba lagi.')
                        ->danger()
                        ->send();
                    return;
                }

                try {
                    $importer = new $importerClass();
                    Excel::import($importer, $fullPath);

                    $imported = $importer->importedCount ?? ($importer->createdCount ?? 0);
                    $updated  = $importer->updatedCount ?? 0;
                    $skipped  = $importer->skippedCount ?? 0;
                    $failed   = $importer->failedCount ?? 0;

                    Notification::make()
                        ->title('Import Selesai')
                        ->body("Berhasil (Baru): {$imported} | Diperbarui: {$updated} | Dilewati: {$skipped} | Gagal (Error Data): {$failed}")
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Excel Import Error: ' . $e->getMessage());
                    Notification::make()
                        ->title('Gagal Membaca File Excel')
                        ->body('Pesan Error: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }

                // Clean up temp file
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            })
            ->modalHeading($modalTitle)
            ->modalDescription('Import data ke database dari file Excel (.xlsx)')
            ->modalSubmitActionLabel('Submit');
    }
}
