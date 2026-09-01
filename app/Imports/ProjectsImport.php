<?php

namespace App\Imports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class ProjectsImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public int $importedCount = 0;
    public int $updatedCount = 0;
    public int $skippedCount = 0;

    public function model(array $row): ?Project
    {
        $code = trim($row['code'] ?? ($row['kode'] ?? null));

        if (empty($code)) {
            $this->skippedCount++;
            return null;
        }

        // Map status
        $statusRaw = strtolower(trim($row['status'] ?? 'active'));
        $status = match(true) {
            in_array($statusRaw, ['active', 'aktif']) => 'active',
            in_array($statusRaw, ['completed', 'selesai', 'complete']) => 'completed',
            in_array($statusRaw, ['on_hold', 'on hold', 'ditunda', 'tunda', 'hold']) => 'on_hold',
            in_array($statusRaw, ['cancelled', 'canceled', 'batal', 'dibatal']) => 'cancelled',
            default => 'active',
        };

        // Parse start_date
        $startDate = null;
        $startDateRaw = $row['start_date'] ?? null;
        if (!empty($startDateRaw)) {
            try {
                if (is_numeric($startDateRaw)) {
                    $startDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($startDateRaw)->format('Y-m-d');
                } else {
                    $startDate = Carbon::parse($startDateRaw)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $startDate = null;
            }
        }

        // Parse end_date
        $endDate = null;
        $endDateRaw = $row['end_date'] ?? ($row['end date'] ?? null);
        if (!empty($endDateRaw)) {
            try {
                if (is_numeric($endDateRaw)) {
                    $endDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($endDateRaw)->format('Y-m-d');
                } else {
                    $endDate = Carbon::parse($endDateRaw)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $endDate = null;
            }
        }

        $data = [
            'name' => trim($row['name'] ?? ($row['nama'] ?? null)),
            'customer' => trim($row['customer'] ?? ($row['pelanggan'] ?? null)),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'budget' => is_numeric($row['budget'] ?? null) ? (float)$row['budget'] : 0,
            'status' => $status,
            'description' => trim($row['description'] ?? ($row['deskripsi'] ?? null)),
        ];

        // Remove null values to avoid overwriting existing data with null
        $data = array_filter($data, fn($v) => $v !== null && $v !== '');

        $existing = Project::withTrashed()->where('code', $code)->first();
        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->fill($data);
            if ($existing->isDirty()) {
                $existing->save();
                $this->updatedCount++;
            } else {
                $this->skippedCount++;
            }
            return null;
        }

        $this->importedCount++;
        return new Project(array_merge(['code' => $code], $data));
    }
}
