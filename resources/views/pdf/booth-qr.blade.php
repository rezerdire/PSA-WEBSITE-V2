<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; text-align: center; color: #111; }
        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }
        .header { width: 100%; height: auto; display: block; margin: 0 auto 44px; }
        .qr { width: 720px; height: 720px; display: block; margin: 0 auto; }
        .name { max-width: 94%; font-weight: bold; line-height: 1.1; margin: 24px auto 0; overflow-wrap: break-word; word-wrap: break-word; }
    </style>
</head>
<body>
@foreach ($booths as $booth)
    @php
        $name = trim((string) $booth['name']);
        $nameLength = mb_strlen($name);
        $nameFontSize = match (true) {
            $nameLength <= 18 => 65,
            $nameLength <= 26 => 56,
            $nameLength <= 36 => 48,
            $nameLength <= 48 => 40,
            $nameLength <= 64 => 34,
            default => 28,
        };
    @endphp
    <div class="page">
        @if ($header)
            <img class="header" src="{{ $header }}" alt="Annual Convention 2026">
        @endif

        @if ($booth['qr'])
            <img class="qr" src="{{ $booth['qr'] }}" alt="QR">
        @endif

        <div class="name" style="font-size: {{ $nameFontSize }}px;">{{ $name }}</div>
    </div>
@endforeach
</body>
</html>