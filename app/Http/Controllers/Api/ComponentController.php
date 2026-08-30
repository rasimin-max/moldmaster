<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Component;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Component::with(['category', 'mold'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->category, fn($q) => $q->whereHas('category', fn($sq) => $sq->where('slug', $request->category)))
            ->when($request->mold_id, fn($q) => $q->where('mold_id', $request->mold_id))
            ->when($request->q, fn($q, $search) => $q->where(fn($sq) =>
                $sq->where('name', 'like', "%{$search}%")
                   ->orWhere('code', 'like', "%{$search}%")
                   ->orWhere('rack_location', 'like', "%{$search}%")
            ));

        $components = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $components->map(fn($c) => $this->formatComponent($c)),
            'meta' => [
                'total' => $components->total(),
                'current_page' => $components->currentPage(),
                'last_page' => $components->lastPage(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $component = Component::with(['category', 'mold', 'vendor'])->findOrFail($id);
        return response()->json($this->formatComponent($component, true));
    }

    public function findByQr(string $qrCode): JsonResponse
    {
        $component = Component::with(['category', 'mold', 'vendor'])
            ->where('qr_code', $qrCode)
            ->orWhere('code', $qrCode)
            ->firstOrFail();

        return response()->json($this->formatComponent($component, true));
    }

    public function search(Request $request): JsonResponse
    {
        $q = $request->q;
        $components = Component::with(['category', 'mold'])
            ->where(fn($query) =>
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('qr_code', 'like', "%{$q}%")
                    ->orWhere('rack_location', 'like', "%{$q}%")
            )
            ->limit(20)
            ->get();

        return response()->json(['data' => $components->map(fn($c) => $this->formatComponent($c))]);
    }

    private function formatComponent(Component $c, bool $detailed = false): array
    {
        $data = [
            'id' => $c->id,
            'code' => $c->code,
            'qr_code' => $c->qr_code,
            'name' => $c->name,
            'category' => $c->category ? ['id' => $c->category->id, 'name' => $c->category->name, 'color' => $c->category->color] : null,
            'mold' => $c->mold ? ['id' => $c->mold->id, 'code' => $c->mold->code, 'name' => $c->mold->name] : null,
            'rack_location' => $c->rack_location,
            'stock' => $c->stock,
            'stock_minimum' => $c->stock_minimum,
            'available_stock' => $c->available_stock,
            'is_low_stock' => $c->is_low_stock,
            'status' => $c->status,
            'status_label' => $c->status_label,
            'shot_count' => $c->shot_count,
            'shot_life' => $c->shot_life,
            'photo' => $c->photo ? asset('storage/' . $c->photo) : null,
        ];

        if ($detailed) {
            $data['material'] = $c->material;
            $data['size_spec'] = $c->size_spec;
            $data['unit_price'] = $c->unit_price;
            $data['unit'] = $c->unit;
            $data['description'] = $c->description;
            $data['vendor'] = $c->vendor ? ['id' => $c->vendor->id, 'name' => $c->vendor->name, 'phone' => $c->vendor->phone] : null;
        }

        return $data;
    }
}
