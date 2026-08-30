<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Tool;
use App\Models\ToolLoan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ToolLoanController extends Controller
{
    public function listTools(Request $request): JsonResponse
    {
        $tools = Tool::where('available_quantity', '>', 0)->get();
        return response()->json(['data' => $tools->map(fn($t) => [
            'id' => $t->id, 'code' => $t->code, 'name' => $t->name,
            'category' => $t->category, 'available_quantity' => $t->available_quantity,
            'total_quantity' => $t->total_quantity, 'condition' => $t->condition, 'location' => $t->location,
        ])]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = ToolLoan::with(['tool', 'borrower', 'approver'])
            ->when($request->status, fn($q) => $q->where('status', $request->status));

        if ($user->hasRole('operator')) {
            $query->where('borrower_id', $user->id);
        }

        $loans = $query->latest()->paginate(20);

        return response()->json([
            'data' => $loans->map(fn($l) => [
                'id' => $l->id, 'loan_number' => $l->loan_number,
                'tool' => ['id' => $l->tool->id, 'name' => $l->tool->name, 'code' => $l->tool->code],
                'quantity' => $l->quantity, 'status' => $l->status,
                'purpose' => $l->purpose,
                'planned_return_date' => $l->planned_return_date,
                'borrowed_at' => $l->borrowed_at, 'returned_at' => $l->returned_at,
                'is_overdue' => $l->is_overdue,
                'borrower' => ['name' => $l->borrower?->name],
            ]),
            'meta' => ['total' => $loans->total()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tool_id' => 'required|exists:tools,id',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'nullable|string',
            'planned_return_date' => 'nullable|date|after:today',
        ]);

        $tool = Tool::findOrFail($validated['tool_id']);
        if ($tool->available_quantity < $validated['quantity']) {
            return response()->json(['message' => "Alat tidak tersedia. Tersedia: {$tool->available_quantity}"], 422);
        }

        $validated['borrower_id'] = $request->user()->id;
        $loan = ToolLoan::create($validated);

        AuditLog::log('created', "Request pinjam alat: {$loan->loan_number}", ToolLoan::class, $loan->id);

        return response()->json([
            'message' => 'Request peminjaman dikirim. Menunggu persetujuan.',
            'data' => ['id' => $loan->id, 'loan_number' => $loan->loan_number, 'status' => $loan->status],
        ], 201);
    }

    public function returnTool(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'condition_returned' => 'required|in:good,fair,poor,damaged',
            'notes' => 'nullable|string',
        ]);

        $loan = ToolLoan::findOrFail($id);

        if (!in_array($loan->status, ['borrowed', 'overdue'])) {
            return response()->json(['message' => 'Alat tidak sedang dipinjam.'], 422);
        }

        $loan->update([
            'status' => 'returned',
            'returned_at' => now(),
            'condition_returned' => $request->condition_returned,
            'notes' => $request->notes,
        ]);

        AuditLog::log('updated', "Alat dikembalikan: {$loan->loan_number}", ToolLoan::class, $loan->id);

        return response()->json(['message' => 'Alat berhasil dikembalikan.']);
    }
}
