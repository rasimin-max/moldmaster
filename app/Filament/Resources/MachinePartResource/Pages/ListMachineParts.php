<?php

namespace App\Filament\Resources\MachinePartResource\Pages;

use App\Filament\Resources\MachinePartResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMachineParts extends ListRecords
{
    protected static string $resource = MachinePartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make()
                ->exports([
                    \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                        ->fromTable()
                        ->ignoreFormatting()
                        ->withFilename('Template-Machine-Part-' . date('Y-m-d')),
                ])
                ->label('Export / Template')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray'),
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color('primary')
                ->processCollectionUsing(function (string $modelClass, \Illuminate\Support\Collection $collection) {
                    foreach ($collection as $index => $row) {
                        $originalData = $row->toArray();
                        
                        // Normalize keys
                        $data = [];
                        $hasData = false;
                        foreach ($originalData as $k => $v) {
                            if ($v !== null && $v !== '') {
                                $hasData = true;
                            }
                            $nk = str_replace([' ', '.'], '_', strtolower(trim((string)$k)));
                            $data[$nk] = $v;
                        }
                        
                        if (!$hasData) {
                            continue; // Skip empty rows
                        }
                        
                        $machineId = $data['machine_id'] ?? null;
                        
                        // Try to find machine by name or code if machine_id is not provided
                        if (empty($machineId)) {
                            $machineRef = $data['machine'] ?? $data['machine_name'] ?? $data['machinename'] ?? $data['mesin'] ?? $data['nama_mesin'] ?? null;
                            if ($machineRef) {
                                $machineRef = trim((string)$machineRef);
                                $machine = \App\Models\Machine::where('name', 'like', "%{$machineRef}%")
                                    ->orWhere('code', $machineRef)
                                    ->first();
                                if ($machine) {
                                    $machineId = $machine->id;
                                } else {
                                    throw new \Exception("Gagal Import: Mesin dengan nama/kode '{$machineRef}' tidak ditemukan di database.");
                                }
                            } else {
                                $headers = implode(', ', array_keys($data));
                                throw new \Exception("Gagal Import: Kolom 'Machine' (mesin) tidak ditemukan atau kosong. Kolom yang terbaca dari Excel Anda: " . $headers);
                            }
                        }
                        
                        $name = $data['name'] ?? $data['nama'] ?? $data['nama_part'] ?? null;
                        
                        if (empty($name)) {
                            throw new \Exception("Gagal Import: Kolom 'Name' atau 'Nama Part' tidak ditemukan atau kosong.");
                        }

                        $data['machine_id'] = $machineId;
                        $data['name'] = trim((string)$name);
                        
                        // Sanitize empty strings for integer and date columns to prevent Postgres cast errors
                        foreach (['expected_life_hours', 'expected_life_cycles', 'installed_at', 'part_number'] as $field) {
                            if (isset($data[$field]) && trim((string)$data[$field]) === '') {
                                $data[$field] = null;
                            }
                        }
                        
                        // Default installed_at if still null or not set
                        if (empty($data['installed_at'])) {
                            $data['installed_at'] = now();
                        }
                        
                        // Default is_active
                        if (isset($data['is_active']) && trim((string)$data['is_active']) === '') {
                            unset($data['is_active']); // Let database or model default handle it, or we could set to true
                        }
                        
                        $modelClass::updateOrCreate(
                            [
                                'machine_id' => $machineId,
                                'name' => $name
                            ],
                            $data
                        );
                    }
                    return $collection;
                }),
            Actions\CreateAction::make(),
        ];
    }
}
