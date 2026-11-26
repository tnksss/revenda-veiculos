cat << 'EOF' > resources/views/catalog/show.blade.php
@extends('layouts.public')

@section('title', "{$vehicle->brand} {$vehicle->model} {$vehicle->year_model} • AutoPrime Seminovos")

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

                <div class="mt-6 pt-6 border-t border-slate-200">
                    <h2 class="text-sm font-bold text-slate-900">Prefere enviar uma proposta ou simular financiamento?</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Preencha abaixo e retornamos com as condições.</p>

                    @if(session('lead_success'))
                        <div class="mt-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold p-3.5 rounded-xl">
                            {{ session('lead_success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('catalog.leads.store', $vehicle->slug) }}" class="mt-4 space-y-3">
                        @csrf

                        <div class="hidden" aria-hidden="true">
                            <label for="website">Website</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Seu nome completo *"
                                   class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            @error('name')
                                <span class="text-xs text-red-600 mt-0.5 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="WhatsApp / Telefone *"
                                   class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            @error('phone')
                                <span class="text-xs text-red-600 mt-0.5 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="E-mail (opcional)"
                                   class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            @error('email')
                                <span class="text-xs text-red-600 mt-0.5 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-2 pt-1 text-xs text-slate-700">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="wants_financing" value="1" @checked(old('wants_financing')) class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                                <span>Quero simular financiamento</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="has_trade_in" value="1" @checked(old('has_trade_in')) class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                                <span>Tenho veículo na troca</span>
                            </label>
                        </div>

                        <div>
                            <textarea name="message" rows="2" placeholder="Observações ou proposta (opcional)"
                                      class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">{{ old('message') }}</textarea>
                        </div>

                        <button type="submit"
                                class="w-full bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-white font-bold py-2.5 px-4 rounded-xl text-sm transition cursor-pointer">
                            Enviar Proposta
                        </button>
                    </form>
                </div>
            </div>
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
                    <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
                        <a href="{{ route('catalog.show', $related->slug) }}" class="relative block aspect-[16/10] bg-slate-200 overflow-hidden">
                            @if($related->coverPhoto)
                                <img src="{{ asset('storage/' . $related->coverPhoto->path) }}"
                                     alt="{{ $related->brand }} {{ $related->model }}"
                                     class="w-full h-full object-cover hover:scale-105 transition duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs">
                                    Sem foto de capa
                                </div>
                            @endif

                            @if($related->status === \App\Enums\VehicleStatus::Reserved)
                                <span class="absolute top-3 left-3 bg-sky-600 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow">
                                    Reservado
                                </span>
                            @endif

                            <span class="absolute bottom-3 right-3 bg-slate-900/80 text-white text-xs px-2.5 py-1 rounded-md backdrop-blur-sm">
                                Final {{ $related->plate_ending }}
                            </span>
                        </a>

                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">
                                    {{ $related->brand }}
                                </span>
                                <h3 class="text-base font-bold text-slate-900 mt-0.5">
                                    <a href="{{ route('catalog.show', $related->slug) }}" class="hover:text-amber-600 transition">
                                        {{ $related->model }}
                                    </a>
                                </h3>
                                <p class="text-xs text-slate-500 line-clamp-1 mt-0.5">{{ $related->version }}</p>
                            </div>

                            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-lg font-extrabold text-slate-900">
                                    R$ {{ number_format((float) $related->price, 2, ',', '.') }}
                                </span>

                                <a href="{{ route('catalog.show', $related->slug) }}"
                                   class="text-xs font-bold text-slate-900 hover:text-amber-600 transition">
                                    Detalhes &rarr;
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
EOF