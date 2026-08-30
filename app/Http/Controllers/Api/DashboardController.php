<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Models\Machine;
use App\Models\Maintenance;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\ToolLoan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $user->getRoleNames()->first();

        $data = match($role) {
            'super_admin', 'admin' => $this->adminStats($user),
            'leader' => $this->leaderStats($user),
            'operator' => $this->operatorStats($user),
            'viewer' => $this->viewerStats(),
            default => [],
        };

        return response()->json([
            'role' => $role,
            'user' => ['name' => $user->name, 'area' => $user->area],
            'stats' => $data,
        ]);
    }

    private function adminStats($user): array
    {
        return [
            'total_components' => Component::count(),
            'ready_components' => Component::where('status', 'ready')->count(),
            'low_stock_components' => Component::whereColumn('stock', '<=', 'stock_minimum')->count(),
            'pending_arrivals' => Component::where('status', 'pending_arrival')->count(),
            'operational_machines' => Machine::where('status', 'operational')->count(),
            'breakdown_machines' => Machine::where('status', 'breakdown')->count(),
            'pending_movements' => StockMovement::where('status', 'pending')->count(),
            'pending_maintenances' => Maintenance::where('status', 'pending')->count(),
            'pending_loans' => ToolLoan::where('status', 'pending')->count(),
            'active_po' => PurchaseOrder::whereIn('status', ['sent', 'ordered', 'partial'])->count(),
            'today_movements' => StockMovement::whereDate('created_at', today())->count(),
        ];
    }

    private function leaderStats($user): array
    {
        return [
            'pending_movements' => StockMovement::where('status', 'pending')->count(),
            'pending_maintenances' => Maintenance::where('status', 'pending')->count(),
            'pending_loans' => ToolLoan::where('status', 'pending')->count(),
            'low_stock_components' => Component::whereColumn('stock', '<=', 'stock_minimum')->count(),
            'breakdown_machines' => Machine::where('status', 'breakdown')->count(),
            'in_progress_maintenances' => Maintenance::where('status', 'in_progress')->count(),
            'active_loans' => ToolLoan::where('status', 'borrowed')->count(),
        ];
    }

    private function operatorStats($user): array
    {
        return [
            'available_components' => Component::where('status', 'ready')->where('stock', '>', 0)->count(),
            'my_pending_movements' => StockMovement::where('requested_by', $user->id)->where('status', 'pending')->count(),
            'my_approved_movements' => StockMovement::where('requested_by', $user->id)->where('status', 'approved')->count(),
            'my_active_loans' => ToolLoan::where('borrower_id', $user->id)->where('status', 'borrowed')->count(),
            'my_pending_maintenances' => Maintenance::where('reported_by', $user->id)->where('status', 'pending')->count(),
            'my_recent_movements' => StockMovement::where('requested_by', $user->id)->latest()->limit(5)->get()->map(fn($m) => [
                'reference_number' => $m->reference_number,
                'type' => $m->type,
                'status' => $m->status,
                'created_at' => $m->created_at,
            ]),
        ];
    }

    private function viewerStats(): array
    {
        return [
            'total_components' => Component::count(),
            'ready_components' => Component::where('status', 'ready')->count(),
            'low_stock_components' => Component::whereColumn('stock', '<=', 'stock_minimum')->count(),
            'total_molds' => \App\Models\Mold::count(),
            'active_molds' => \App\Models\Mold::where('status', 'active')->count(),
            'operational_machines' => Machine::where('status', 'operational')->count(),
            'breakdown_machines' => Machine::where('status', 'breakdown')->count(),
        ];
    }
}
