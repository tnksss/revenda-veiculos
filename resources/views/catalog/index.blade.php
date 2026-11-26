@extends('layouts.public')

@section('title', 'Estoque de Veículos • AutoPrime Seminovos')

@section('content')
<section class="bg-slate-900 text-white py-10 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Encontre seu próximo veículo</h1>
        <p class="text-slate-300 mt-2 max-w-2xl">Seminovos selecionados, revisados e com laudo cautelar aprovado.</p>

        <form method="GET" action="{{ route('catalog.index') }}" class="mt-6 bg-white text-slate-900 p-4 rounded-xl shadow-lg grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Buscar marca ou modelo</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Ex: Civic, Corolla, SUV..."
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Combustível</label>
                <select name="fuel" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm bg-white">
                    <option value="">Todos</option>
                    @foreach($fuelTypes as $fuel)
                        <option value="{{ $fuel->value }}" @selected(($filters['fuel'] ?? '') === $fuel->value)>
                            {{ $fuel->getLabel() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Câmbio</label>
                <select name="transmission" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm bg-white">
                    <option value="">Todos</option>
                    @foreach($transmissionTypes as $transmission)
                        <option value="{{ $transmission->value }}" @selected(($filters['transmission'] ?? '') === $transmission->value)>
                            {{ $transmission->getLabel() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Preço Máximo (R$)</label>
                <input type="number" name="max_price" value="{{ $filters['max_price'] ?? '' }}" placeholder="Ex: 120000"
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2 px-4 rounded-lg text-sm transition cursor-pointer">
                    Filtrar
                </button>
                @if(!empty(array_filter($filters)))
                    <a href="{{ route('catalog.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800 underline">
                        Limpar
                    </a>
                @endif
            </div>
        </form>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <p class="text-sm text-slate-600">
            Exibindo <span class="font-bold text-slate-900">{{ $vehicles->total() }}</span> veículo(s) encontrado(s)
        </p>

        <form method="GET" action="{{ route('catalog.index') }}" class="flex items-center gap-2">
            @foreach(request()->except('sort', 'page') as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label for="sort" class="text-xs font-semibold text-slate-500 uppercase">Ordenar:</label>
            <select id="sort" name="sort" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
                <option value="featured" @selected(($filters['sort'] ?? 'featured') === 'featured')>Destaques</option>
                <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Menor Preço</option>
                <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Maior Preço</option>
                <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Ano Mais Novo</option>
            </select>
        </form>
    </div>

    @if($vehicles->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
            <h2 class="text-lg font-bold text-slate-800">Nenhum veículo encontrado com esses filtros</h2>
            <p class="text-slate-500 text-sm mt-1">Tente remover alguns filtros para ver todo o nosso estoque disponível.</p>
            <a href="{{ route('catalog.index') }}" class="inline-block mt-4 bg-slate-900 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-800">
                Ver todo o estoque
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($vehicles as $vehicle)
                <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
                    <a href="{{ route('catalog.show', $vehicle->slug) }}" class="relative block aspect-[16/10] bg-slate-200 overflow-hidden">
                        @if($vehicle->coverPhoto)
                            <img src="{{ asset('storage/' . $vehicle->coverPhoto->path) }}"
                                 alt="{{ $vehicle->brand }} {{ $vehicle->model }}"
                                 class="w-full h-full object-cover hover:scale-105 transition duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm">
                                Sem foto de capa
                            </div>
                        @endif

                        <div class="absolute top-3 left-3 flex gap-2">
                            @if($vehicle->status === \App\Enums\VehicleStatus::Reserved)
                                <span class="bg-sky-600 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow">
                                    Reservado
                                </span>
                            @elseif($vehicle->is_featured)
                                <span class="bg-amber-500 text-slate-950 text-xs font-bold px-2.5 py-1 rounded-full shadow">
                                    Destaque
                                </span>
                            @endif
                        </div>

                        <span class="absolute bottom-3 right-3 bg-slate-900/80 text-white text-xs px-2.5 py-1 rounded-md backdrop-blur-sm">
                            Final {{ $vehicle->plate_ending }}
                        </span>
                    </a>

                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="text-xs font-semibold text-amber-600 uppercase tracking-wider">
                                {{ $vehicle->brand }}
                            </div>
                            <h2 class="text-lg font-bold text-slate-900 mt-0.5">
                                <a href="{{ route('catalog.show', $vehicle->slug) }}" class="hover:text-amber-600 transition">
                                    {{ $vehicle->model }}
                                </a>
                            </h2>
                            <p class="text-xs text-slate-500 line-clamp-1 mt-0.5">{{ $vehicle->version }}</p>

                            <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-slate-100 text-xs text-slate-600">
                                <div>
                                    <span class="block text-slate-400">Ano</span>
                                    <strong class="text-slate-800">{{ $vehicle->year_manufacture }}/{{ $vehicle->year_model }}</strong>
                                </div>
                                <div>
                                    <span class="block text-slate-400">KM</span>
                                    <strong class="text-slate-800">{{ number_format($vehicle->mileage, 0, ',', '.') }}</strong>
                                </div>
                                <div>
                                    <span class="block text-slate-400">Câmbio</span>
                                    <strong class="text-slate-800">{{ $vehicle->transmission->getLabel() }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="block text-xs text-slate-400">Valor à vista</span>
                                <span class="text-xl font-extrabold text-slate-900">
                                    R$ {{ number_format((float) $vehicle->price, 2, ',', '.') }}
                                </span>
                            </div>

                            <a href="{{ route('catalog.show', $vehicle->slug) }}"
                               class="bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-white text-xs font-bold px-4 py-2.5 rounded-lg transition">
                                Ver Detalhes
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $vehicles->links() }}
        </div>
    @endif
</section>
@endsection