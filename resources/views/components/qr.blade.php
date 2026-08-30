<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>QR Komponen - {{ $component->code }}</title>
    <style>
        @page {
            size: 60mm 30mm landscape;
            margin: 0;
        }
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 2mm 3mm;
            width: 60mm;
            height: 30mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-sizing: border-box;
            background: white;
            overflow: hidden;
        }
        .info {
            width: 60%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
            padding-right: 2mm;
        }
        .title {
            font-size: 8pt;
            font-weight: bold;
            line-height: 1.1;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .detail {
            font-size: 6pt;
            color: #000;
            line-height: 1.3;
            margin: 0;
        }
        .qr-container {
            width: 40%;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        .qr-container svg {
            width: 24mm;
            height: 24mm;
        }
        
        /* Hilangkan margin & header/footer default browser saat print */
        @media print {
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="info">
        <div class="title">{{ $component->name }}</div>
        <p class="detail">
            Kode: {{ $component->code }}<br>
            Mold: {{ $component->mold?->mold_number ?? $component->mold?->code ?? '-' }}<br>
            Bagian: {{ $component->category?->name ?? '-' }}<br>
            Spek: {{ $component->size_spec ?? '-' }}
        </p>
    </div>
    <div class="qr-container">
        {!! $qrSvg !!}
    </div>
    <script>
        // Otomatis membuka dialog print saat halaman dimuat
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>