<?php

namespace App\Imports;

use App\Models\Component;
use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InboundComponentImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $rowObj)
    {
        $row = $rowObj->toArray();
        $code = trim($row['kode_komponen'] ?? '');
        $quantity = (int)($row['jumlah'] ?? 0);

        if (!$code || $quantity <= 0) return;

        $item = Component::where('code', $code)->first();
        if ($item) {
            StockMovement::create([
                'component_id' => $item->id,
                'type' => 'in',
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