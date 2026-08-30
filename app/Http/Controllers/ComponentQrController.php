<?php

namespace App\Http\Controllers;

use App\Models\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ComponentQrController extends Controller
{
    public function show(Component $component)
    {
        $qrSvg = QrCode::size(250)->generate($component->code);

        return view('components.qr', [
            'component' => $component,
            'qrSvg' => $qrSvg,
        ]);
    }

    public function bulkShow(\Illuminate\Http\Request $request)
    {
        $data = $request->query('data');
        if ($data) {
            $idsWithCopies = json_decode(urldecode($data), true);
        } else {
            // fallback for old format
            $ids = $request->query('ids', []);
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            $copies = (int) $request->query('copies', 1);
            $idsWithCopies = [];
            foreach ($ids as $id) {
                if ($id) $idsWithCopies[$id] = $copies > 0 ? $copies : 1;
            }
        }

        $components = Component::whereIn('id', array_keys($idsWithCopies))->get();

        $qrs = [];
        $printData = []; // Array of {component, copies}
        
        foreach ($components as $component) {
            $qrs[$component->id] = QrCode::size(250)->generate($component->code);
            $printData[] = [
                'component' => $component,
                'copies' => $idsWithCopies[$component->id] ?? 1,
            ];
        }

        return view('components.qr-bulk', [
            'printData' => $printData,
            'qrs' => $qrs,
        ]);
    }
}
