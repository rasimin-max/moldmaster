<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ToolQrController extends Controller
{
    public function show(Tool $tool)
    {
        $qrSvg = QrCode::size(250)->generate($tool->code);

        return view('tools.qr', [
            'tool' => $tool,
            'qrSvg' => $qrSvg,
        ]);
    }
}
