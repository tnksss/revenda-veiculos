<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TOBIAS VEÍCULOS • Seminovos Selecionados')</title>
    <meta name="description" content="@yield('description', 'Seminovos selecionados, periciados e com procedência garantida na TOBIAS VEÍCULOS.')">
    <link rel="canonical" href="{{ url()->current() }}">

    @yield('meta')
    @stack('structured-data')

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-900 antialiased flex flex-col min-h-screen">
    <header class="bg-slate-900 text-white shadow-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('catalog.index') }}" class="flex items-center gap-2 font-bold text-xl tracking-tight">
                <span class="bg-amber-500 text-slate-950 px-2 py-0.5 rounded font-black">TOBIAS</span> <span class="text-white">VEÍCULOS</span>
            </a>

            <nav class="flex items-center gap-6 text-sm font-medium">
                <a href="{{ route('catalog.index') }}" class="hover:text-amber-400 transition">Estoque</a>
                <a href="/admin" class="text-slate-400 hover:text-white text-xs border border-slate-700 px-3 py-1.5 rounded-md transition">
                    Área Restrita
                </a>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

        <footer class="bg-slate-900 text-slate-400 mt-16 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 md:grid-cols-3 gap-8 text-sm">
            <div>
                <div class="inline-flex items-center gap-1.5 font-black text-lg tracking-tight text-white">
                    <span class="text-amber-400">TOBIAS</span> VEÍCULOS
                </div>
                <p class="mt-3 text-slate-400 leading-relaxed">
                    Seminovos selecionados com procedência, perícia cautelar e transparência em cada negociação.
                </p>
            </div>

            <div class="space-y-2">
                <h3 class="text-white font-bold uppercase tracking-wider text-xs">Atendimento &amp; Localização</h3>
                <p>{{ config('store.address') }}</p>
                <p>{{ config('store.hours') }}</p>
                <p class="text-slate-300 font-medium">Telefone: {{ config('store.phone') }}</p>
            </div>

            <div class="flex flex-col items-start md:items-end justify-between gap-4">
                <a href="{{ \App\Support\StoreContact::whatsappUrl('Olá! Vim pelo site da Tobias Veículos e gostaria de atendimento.') }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold px-5 py-2.5 rounded-xl transition">
                    <span>Chamar no WhatsApp</span>
                </a>
                <p class="text-xs text-slate-500">
                    &copy; {{ date('Y') }} {{ config('store.name') }}. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>