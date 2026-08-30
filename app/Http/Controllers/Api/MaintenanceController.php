<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Maintenance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Maintenance::with(['machine', 'reporter', 'approver', 'technician'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->when($request->machine_id, fn($q) => $q->where('machine_id', $request->machine_id));

        if ($user->hasRole('operator')) {
            $query->where('reported_by', $user->id);
        }

        $items = $query->latest('reported_at')->paginate(20);

        return response()->json([
            'data' => $items->map(fn($m) => [
                'id' => $m->id,
                'work_order_number' => $m->work_order_number,
                'machine' => ['id' => $m->machine->id, 'code' => $m->machine->code, 'name' => $m->machine->name, 'type' => $m->machine->type],
                'type' => $m->type,
                'status' => $m->status,
                'priority' => $m->priority,
                'problem_description' => $m->problem_description,
                'action_taken' => $m->action_taken,
                'downtime_hours' => $m->downtime_hours,
                'total_cost' => $m->total_cost,
                'reporter' => ['name' => $m->reporter?->name],
                'approver' => ['name' => $m->approver?->name],
                'photo' => $m->photo ? asset('storage/' . $m->photo) : null,
                'reported_at' => $m->reported_at,
                'completed_at' => $m->completed_at,
            ]),
            'meta' => ['total' => $items->total(), 'current_page' => $items->currentPage(), 'last_page' => $items->lastPage()],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $m = Maintenance::with(['machine', 'reporter', 'approver', 'technician', 'spareParts'])->findOrFail($id);
        return response()->json($m);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'type' => 'required|in:preventive,corrective,breakdown,inspection',
            'priority' => 'required|in:urgent,high,medium,low',
            'problem_description' => 'required|string',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:5120',
        ]);

        $validated['reported_by'] = $request->user()->id;
        $validated['reported_at'] = now();

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('maintenances', 'public');
        }

        $maintenance = Maintenance::create($validated);

        AuditLog::log('created', "Laporan maintenance dibuat: {$maintenance->work_order_number}", Maintenance::class, $maintenance->id);

        return response()->json([
            'message' => 'Laporan maintenance berhasil dikirim.',
            'data' => ['id' => $maintenance->id, 'work_order_number' => $maintenance->work_order_number, 'status' => $maintenance->status],
        ], 201);
    }

    public function approve(int $id, Request $request): JsonResponse
    {
        $request->validate(['priority' => 'required|in:urgent,high,medium,low']);
        $maintenance = Maintenance::findOrFail($id);

        if (!$request->user()->can('approve maintenances')) {
            return response()->json(['message' => 'Tidak ada izin.'], 403);
        }

        $maintenance->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'priority' => $request->priority,
        ]);

        return response()->json(['message' => 'Maintenance disetujui.']);
    }

    public function complete(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'action_taken' => 'required|string',
            'downtime_hours' => 'required|numeric|min:0',
        ]);

        $maintenance = Maintenance::findOrFail($id);
        $maintenance->update([
            'status' => 'completed',
            'completed_at' => now(),
            'action_taken' => $request->action_taken,
            'downtime_hours' => $request->downtime_hours,
        ]);

        return response()->json(['message' => 'Maintenance selesai.']);
    }
}
