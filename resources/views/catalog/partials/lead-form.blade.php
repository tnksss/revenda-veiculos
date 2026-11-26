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
