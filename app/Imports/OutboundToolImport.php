<?php

namespace App\Imports;

use App\Models\Tool;
use App\Models\ToolMovement;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OutboundToolImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $rowObj)
    {
        $row = $rowObj->toArray();
        $code = trim($row['kode_alat'] ?? '');
        $quantity = (int)($row['jumlah'] ?? 0);

        if (!$code || $quantity <= 0) return;

        $item = Tool::where('code', $code)->first();
        if ($item) {
            ToolMovement::create([
                'tool_id' => $item->id,
                'type' => 'out',
                'quantity' => $quantity,
                'operator_name' => $row['nama_operator'] ?? null,
                'purpose' => $row['tujuan'] ?? null,
                'notes' => $row['catatan'] ?? null,
                'status' => 'approved',
                'requested_by' => auth()->id() ?? 1,
            ]);
        }
    }
}