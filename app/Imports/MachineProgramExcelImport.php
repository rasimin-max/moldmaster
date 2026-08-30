<?php

namespace App\Imports;

use App\Models\MachineProgram;
use App\Models\Project;
use App\Models\Mold;
use App\Models\Component;
use App\Models\Machine;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MachineProgramExcelImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Ignore empty rows without program name
        if (empty($row['program_name'])) {
            return null;
        }

        // Try to find IDs for relations if names are provided
        $projectId = isset($row['project']) && trim($row['project']) !== '' ? Project::where('name', 'LIKE', trim($row['project']))->value('id') : null;
        $moldId = isset($row['mold_name']) && trim($row['mold_name']) !== '' ? Mold::where('name', 'LIKE', trim($row['mold_name']))->value('id') : null;
        $componentId = isset($row['component_name']) && trim($row['component_name']) !== '' ? Component::where('name', 'LIKE', trim($row['component_name']))->value('id') : null;
        $machineId = isset($row['machine']) && trim($row['machine']) !== '' ? Machine::where('name', 'LIKE', trim($row['machine']))->value('id') : null;

        $barcode = isset($row['barcode']) ? trim((string)$row['barcode']) : null;

        $data = [
            'project_id'        => $projectId,
            'mold_id'           => $moldId,
            'component_id'      => $componentId,
            'machine_id'        => $machineId,
            'programmer'        => $row['programmer'] ?? null,
            'name'              => $row['program_name'],
            'r_f'               => $row['rf'] ?? null,
            'b'                 => $row['b'] ?? null,
            'tool_no'           => $row['tool_no'] ?? null,
            'tool_name'         => $row['tool_name'] ?? null,
            'tool_dia'          => $row['tool_dia'] ?? null,
            'tool_r'            => $row['tool_r'] ?? null,
            'tool_length_total' => $row['length_total'] ?? null,
            'tool_length_eff'   => $row['length_eff'] ?? null,
            'tool_num'          => $row['tool_num'] ?? null,
            'holder'            => $row['holder'] ?? null,
            'ps_thick'          => $row['ps_thick'] ?? null,
            'rpm'               => $row['rpm'] ?? null,
            'feed'              => $row['feed'] ?? null,
            'doc'               => $row['doc'] ?? null,
            'setting'           => $row['setting'] ?? null,
            'estimated_time'    => $row['process_time_plan'] ?? null,
            'actual_time'       => $row['process_time_actual'] ?? null,
            'barcode'           => $barcode,
            'description'       => $row['description'] ?? null,
        ];

        $program = null;

        if (!empty($barcode)) {
            $program = MachineProgram::where('barcode', $barcode)->first();
        }

        if (!$program && !empty($row['program_name'])) {
            $program = MachineProgram::where('name', $row['program_name'])->first();
        }

        if ($program) {
            $program->fill($data);
            return $program;
        }

        return new MachineProgram($data);
    }
}
