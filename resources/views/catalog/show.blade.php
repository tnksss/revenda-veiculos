@extends('layouts.public')

@section('title', "{$vehicle->brand} {$vehicle->model} {$vehicle->year_model} • AutoPrime Seminovos")
@section('description', "{$vehicle->brand} {$vehicle->model} {$vehicle->version} ({$vehicle->year_manufacture}/{$vehicle->year_model}), {$vehicle->transmission->getLabel()}, {$vehicle->fuel_type->getLabel()}, com " . number_format($vehicle->mileage, 0, ',', '.') . " km por R$ " . number_format((float) $vehicle->price, 2, ',', '.') . ".")

@section('meta')
    @php
        $ogPhoto = $vehicle->photos->first()?->path
            ? asset('storage/' . $vehicle->photos->first()->path)
            : null;
        $ogTitle = "{$vehicle->brand} {$vehicle->model} {$vehicle->year_manufacture}/{$vehicle->year_model} • R$ " . number_format((float) $vehicle->price, 2, ',', '.');
        $ogDescription = "{$vehicle->version} • " . number_format($vehicle->mileage, 0, ',', '.') . " km • {$vehicle->transmission->getLabel()} • {$vehicle->fuel_type->getLabel()}";
    @endphp
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="AutoPrime Seminovos">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:url" content="{{ route('catalog.show', $vehicle->slug) }}">
    @if($ogPhoto)
        <meta property="og:image" content="{{ $ogPhoto }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
@endsection

@push('structured-data')
    @php
        $schemaData = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Vehicle',
            'name' => "{$vehicle->brand} {$vehicle->model} {$vehicle->year_model}",
            'brand' => ['@type' => 'Brand', 'name' => $vehicle->brand],
            'model' => $vehicle->model,
            'vehicleModelDate' => (string) $vehicle->year_model,
            'productionDate' => (string) $vehicle->year_manufacture,
            'color' => $vehicle->color,
            'fuelType' => $vehicle->fuel_type->getLabel(),
            'vehicleTransmission' => $vehicle->transmission->getLabel(),
            'mileageFromOdometer' => [
                '@type' => 'QuantitativeValue',
                'value' => $vehicle->mileage,
                'unitCode' => 'KMT',
            ],
            'image' => $vehicle->photos->map(fn ($photo): string => asset('storage/' . $photo->path))->values()->all(),
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'BRL',
                'price' => number_format((float) $vehicle->price, 2, '.', ''),
                'availability' => $vehicle->status === \App\Enums\VehicleStatus::Available
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/LimitedAvailability',
                'url' => route('catalog.show', $vehicle->slug),
            ],
        ]);
    @endphp
    <script type="application/ld+json">{!! json_encode($schemaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
@php
    $firstPhoto = $vehicle->photos->first()?->path;
    $whatsappMessage = urlencode("Olá! Tenho interesse no {$vehicle->brand} {$vehicle->model} ({$vehicle->year_manufacture}/{$vehicle->year_model}) anunciado no site.");
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <nav class="text-xs text-slate-500 mb-6 flex items-center gap-2">
        <a href="{{ route('catalog.index') }}" class="hover:text-slate-900">Estoque</a>
        <span>/</span>
        <span class="text-slate-800 font-medium">{{ $vehicle->brand }} {{ $vehicle->model }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-7" x-data="{ activePhoto: '{{ $firstPhoto ? asset('storage/' . $firstPhoto) : '' }}' }">
            <div class="aspect-[16/10] bg-slate-200 rounded-2xl overflow-hidden border border-slate-200">
                <template x-if="activePhoto">
                    <img :src="activePhoto" alt="{{ $vehicle->brand }} {{ $vehicle->model }}" class="w-full h-full object-cover">
                </template>
                <template x-if="!activePhoto">
                    <div class="w-full h-full flex items-center justify-center text-slate-400">
                        Nenhuma foto disponível
                    </div>
                </template>
            </div>

            @if($vehicle->photos->count() > 1)
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-3 mt-4">
                    @foreach($vehicle->photos as $photo)
                        <button type="button"
                                @click="activePhoto = '{{ asset('storage/' . $photo->path) }}'"
                                :class="activePhoto === '{{ asset('storage/' . $photo->path) }}' ? 'ring-2 ring-amber-500' : 'opacity-75 hover:opacity-100'"
                                class="aspect-[16/10] rounded-lg overflow-hidden bg-slate-200 border border-slate-200 transition cursor-pointer">
                            <img src="{{ asset('storage/' . $photo->path) }}" alt="Miniatura" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif

            @if($vehicle->description)
                <div class="bg-white rounded-2xl border border-slate-200 p-6 mt-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-3">Sobre este veículo</h2>
                    <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line">{{ $vehicle->description }}</p>
                </div>
            @endif

            @if(!empty($vehicle->features))
                <div class="bg-white rounded-2xl border border-slate-200 p-6 mt-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Opcionais e Equipamentos</h2>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-sm text-slate-700">
                        @foreach($vehicle->features as $feature)
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-600 font-bold">✓</span>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="lg:col-span-5">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm sticky top-24">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600">{{ $vehicle->brand }}</span>
                    <span class="text-xs bg-slate-100 text-slate-700 font-semibold px-2.5 py-1 rounded">
                        Final da Placa: {{ $vehicle->plate_ending }}
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $vehicle->model }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ $vehicle->version }}</p>

                @if($vehicle->status === \App\Enums\VehicleStatus::Reserved)
                    <div class="mt-4 bg-sky-50 border border-sky-200 text-sky-800 text-xs font-semibold px-3 py-2 rounded-lg">
                        Este veículo está reservado no momento, mas você pode entrar na fila de interesse.
                    </div>
                @endif

                <div class="mt-6 pt-6 border-t border-slate-100">
                    <span class="text-xs text-slate-400 block">Valor à vista</span>
                    <span class="text-3xl font-black text-slate-900">
                        R$ {{ number_format((float) $vehicle->price, 2, ',', '.') }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-slate-100 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 block">Ano Fab./Mod.</span>
                        <strong class="text-slate-800">{{ $vehicle->year_manufacture }}/{{ $vehicle->year_model }}</strong>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Quilometragem</span>
                        <strong class="text-slate-800">{{ number_format($vehicle->mileage, 0, ',', '.') }} km</strong>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Câmbio</span>
                        <strong class="text-slate-800">{{ $vehicle->transmission->getLabel() }}</strong>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Combustível</span>
                        <strong class="text-slate-800">{{ $vehicle->fuel_type->getLabel() }}</strong>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Cor</span>
                        <strong class="text-slate-800">{{ $vehicle->color }}</strong>
                    </div>
                </div>

                <div class="mt-8 space-y-3">
                    <a href="https://wa.me/5542999999999?text={{ $whatsappMessage }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-5 rounded-xl transition shadow-sm">
                        <span>Chamar Agora no WhatsApp</span>
                    </a>
                </div>

                @include('catalog.partials.lead-form')
            </div>
        </div>
    </div>

    @if($relatedVehicles->isNotEmpty())
        <section class="mt-16 pt-12 border-t border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Outros veículos em destaque</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Opções selecionadas disponíveis em nosso estoque.</p>
                </div>

                <a href="{{ route('catalog.index') }}" class="text-xs sm:text-sm font-bold text-amber-600 hover:text-amber-700 transition">
                    Ver estoque completo &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($relatedVehicles as $related)
                    <x-vehicle-card :vehicle="$related" />
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
