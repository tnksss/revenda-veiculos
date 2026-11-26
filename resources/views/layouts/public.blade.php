<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AutoPrime Seminovos • Seminovos Selecionados')</title>
    <meta name="description" content="@yield('description', 'Seminovos selecionados, periciados e com procedência garantida na AutoPrime Seminovos.')">
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
                <span class="bg-amber-500 text-slate-950 px-2 py-0.5 rounded font-black">AUTO</span>
                <span>PRIME SEMINOVOS</span>
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm">
            <p>&copy; {{ date('Y') }} AutoPrime Seminovos. Todos os direitos reservados.</p>
            <p class="text-xs text-slate-500">Veículos periciados com procedência garantida.</p>
        </div>
    </footer>
</body>
</html>