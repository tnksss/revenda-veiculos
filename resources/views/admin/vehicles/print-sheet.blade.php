<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Pátio - {{ $vehicle->brand }} {{ $vehicle->model }} | Tobias Veículos</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <style>
        @media print {
            @page { size: A4 portrait; margin: 12mm; }
            .no-print { display: none !important; }
            body { background: #fff !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
    <div class="no-print bg-slate-900 text-white py-3 px-6 flex items-center justify-between">
        <span class="text-sm font-medium">Pré-visualização da Ficha de Pátio (A4 — Modo Economia de Toner)</span>
        <button type="button" onclick="window.print()" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm px-4 py-2 rounded-lg cursor-pointer">
            Imprimir / Salvar PDF
        </button>
    </div>

    <main class="max-w-3xl mx-auto my-6 bg-white border-2 border-slate-800 rounded-2xl p-8 shadow-sm print:my-0 print:shadow-none">
        <header class="flex items-center justify-between border-b-2 border-slate-800 pb-5">
            <div>
                <div class="inline-flex items-center gap-1.5 border-2 border-slate-900 text-slate-900 px-3 py-1 rounded-md text-xs font-extrabold tracking-wider">
                    <span class="text-amber-600">TOBIAS</span> VEÍCULOS
                </div>
                <p class="text-xs text-slate-500 mt-1.5">Seminovos selecionados com procedência e garantia</p>
            </div>
            <div class="text-right">
                <span class="text-xs uppercase tracking-wider text-slate-500 block">Final da Placa</span>
                <span class="text-2xl font-black text-slate-900">{{ substr($vehicle->plate, -1) }}</span>
            </div>
        </header>

        <section class="mt-6">
            <p class="text-sm font-bold uppercase tracking-widest text-amber-600">{{ $vehicle->brand }}</p>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-0.5">{{ $vehicle->model }}</h1>
            @if($vehicle->version)
                <p class="text-lg text-slate-600 font-medium mt-1">{{ $vehicle->version }}</p>
            @endif
        </section>

        <section class="mt-6 bg-slate-50 border border-slate-300 border-l-8 border-l-amber-500 rounded-xl p-6 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block">Valor à Vista</span>
                <span class="text-4xl font-black text-slate-900">R$ {{ number_format((float) $vehicle->price, 2, ',', '.') }}</span>
            </div>
            <div class="text-right text-xs text-slate-600 max-w-xs">
                Consulte condições especiais de financiamento e avaliação do seu usado na troca.
            </div>
        </section>

        <section class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
            <div class="border border-slate-300 rounded-xl p-3.5">
                <span class="text-xs text-slate-500 block">Ano Fab./Mod.</span>
                <strong class="text-base font-bold text-slate-900">{{ $vehicle->year_manufacture }}/{{ $vehicle->year_model }}</strong>
            </div>
            <div class="border border-slate-300 rounded-xl p-3.5">
                <span class="text-xs text-slate-500 block">Quilometragem</span>
                <strong class="text-base font-bold text-slate-900">{{ number_format($vehicle->mileage, 0, ',', '.') }} km</strong>
            </div>
            <div class="border border-slate-300 rounded-xl p-3.5">
                <span class="text-xs text-slate-500 block">Câmbio</span>
                <strong class="text-base font-bold text-slate-900">{{ $vehicle->transmission->getLabel() }}</strong>
            </div>
            <div class="border border-slate-300 rounded-xl p-3.5">
                <span class="text-xs text-slate-500 block">Combustível</span>
                <strong class="text-base font-bold text-slate-900">{{ $vehicle->fuel_type->getLabel() }}</strong>
            </div>
        </section>

        @if(!empty($vehicle->features))
            <section class="mt-6">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200 pb-2 mb-3">
                    Principais Opcionais e Equipamentos
                </h2>
                <ul class="grid grid-cols-2 gap-x-6 gap-y-1.5 text-sm text-slate-800">
                    @foreach(array_slice($vehicle->features, 0, 14) as $feature)
                        <li>&#10003; {{ $feature }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="mt-8 pt-5 border-t border-slate-300 flex items-center justify-between gap-6">
            <div class="space-y-1">
                <p class="text-sm font-bold text-slate-900">Aponte a câmera do celular para o QR Code</p>
                <p class="text-xs text-slate-600">Veja a galeria completa de fotos, detalhes e envie sua proposta online:</p>
                <p class="font-mono text-xs font-semibold text-slate-800 pt-1 break-all">{{ $publicUrl }}</p>
            </div>
            <div class="shrink-0 p-2 bg-white border border-slate-300 rounded-xl">
                <div id="vehicle-qrcode" aria-label="QR Code para ver mais fotos do veículo"></div>
            </div>
        </footer>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof qrcode === 'function') {
                var qr = qrcode(0, 'M');
                qr.addData(@json($publicUrl));
                qr.make();
                document.getElementById('vehicle-qrcode').innerHTML = qr.createSvgTag({ cellSize: 3, margin: 0, scalable: false });
            }
        });
    </script>
</body>
</html>
