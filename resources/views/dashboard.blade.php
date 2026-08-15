<x-app-layout>
    <x-slot name="header">
        <h1 class="text-base font-semibold text-slate-900">Dashboard</h1>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Boas-vindas --}}
        <div class="app-card mb-6 flex flex-col gap-4 overflow-hidden p-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-violet-500">Olá, {{ explode(' ', auth()->user()->name)[0] }}</p>
                <h2 class="mt-1 text-xl font-bold text-slate-900">Como está o funil hoje?</h2>
                <p class="mt-1 text-sm text-slate-500">Acompanhe seus leads, responda rápido e feche mais negócios.</p>
            </div>
            <a href="{{ route('admin.leads.index') }}" class="btn-primary shrink-0">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15" />
                </svg>
                Abrir o Kanban
            </a>
        </div>

        {{-- Stat tiles --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="app-card p-5">
                <p class="field-label">Total de leads</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                <p class="mt-1 text-xs text-slate-400">em todo o funil</p>
            </div>

            <div class="app-card p-5">
                <p class="field-label">Novos essa semana</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['novos_semana'] }}</p>
                <p class="mt-1 text-xs text-slate-400">desde segunda-feira</p>
            </div>

            <div class="app-card p-5">
                <p class="field-label">Em negociação</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">
                    {{ $stats['valor_em_negociacao'] > 0 ? 'R$ '.number_format($stats['valor_em_negociacao'], 0, ',', '.') : '—' }}
                </p>
                <p class="mt-1 text-xs text-slate-400">valor estimado somado</p>
            </div>

            <div class="app-card p-5">
                <p class="field-label">Taxa de fechamento</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">
                    {{ $stats['taxa_fechamento'] !== null ? $stats['taxa_fechamento'].'%' : '—' }}
                </p>
                <p class="mt-1 text-xs text-slate-400">fechados vs. perdidos</p>
            </div>
        </div>

        {{-- Funil --}}
        <div class="app-card mt-6 p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Funil de leads</h3>
                <a href="{{ route('admin.leads.index') }}" class="text-xs font-medium text-violet-600 hover:text-violet-700">ver Kanban →</a>
            </div>

            <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100">
                @foreach (\App\Models\Lead::STATUSES as $status)
                    @php $qtd = $stats['por_status'][$status] ?? 0; @endphp
                    @if ($qtd > 0)
                        <div
                            class="{{ \App\Models\Lead::STATUS_DOT_CLASSES[$status] }} h-full"
                            style="width: {{ $stats['total'] > 0 ? round(($qtd / $stats['total']) * 100, 2) : 0 }}%"
                            title="{{ \App\Models\Lead::STATUS_LABELS[$status] }}: {{ $qtd }}"
                        ></div>
                    @endif
                @endforeach
            </div>

            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2">
                @foreach (\App\Models\Lead::STATUSES as $status)
                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="h-2 w-2 rounded-full {{ \App\Models\Lead::STATUS_DOT_CLASSES[$status] }}"></span>
                        {{ \App\Models\Lead::STATUS_LABELS[$status] }}
                        <span class="font-semibold text-slate-700">{{ $stats['por_status'][$status] ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
