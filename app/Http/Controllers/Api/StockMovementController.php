<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Component;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StockMovementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = StockMovement::with(['component', 'requester', 'approver', 'mold', 'machine'])
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status));

        // Operator only sees their own movements
        if ($user->hasRole('operator')) {
            $query->where('requested_by', $user->id);
        }

        $movements = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $movements->map(fn($m) => [
                'id' => $m->id,
                'reference_number' => $m->reference_number,
                'type' => $m->type,
                'type_label' => $m->type_label,
                'status' => $m->status,
                'quantity' => $m->quantity,
                'quantity_before' => $m->quantity_before,
                'quantity_after' => $m->quantity_after,
                'component' => $m->component ? ['id' => $m->component->id, 'code' => $m->component->code, 'name' => $m->component->name] : null,
                'mold' => $m->mold ? ['code' => $m->mold->code, 'name' => $m->mold->name] : null,
                'machine' => $m->machine ? ['code' => $m->machine->code, 'name' => $m->machine->name] : null,
                'purpose' => $m->purpose,
                'condition' => $m->condition,
                'notes' => $m->notes,
                'rejection_reason' => $m->rejection_reason,
                'requester' => $m->requester ? ['name' => $m->requester->name] : null,
                'approver' => $m->approver ? ['name' => $m->approver->name] : null,
                'photo' => $m->photo ? asset('storage/' . $m->photo) : null,
                'approved_at' => $m->approved_at,
                'created_at' => $m->created_at,
            ]),
            'meta' => ['total' => $movements->total(), 'current_page' => $movements->currentPage(), 'last_page' => $movements->lastPage()],
        ]);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $movement = StockMovement::with(['component', 'requester', 'approver', 'mold', 'machine'])->findOrFail($id);
        return response()->json($movement);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'component_id' => 'required|exists:components,id',
            'type' => 'required|in:out,return,in',
            'quantity' => 'required|integer|min:1',
            'mold_id' => 'nullable|exists:molds,id',
            'machine_id' => 'nullable|exists:machines,id',
            'purpose' => 'nullable|string|max:255',
            'operator_name' => 'nullable|string|max:100',
            'condition' => 'nullable|in:good,damaged,needs_sharpening,needs_coating,lost',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:5120',
        ]);

        $validated['requested_by'] = $request->user()->id;

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('stock-movements', 'public');
        }

        // Validate stock for outgoing
        if ($validated['type'] === 'out') {
            $component = Component::findOrFail($validated['component_id']);
            if ($component->available_stock < $validated['quantity']) {
                return response()->json([
                    'message' => "Stok tidak mencukupi. Tersedia: {$component->available_stock}",
                ], 422);
            }
        }

        $movement = StockMovement::create($validated);

        AuditLog::log('created', "Transaksi {$movement->type_label} dibuat untuk {$movement->component->name}", StockMovement::class, $movement->id);

        return response()->json([
            'message' => 'Transaksi berhasil dibuat. Menunggu persetujuan leader.',
            'data' => ['id' => $movement->id, 'reference_number' => $movement->reference_number, 'status' => $movement->status],
        ], 201);
    }

    public function approve(int $id, Request $request): JsonResponse
    {
        $movement = StockMovement::findOrFail($id);

        if ($movement->status !== 'pending') {
            return response()->json(['message' => 'Transaksi ini tidak bisa disetujui.'], 422);
        }

        if (!$request->user()->can('approve stock movements')) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menyetujui transaksi.'], 403);
        }

        $movement->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
        ]);

        AuditLog::log('approved', "Transaksi {$movement->reference_number} disetujui", StockMovement::class, $movement->id);

        return response()->json(['message' => 'Transaksi berhasil disetujui.', 'data' => ['status' => 'approved']]);
    }

    public function reject(int $id, Request $request): JsonResponse
    {
        $request->validate(['rejection_reason' => 'required|string']);
        $movement = StockMovement::findOrFail($id);

        if ($movement->status !== 'pending') {
            return response()->json(['message' => 'Transaksi ini tidak bisa ditolak.'], 422);
        }

        $movement->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'rejection_reason' => $request->rejection_reason,
        ]);

        AuditLog::log('rejected', "Transaksi {$movement->reference_number} ditolak", StockMovement::class, $movement->id);

        return response()->json(['message' => 'Transaksi ditolak.']);
    }
}
