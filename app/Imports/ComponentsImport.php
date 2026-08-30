<?php

namespace App\Imports;

use App\Models\Component;
use App\Models\ComponentCategory;
use App\Models\Mold;
use App\Models\MaterialType;
use App\Models\MachiningType;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class ComponentsImport implements OnEachRow, WithHeadingRow, WithEvents
{
    public int $skippedCount = 0;
    public int $updatedCount = 0;

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                if ($this->skippedCount > 0 || $this->updatedCount > 0) {
                    $body = [];
                    if ($this->updatedCount > 0) {
                        $body[] = "{$this->updatedCount} item diperbarui datanya.";
                    }
                    if ($this->skippedCount > 0) {
                        $body[] = "{$this->skippedCount} item dilewati (sudah ada & sama persis).";
                    }

                    Notification::make()
                        ->success()
                        ->title('Import Selesai')
                        ->body(implode(' ', $body))
                        ->send();
                }
            },
        ];
    }
    public function onRow(Row $rowObj)
    {
        $row = $rowObj->toArray();
        // 1. Get/Create Bagian (Category) - using it as name too since nama_komponen is removed
        $categoryName = trim($row['bagian'] ?? '');
        $category = null;
        if (!empty($categoryName)) {
            $category = ComponentCategory::firstOrCreate(
                ['name' => $categoryName]
            );
        }

        // 2. Get/Create Project
        $projectName = trim($row['nama_project'] ?? '');
        $project = null;
        if (!empty($projectName)) {
            $project = Project::firstOrCreate(
                ['name' => $projectName],
                ['code' => 'PRJ-' . strtoupper(Str::random(4)), 'status' => 'active']
            );
        }

        // 3. Get/Create Mold
        $moldCode = trim($row['nomor_mold'] ?? '');
        $moldName = trim($row['nama_mold'] ?? ('Mold ' . $moldCode));
        $mold = null;
        if (!empty($moldCode)) {
            $mold = Mold::where('mold_number', $moldCode)
                ->orWhere('code', $moldCode)
                ->first();
                
            if (!$mold) {
                $mold = Mold::create([
                    'code' => (string)$moldCode,
                    'name' => $moldName ?: 'Mold ' . $moldCode,
                    'mold_number' => (string)$moldCode,
                    'project_id' => $project?->id,
                    'status' => 'active'
                ]);
            }
        }

        // 4. Get/Create Material Type
        $materialName = trim($row['material'] ?? '');
        $materialType = null;
        if (!empty($materialName)) {
            $materialType = MaterialType::firstOrCreate(
                ['name' => $materialName]
            );
        }

        // 5. Get/Create Machining Type
        $machiningName = trim($row['machining'] ?? '');
        $machiningType = null;
        if (!empty($machiningName)) {
            $machiningType = MachiningType::firstOrCreate(
                ['name' => $machiningName]
            );
        }

        $statusStr = strtolower(trim($row['status'] ?? 'ready'));
        $status = match(true) {
            str_contains($statusStr, 'dipakai') => 'in_use',
            str_contains($statusStr, 'belum datang') => 'pending_arrival',
            str_contains($statusStr, 'maintenance') => 'maintenance',
            str_contains($statusStr, 'pensiunkan') => 'retired',
            default => 'ready',
        };

        $code = trim($row['kode_qr'] ?? ($row['kode'] ?? 'COMP-' . strtoupper(Str::random(6))));

        $rawPrice = (string)($row['harga_pcs'] ?? ($row['hargapcs'] ?? ($row['harga'] ?? '0')));
        $rawPriceParts = explode(',', $rawPrice);
        $cleanPrice = preg_replace('/[^0-9]/', '', $rawPriceParts[0]);
        $unitPrice = (float)($cleanPrice ?: 0);

        $data = [
            'name' => trim($row['bagian'] ?? ($row['nama_komponen'] ?? 'Unknown Component')),
            'category_id' => $category?->id,
            'mold_id' => $mold?->id,
            'material_type_id' => $materialType?->id,
            'machining_type_id' => $machiningType?->id,
            'material' => $materialName,
            'size_spec' => trim($row['spesifikasi_ukuran'] ?? ($row['spek'] ?? ($row['spesifikasi'] ?? ($row['ukuran'] ?? '')))),
            'rack_location' => trim($row['lokasi_rak'] ?? ''),
            'stock' => (int)($row['jumlah_masuk'] ?? ($row['stok'] ?? 0)),
            'required_qty' => (int)($row['kebutuhan'] ?? 0),
            'unit_price' => $unitPrice,
            'status' => $status,
            'photo' => trim($row['foto'] ?? ''),
        ];

        $terpakai = (int)($row['terpakai'] ?? 0);

        // Check if it already exists in DB
        $existingComponent = Component::withTrashed()->where('code', $code)->first();
        if ($existingComponent) {
            if ($existingComponent->trashed()) {
                $existingComponent->restore();
            }
            
            $currentTaken = $existingComponent->taken_qty;
            $existingComponent->fill($data);
            
            $isDirty = $existingComponent->isDirty();
            if ($isDirty) {
                $existingComponent->save();
            }

            if ($terpakai > $currentTaken) {
                $diff = $terpakai - $currentTaken;
                \Illuminate\Support\Facades\DB::table('stock_movements')->insert([
                    'reference_number' => 'IMP-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
                    'component_id' => $existingComponent->id,
                    'type' => 'out',
                    'quantity' => $diff,
                    'quantity_before' => $existingComponent->stock,
                    'quantity_after' => $existingComponent->stock,
                    'status' => 'approved',
                    'purpose' => 'Imported Initial Used Data',
                    'requested_by' => auth()->id() ?? 1,
                    'approved_by' => auth()->id() ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'approved_at' => now(),
                ]);
                if (!$isDirty) {
                    $this->updatedCount++;
                }
            }
            
            if ($isDirty && $terpakai <= $currentTaken) {
                $this->updatedCount++;
            } elseif (!$isDirty && $terpakai <= $currentTaken) {
                $this->skippedCount++;
            }
            
            return; 
        }

        try {
            $newComp = Component::create(array_merge(['code' => $code], $data));
            if ($terpakai > 0) {
                \Illuminate\Support\Facades\DB::table('stock_movements')->insert([
                    'reference_number' => 'IMP-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
                    'component_id' => $newComp->id,
                    'type' => 'out',
                    'quantity' => $terpakai,
                    'quantity_before' => $newComp->stock,
                    'quantity_after' => $newComp->stock,
                    'status' => 'approved',
                    'purpose' => 'Imported Initial Used Data',
                    'requested_by' => auth()->id() ?? 1,
                    'approved_by' => auth()->id() ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'approved_at' => now(),
                ]);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // Catch 1062 duplicate entry just in case of race condition or cross-chunk leak
            if ($e->errorInfo[1] == 1062) {
                $this->skippedCount++;
                return;
            }
            throw $e;
        }
    }
}
