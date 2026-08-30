<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order #{{ $po->po_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; margin: 40px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; }
        .info-box { margin-bottom: 20px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px 0; vertical-align: top; }
        .info-table .label { width: 150px; font-weight: bold; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .items-table th { background-color: #f4f4f4; }
        .text-right { text-align: right !important; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #777; }
        .signature { margin-top: 50px; display: flex; justify-content: space-between; }
        .sign-box { text-align: center; width: 200px; }
        .sign-line { border-top: 1px solid #333; margin-top: 60px; padding-top: 5px; }
        @media print {
            body { margin: 0; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>
    <div style="text-align: right; margin-bottom: 20px;">
        <button class="btn-print" onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #000; color: #fff; border: none; border-radius: 4px;">Cetak Dokumen</button>
    </div>

    <div class="header">
        <div>
            <h2>MOLDMASTER</h2>
            <p>PT. Moldmaster Indonesia<br>Jl. Industri No. 1, Cikarang</p>
        </div>
        <div style="text-align: right;">
            <div class="title">PURCHASE ORDER</div>
            <div><strong>No. PO:</strong> {{ $po->po_number }}</div>
            <div><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($po->po_date)->format('d F Y') }}</div>
        </div>
    </div>

    <div class="info-box">
        <table class="info-table">
            <tr>
                <td class="label">Kepada (Supplier):</td>
                <td>
                    <strong>{{ $po->vendor->name ?? '-' }}</strong><br>
                    {{ $po->vendor->address ?? '-' }}<br>
                    Telp: {{ $po->vendor->phone ?? '-' }}<br>
                    PIC: {{ $po->vendor->contact_person ?? '-' }}
                </td>
                <td class="label">Informasi Tambahan:</td>
                <td>
                    Status: {{ strtoupper($po->status) }}<br>
                    Mata Uang: {{ $po->currency }}<br>
                    Syarat Pembayaran: {{ $po->payment_terms ?? '-' }}<br>
                    Estimasi Tiba: {{ $po->expected_arrival_date ? \Carbon\Carbon::parse($po->expected_arrival_date)->format('d F Y') : '-' }}
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Komponen/Part</th>
                <th class="text-right">Qty</th>
                <th>Satuan</th>
                <th class="text-right">Harga Satuan ({{ $po->currency }})</th>
                <th class="text-right">Subtotal ({{ $po->currency }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->component->name ?? 'Unknown Part' }}</td>
                <td class="text-right">{{ number_format($item->qty_ordered, 0, ',', '.') }}</td>
                <td>{{ $item->unit }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->total_price, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Total Keseluruhan</th>
                <th class="text-right">{{ number_format($po->total_amount, 2, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    @if($po->notes)
    <div style="margin-bottom: 30px;">
        <strong>Catatan:</strong><br>
        <p style="white-space: pre-line;">{{ $po->notes }}</p>
    </div>
    @endif

    <div class="signature">
        <div class="sign-box">
            Dibuat Oleh,
            <div class="sign-line">{{ $po->creator->name ?? 'Admin' }}</div>
        </div>
        <div class="sign-box">
            Disetujui Oleh,
            <div class="sign-line">Manajer Pembelian</div>
        </div>
        <div class="sign-box">
            Diterima Oleh Supplier,
            <div class="sign-line">Tanda Tangan & Cap</div>
        </div>
    </div>

    <div class="footer">
        Dokumen ini dibuat otomatis oleh Sistem Moldmaster pada {{ now()->format('d F Y H:i:s') }}
    </div>
</body>
</html>
