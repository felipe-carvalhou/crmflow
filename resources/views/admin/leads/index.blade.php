<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <h1 class="text-base font-semibold text-slate-900">Leads</h1>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">{{ $stats['total'] }}</span>
        </div>
    </x-slot>

    <div
        class="px-4 py-5 sm:px-6 lg:px-8"
        x-data="leadsBoard(@js($leads), @js($statuses), @js($statusFiltroInicial))"
    >
        {{-- Erro global (toast simples) --}}
        <div
            x-show="erro"
            x-cloak
            x-transition
            @click="erro = ''"
            class="mb-4 cursor-pointer rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
        >
            <span x-text="erro"></span>
            <span class="ml-2 text-rose-400">(toque pra fechar)</span>
        </div>

        {{-- Intro --}}
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Funil de leads</h2>
                <p class="mt-1 text-sm text-slate-500">Arraste os cards entre as colunas, registre notas e nunca perca um contato.</p>
            </div>
            <button @click="abrirCriacao()" class="btn-primary shrink-0">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Novo lead
            </button>
        </div>

        {{-- Stat tiles --}}
        <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="app-card p-4">
                <p class="field-label">Total</p>
                <p class="mt-1.5 text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
            </div>
            <div class="app-card p-4">
                <p class="field-label">Novos / semana</p>
                <p class="mt-1.5 text-2xl font-bold text-slate-900">{{ $stats['novos_semana'] }}</p>
            </div>
            <div class="app-card p-4">
                <p class="field-label">Em negociação</p>
                <p class="mt-1.5 text-2xl font-bold text-slate-900">
                    {{ $stats['valor_em_negociacao'] > 0 ? 'R$ '.number_format($stats['valor_em_negociacao'], 0, ',', '.') : '—' }}
                </p>
            </div>
            <div class="app-card p-4">
                <p class="field-label">Fechamento</p>
                <p class="mt-1.5 text-2xl font-bold text-slate-900">
                    {{ $stats['taxa_fechamento'] !== null ? $stats['taxa_fechamento'].'%' : '—' }}
                </p>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="app-card mb-4 flex flex-col gap-3 p-3 sm:flex-row sm:items-center">
            <div class="relative w-full sm:w-72">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Buscar por nome ou telefone..."
                    class="field-input pl-9"
                >
            </div>

            <select x-model="origemFiltro" class="field-input w-full sm:w-56">
                <option value="">Todas as origens</option>
                @foreach ($origens as $origem)
                    <option value="{{ $origem }}">{{ $origem }}</option>
                @endforeach
            </select>

            <div x-show="statusFiltro" x-cloak class="inline-flex w-fit items-center gap-1.5 rounded-full bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700">
                <span>Mostrando só: <span x-text="statusLabel(statusFiltro)"></span></span>
                <button @click="limparFiltroStatus()" class="text-violet-400 hover:text-violet-600">&times;</button>
            </div>

            <span class="text-xs text-slate-400 sm:ml-auto">
                Arraste no desktop, ou use o seletor de status no celular.
            </span>
        </div>

        {{-- Board --}}
        <div class="flex gap-4 overflow-x-auto pb-4 items-start">
            <template x-for="status in colunasVisiveis" :key="status">
                <div
                    class="flex-shrink-0 w-[85vw] sm:w-80 app-card flex flex-col max-h-[75vh] transition"
                    :class="{ 'ring-2 ring-violet-400': dragOverStatus === status }"
                    @dragover.prevent="onDragOverColumn(status)"
                    @dragleave="dragOverStatus === status && (dragOverStatus = null)"
                    @drop.prevent="onDropColumn(status)"
                >
                    <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-100 sticky top-0 bg-white rounded-t-2xl z-10">
                        <span class="h-2 w-2 rounded-full" :class="statusDot(status)"></span>
                        <h3 class="flex-1 font-semibold text-sm text-slate-700" x-text="statusLabel(status)"></h3>
                        <span class="inline-flex items-center justify-center min-w-[1.5rem] h-5 px-1.5 rounded-full bg-slate-100 text-slate-500 text-xs font-semibold" x-text="porStatus(status).length"></span>
                    </div>

                    <div class="p-2.5 space-y-2.5 overflow-y-auto scrollbar-thin">
                        <template x-for="lead in porStatus(status)" :key="lead.id">
                            <div
                                draggable="true"
                                @dragstart="onDragStart(lead)"
                                @click="abrir(lead)"
                                class="group rounded-xl border border-slate-200/70 bg-white p-3 cursor-pointer transition hover:shadow-card hover:border-violet-200"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <div
                                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[11px] font-bold"
                                            :class="avatarBadge(lead.nome)"
                                            x-text="iniciais(lead.nome)"
                                        ></div>
                                        <p class="truncate text-sm font-semibold text-slate-800" x-text="lead.nome"></p>
                                    </div>
                                    <span
                                        x-show="lead.origem"
                                        x-text="lead.origem"
                                        class="shrink-0 rounded-md px-1.5 py-0.5 text-[10px] font-semibold"
                                        :class="origemBadge(lead.origem)"
                                    ></span>
                                </div>

                                <p class="mt-2 truncate text-xs text-slate-400" x-text="lead.segmento"></p>

                                <div class="mt-2.5 flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span x-text="lead.criado_em"></span>
                                    </span>
                                    <span x-show="lead.valor_estimado" class="text-[11px] font-semibold text-emerald-600" x-text="formatarMoeda(lead.valor_estimado)"></span>
                                </div>

                                <div class="mt-2.5 flex items-center gap-1.5" @click.stop>
                                    <a
                                        :href="waLink(lead.telefone)"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-emerald-50 py-1.5 text-[11px] font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                    >
                                        <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                        </svg>
                                        <span x-text="lead.telefone"></span>
                                    </a>
                                </div>

                                {{-- Fallback de mudança de status sem drag-and-drop (essencial no celular) --}}
                                <select
                                    @click.stop
                                    @change="onSelecionarStatus(lead, $event)"
                                    class="mt-1.5 w-full rounded-lg border-slate-200 bg-white py-1 text-[11px] text-slate-500 focus:border-violet-400 focus:ring-violet-400"
                                >
                                    <template x-for="s in statuses" :key="s">
                                        <option :value="s" :selected="s === lead.status" x-text="statusLabel(s)"></option>
                                    </template>
                                </select>
                            </div>
                        </template>

                        <p
                            x-show="porStatus(status).length === 0"
                            class="text-xs text-slate-400 text-center py-8"
                        >
                            Nenhum lead aqui.
                        </p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Painel lateral / bottom sheet --}}
        <div x-show="selecionado" x-cloak class="fixed inset-0 z-40" style="display: none;">
            <div
                class="absolute inset-0 bg-slate-900/40"
                x-show="selecionado"
                x-transition.opacity
                @click="fechar()"
            ></div>

            <div
                x-show="selecionado"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-y-full sm:translate-y-0 sm:translate-x-full"
                x-transition:enter-end="translate-y-0 sm:translate-x-0"
                class="absolute bottom-0 sm:top-0 sm:right-0 w-full sm:w-96 h-[90vh] sm:h-full bg-white shadow-popover rounded-t-2xl sm:rounded-none overflow-y-auto scrollbar-thin"
                @click.stop
            >
                <template x-if="selecionado">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-start justify-between mb-1">
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold"
                                    :class="avatarBadge(selecionado.nome)"
                                    x-text="iniciais(selecionado.nome)"
                                ></div>
                                <div class="min-w-0">
                                    <h3 class="text-lg font-semibold text-slate-900 truncate" x-text="selecionado.nome"></h3>
                                    <p class="text-sm text-slate-500 truncate" x-text="selecionado.segmento"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0 ml-2">
                                <button
                                    @click="excluirLead()"
                                    :disabled="excluindo"
                                    title="Excluir lead"
                                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 disabled:opacity-50"
                                >
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                                <button @click="fechar()" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <span
                            class="mt-2 inline-block rounded-full px-2 py-0.5 text-xs font-semibold"
                            :class="statusBadge(selecionado.status)"
                            x-text="statusLabel(selecionado.status)"
                        ></span>

                        {{-- Dados do lead (editável, pra corrigir typos vindos do formulário público) --}}
                        <div class="mt-4 border-t border-slate-100 pt-4" x-data="{ editando: false }">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="field-label">Dados do lead</h4>
                                <button
                                    @click="editando = !editando"
                                    class="text-xs font-semibold text-violet-600 hover:text-violet-700"
                                    x-text="editando ? 'cancelar' : 'editar'"
                                ></button>
                            </div>

                            <template x-if="!editando">
                                <p class="text-sm">
                                    <span class="text-slate-400">Telefone:</span>
                                    <a :href="waLink(selecionado.telefone)" target="_blank" rel="noopener" class="ml-1 font-medium text-emerald-600" x-text="selecionado.telefone"></a>
                                </p>
                            </template>

                            <template x-if="editando">
                                <div class="space-y-2">
                                    <input type="text" x-model="nomeEdit" placeholder="Nome" class="field-input">
                                    <input type="text" x-model="telefoneEdit" placeholder="Telefone" class="field-input">
                                    <input type="text" x-model="segmentoEdit" placeholder="Segmento" class="field-input">
                                    <button
                                        @click="salvarDados()"
                                        :disabled="salvandoDados"
                                        class="btn-primary w-full"
                                    >
                                        Salvar dados
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="space-y-1.5 text-sm mt-4 mb-4">
                            <p x-show="selecionado.origem">
                                <span class="text-slate-400">Origem:</span>
                                <span class="ml-1 font-medium text-slate-700" x-text="selecionado.origem"></span>
                            </p>
                            <p>
                                <span class="text-slate-400">Último contato:</span>
                                <span class="ml-1 font-medium text-slate-700" x-text="selecionado.tempo_desde_ultimo_contato ?? '—'"></span>
                            </p>
                            <p x-show="selecionado.observacoes">
                                <span class="text-slate-400">Observações:</span>
                                <span class="ml-1 font-medium text-slate-700" x-text="selecionado.observacoes"></span>
                            </p>
                        </div>

                        {{-- Valor estimado --}}
                        <div class="mb-5 border-t border-slate-100 pt-4">
                            <p class="field-label mb-1.5">Valor estimado (R$)</p>
                            <div class="flex gap-2">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    x-model="novoValor"
                                    class="field-input flex-1"
                                >
                                <button
                                    @click="salvarValor()"
                                    :disabled="salvandoValor"
                                    class="btn-primary"
                                >
                                    Salvar
                                </button>
                            </div>
                        </div>

                        {{-- Nova nota --}}
                        <div class="mb-5 border-t border-slate-100 pt-4">
                            <p class="field-label mb-1.5">Nova nota</p>
                            <textarea
                                x-model="novaNota"
                                rows="2"
                                placeholder="Ex: cliente pediu retorno amanhã..."
                                class="field-input"
                            ></textarea>
                            <button
                                @click="salvarNota()"
                                :disabled="salvandoNota || !novaNota.trim()"
                                class="btn-secondary mt-2 w-full"
                            >
                                Adicionar nota
                            </button>
                        </div>

                        {{-- Histórico --}}
                        <div class="border-t border-slate-100 pt-4">
                            <p class="field-label mb-2">Histórico de notas</p>
                            <div class="space-y-2">
                                <template x-for="note in selecionado.notes" :key="note.id">
                                    <div class="rounded-lg bg-slate-50 p-2.5 text-sm">
                                        <p class="text-slate-700" x-text="note.texto"></p>
                                        <p class="text-[11px] text-slate-400 mt-1" x-text="note.created_at"></p>
                                    </div>
                                </template>
                                <p x-show="!selecionado.notes.length" class="text-xs text-slate-400">
                                    Nenhuma nota ainda.
                                </p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Modal: novo lead --}}
        <div x-show="criarAberto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div
                class="absolute inset-0 bg-slate-900/40"
                x-show="criarAberto"
                x-transition.opacity
                @click="fecharCriacao()"
            ></div>

            <div
                x-show="criarAberto"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="relative w-full max-w-md rounded-2xl bg-white p-5 shadow-popover sm:p-6"
                @click.stop
            >
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Novo lead</h3>
                        <p class="mt-0.5 text-sm text-slate-500">Cadastro manual — pra contatos que chegaram por outro canal.</p>
                    </div>
                    <button @click="fecharCriacao()" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="field-label mb-1">Nome</label>
                        <input type="text" x-model="novoLead.nome" placeholder="Nome do lead" class="field-input">
                    </div>
                    <div>
                        <label class="field-label mb-1">Telefone</label>
                        <input type="text" x-model="novoLead.telefone" placeholder="(11) 91234-5678" class="field-input">
                    </div>
                    <div>
                        <label class="field-label mb-1">Segmento</label>
                        <input type="text" x-model="novoLead.segmento" placeholder="Ex: Salão de beleza" class="field-input">
                    </div>
                    <div>
                        <label class="field-label mb-1">Origem <span class="normal-case text-slate-400">(opcional)</span></label>
                        <input type="text" x-model="novoLead.origem" placeholder="manual" class="field-input">
                    </div>
                    <div>
                        <label class="field-label mb-1">Valor estimado (R$) <span class="normal-case text-slate-400">(opcional)</span></label>
                        <input type="number" step="0.01" min="0" x-model="novoLead.valor_estimado" class="field-input">
                    </div>
                </div>

                <div class="mt-5 flex gap-2">
                    <button @click="fecharCriacao()" class="btn-secondary flex-1">Cancelar</button>
                    <button @click="criarLead()" :disabled="salvandoCriacao" class="btn-primary flex-1">
                        <span x-show="!salvandoCriacao">Criar lead</span>
                        <span x-show="salvandoCriacao">Salvando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
