<?php

namespace App\Imports;

use App\Models\Mold;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MoldsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $projectId = null;
        $projectCode = trim($row['project_code'] ?? ($row['kode_project'] ?? ''));
        if (!empty($projectCode)) {
            $project = Project::where('code', $projectCode)->first();
            if ($project) {
                $projectId = $project->id;
            }
        }

        $code = trim($row['code'] ?? ($row['kode'] ?? ($row['kode_mold'] ?? '')));
        if (empty($code)) {
            $code = 'MOL-' . date('Y') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
        }
        
        $name = trim($row['name'] ?? ($row['nama_mold'] ?? ($row['nama'] ?? '')));
        if (empty($name)) {
            $name = 'Mold ' . $code;
        }

        $statusStr = strtolower(trim($row['status'] ?? 'active'));
        $status = match(true) {
            str_contains($statusStr, 'non') || str_contains($statusStr, 'inactive') => 'inactive',
            str_contains($statusStr, 'maintenance') || str_contains($statusStr, 'perawatan') || str_contains($statusStr, 'rusak') => 'maintenance',
            str_contains($statusStr, 'pensiun') || str_contains($statusStr, 'retired') => 'retired',
            default => 'active',
        };

        return new Mold([
            'mold_number'  => trim($row['mold_number'] ?? ($row['nomor_mold'] ?? ($row['no_mold'] ?? null))),
            'project_id'   => $projectId,
            'code'         => $code,
            'name'         => $name,
            'project_name' => trim($row['project_name'] ?? ($row['nama_project'] ?? null)),
            'customer'     => trim($row['customer'] ?? null),
            'product_type' => trim($row['product_type'] ?? ($row['produk'] ?? ($row['jenis_produk'] ?? null))),
            'cavity'       => (int) trim($row['cavity'] ?? ($row['kaviti'] ?? 1)),
            'shot_life'    => (int) trim($row['shot_life'] ?? ($row['target_shot'] ?? 0)),
            'current_shot' => (int) trim($row['current_shot'] ?? ($row['shot_saat_ini'] ?? ($row['shot'] ?? 0))),
            'status'       => $status,
            'description'  => trim($row['description'] ?? ($row['deskripsi'] ?? null)),
        ]);
    }
}
