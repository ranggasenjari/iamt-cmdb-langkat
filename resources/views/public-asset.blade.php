<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $asset['asset_code'] }} - {{ $asset['name'] }}</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --blue-950: #0b1b49;
            --blue-700: #1d4ed8;
            --blue-600: #2563eb;
            --blue-50: #eff6ff;
            --yellow-500: #f6c343;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-200: #e2e8f0;
            --page: #f7faff;
        }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background: linear-gradient(135deg, var(--blue-950), #153d8a 52%, var(--page) 52%);
            color: var(--blue-950);
        }
        main {
            width: min(760px, 100%);
            border: 1px solid rgba(37, 99, 235, 0.18);
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 28px 90px rgba(11, 27, 73, 0.28);
            overflow: hidden;
        }
        header {
            display: flex;
            gap: 16px;
            align-items: center;
            padding: 22px;
            border-bottom: 1px solid var(--slate-200);
        }
        img {
            width: 64px;
            height: 64px;
            object-fit: contain;
        }
        .eyebrow {
            margin: 0 0 4px;
            color: var(--blue-700);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0;
            text-transform: uppercase;
        }
        h1 {
            margin: 0;
            color: var(--blue-950);
            font-size: clamp(28px, 5vw, 42px);
            line-height: 1.02;
        }
        .body {
            display: grid;
            gap: 16px;
            padding: 22px;
        }
        .code-card {
            display: grid;
            gap: 6px;
            padding: 18px;
            border-radius: 8px;
            background: #fff7d6;
        }
        .code-card span,
        .detail span {
            color: var(--slate-500);
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .code-card strong {
            color: var(--blue-700);
            font-size: clamp(30px, 7vw, 52px);
            line-height: 1;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .detail {
            min-height: 86px;
            display: grid;
            align-content: center;
            gap: 6px;
            padding: 14px;
            border: 1px solid var(--slate-200);
            border-radius: 8px;
            background: var(--blue-50);
        }
        .detail strong {
            overflow-wrap: anywhere;
            color: var(--slate-700);
        }
        footer {
            padding: 14px 22px 20px;
            color: var(--slate-500);
            font-size: 13px;
        }
        @media (max-width: 620px) {
            body { padding: 14px; }
            header { align-items: flex-start; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <main>
        <header>
            @if ($logoDataUri)
                <img src="{{ $logoDataUri }}" alt="Logo Kabupaten Langkat">
            @endif
            <div>
                <p class="eyebrow">IAMT CMDB Kabupaten Langkat</p>
                <h1>{{ $asset['name'] }}</h1>
            </div>
        </header>
        <section class="body">
            <div class="code-card">
                <span>Kode Aset</span>
                <strong>{{ $asset['asset_code'] }}</strong>
            </div>
            <div class="grid">
                <div class="detail"><span>Jenis Aset</span><strong>{{ $asset['module_label'] }}</strong></div>
                <div class="detail"><span>Status / Kategori</span><strong>{{ $asset['status'] }}</strong></div>
                <div class="detail"><span>Lokasi / Relasi</span><strong>{{ $asset['location'] }}</strong></div>
                @foreach ($asset['details'] as $label => $value)
                    <div class="detail"><span>{{ $label }}</span><strong>{{ $value }}</strong></div>
                @endforeach
            </div>
        </section>
        <footer>
            Halaman ini hanya menampilkan identitas aset publik untuk verifikasi label inventaris.
        </footer>
    </main>
</body>
</html>
