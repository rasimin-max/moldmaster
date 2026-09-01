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
        if (!empty($row['project_code'])) {
            $project = Project::where('code', $row['project_code'])->first();
            if ($project) {
                $projectId = $project->id;
            }
        }

        return new Mold([
            'mold_number'  => $row['mold_number'] ?? null,
            'project_id'   => $projectId,
            'code'         => $row['code'] ?? null,
            'name'         => $row['name'] ?? null,
            'project_name' => $row['project_name'] ?? null,
            'customer'     => $row['customer'] ?? null,
            'product_type' => $row['product_type'] ?? null,
            'cavity'       => $row['cavity'] ?? null,
            'shot_life'    => $row['shot_life'] ?? 0,
            'current_shot' => $row['current_shot'] ?? 0,
            'status'       => $row['status'] ?? 'active',
            'description'  => $row['description'] ?? null,
        ]);
    }
}
