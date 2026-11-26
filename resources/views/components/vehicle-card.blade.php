@props(['vehicle'])

<article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
    <a href="{{ route('catalog.show', $vehicle->slug) }}" class="relative block aspect-[16/10] bg-slate-200 overflow-hidden">
        @if($vehicle->cover_url)
            <img src="{{ $vehicle->cover_url }}"
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
            <h3 class="text-lg font-bold text-slate-900 mt-0.5">
                <a href="{{ route('catalog.show', $vehicle->slug) }}" class="hover:text-amber-600 transition">
                    {{ $vehicle->model }}
                </a>
            </h3>
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
