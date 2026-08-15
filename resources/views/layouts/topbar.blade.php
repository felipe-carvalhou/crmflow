<header class="sticky top-0 z-30 flex items-center gap-3 border-b border-slate-200/70 bg-white/80 px-4 py-3.5 backdrop-blur sm:px-6">
    <button @click="sidebarOpen = true" class="shrink-0 rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700 lg:hidden">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
        </svg>
    </button>

    @isset($header)
        <div class="min-w-0 flex-1">{{ $header }}</div>
    @endisset
</header>
