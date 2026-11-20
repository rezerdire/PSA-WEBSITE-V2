<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            font-family: "DejaVu Sans", sans-serif;
            text-align: center;
            color: #111;
        }

        .page {
            width: 100%;
            height: 296mm;
            border-collapse: collapse;
            border-spacing: 0;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .page td {
            height: 296mm;
            padding: 0;
            vertical-align: middle;
            text-align: center;
        }


        .qr {
            width: 720px;
            height: 720px;
            display: block;
            margin: 0 auto;
        }

        .raffle-title {
            font-family: "DejaVu Sans", sans-serif;
            font-weight: bold;
            margin: 0 auto 0;
        }
        .name {
            font-family: "DejaVu Sans", sans-serif;
            font-weight: bold;
            line-height: 1;
        }

        

        .name {
            max-width: 94%;
            margin: 8px auto 0;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
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

        <table class="page">
            <tr>
                <td>
                    <div class="raffle-title" style="font-size: {{ $nameFontSize }}px;">
                        PSA QR RAFFLE
                    </div>

                    @if ($booth['qr'])
                        <img class="qr" src="{{ $booth['qr'] }}" alt="QR">
                    @endif

                    <div class="name" style="font-size: {{ $nameFontSize }}px;">
                        {{ $name }}
                    </div>
                </td>
            </tr>
        </table>
    @endforeach

</body>

</html>
