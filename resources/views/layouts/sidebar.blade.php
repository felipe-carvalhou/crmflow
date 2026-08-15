@php
    $totalLeads = $leadCountsPorStatus->sum();
    $semFiltro = request()->routeIs('admin.leads.index') && ! request('status');
@endphp

<aside
    x-cloak
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-white border-r border-slate-200/70 transition-transform duration-200 ease-out lg:translate-x-0"
>
    {{-- Marca --}}
    <div class="flex items-center gap-3 px-5 py-5">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-600 via-fuchsia-500 to-orange-400 shadow-soft">
            <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
            </svg>
        </div>
        <div class="min-w-0">
            <p class="truncate text-sm font-bold text-slate-900">AutoFlow <span class="font-medium text-violet-500">CRM</span></p>
            <p class="truncate text-xs text-slate-400">Leads &amp; funil de vendas</p>
        </div>
        <button @click="sidebarOpen = false" class="ml-auto shrink-0 text-slate-400 hover:text-slate-600 lg:hidden">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto scrollbar-thin px-3 pb-4">
        <p class="px-3 pb-1.5 pt-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Visão geral</p>

        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
            </svg>
            Dashboard
        </a>

        <p class="px-3 pb-1.5 pt-5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Leads</p>

        <a href="{{ route('admin.leads.index') }}" class="nav-link {{ $semFiltro ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            <span class="flex-1 text-left">Todos os leads</span>
            <span class="rounded-full {{ $semFiltro ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-500' }} px-1.5 py-0.5 text-[11px] font-semibold">{{ $totalLeads }}</span>
        </a>

        @foreach (\App\Models\Lead::STATUSES as $status)
            @php $ativo = request()->routeIs('admin.leads.index') && request('status') === $status; @endphp
            <a href="{{ route('admin.leads.index', ['status' => $status]) }}" class="nav-link {{ $ativo ? 'nav-link-active' : '' }}">
                <span class="ml-0.5 h-2 w-2 shrink-0 rounded-full {{ \App\Models\Lead::STATUS_DOT_CLASSES[$status] }}"></span>
                <span class="flex-1 text-left">{{ \App\Models\Lead::STATUS_LABELS[$status] }}</span>
                <span class="rounded-full {{ $ativo ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-500' }} px-1.5 py-0.5 text-[11px] font-semibold">{{ $leadCountsPorStatus[$status] ?? 0 }}</span>
            </a>
        @endforeach

        <p class="px-3 pb-1.5 pt-5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Conta</p>

        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'nav-link-active' : '' }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Perfil
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-link w-full">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                </svg>
                Sair
            </button>
        </form>
    </nav>

    <div class="border-t border-slate-200/70 p-4">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-violet-100 text-sm font-bold text-violet-700">
                {{ Illuminate\Support\Str::of(auth()->user()->name)->substr(0, 1)->upper() }}
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-slate-400">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>
</aside>
