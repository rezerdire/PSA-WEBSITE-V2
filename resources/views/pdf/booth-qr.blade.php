<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 40px; }
        body { font-family: DejaVu Sans, sans-serif; text-align: center; color: #111; }
        .page { page-break-after: always; padding-top: 60px; }
        .page:last-child { page-break-after: auto; }
        .name { font-size: 34px; font-weight: bold; margin-bottom: 8px; }
        .meta { font-size: 18px; color: #555; margin-bottom: 4px; }
        .code { font-size: 22px; letter-spacing: 3px; margin-top: 24px; font-weight: bold; }
        .hint { font-size: 14px; color: #777; margin-top: 30px; }
        img { width: 420px; height: 420px; margin-top: 30px; }
    </style>
</head>
<body>
@foreach ($booths as $booth)
    <div class="page">
        <div class="name">{{ $booth['name'] }}</div>

        @if ($booth['qr'])
            <img src="{{ $booth['qr'] }}" alt="QR">
        @endif

        
    </div>
@endforeach
</body>
</html>