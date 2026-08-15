const STATUS_LABELS = {
    novo: 'Novo',
    contatado: 'Contatado',
    negociando: 'Negociando',
    fechado: 'Fechado',
    perdido: 'Perdido',
};

// Espelha App\Models\Lead::STATUS_DOT_CLASSES / STATUS_BADGE_CLASSES — mantidos
// em sincronia manualmente, já que um lado é PHP (sidebar) e o outro JS (board).
const STATUS_DOT_CLASSES = {
    novo: 'bg-slate-400',
    contatado: 'bg-sky-500',
    negociando: 'bg-amber-500',
    fechado: 'bg-emerald-500',
    perdido: 'bg-rose-500',
};

const STATUS_BADGE_CLASSES = {
    novo: 'bg-slate-100 text-slate-600',
    contatado: 'bg-sky-100 text-sky-700',
    negociando: 'bg-amber-100 text-amber-700',
    fechado: 'bg-emerald-100 text-emerald-700',
    perdido: 'bg-rose-100 text-rose-700',
};

// Paletas usadas pros badges de origem e avatares — presas aqui por extenso
// (classes completas) porque o Tailwind escaneia o texto do arquivo em busca
// de nomes de classe literais; strings montadas em runtime não seriam achadas.
const ORIGEM_PALETTE = [
    'bg-violet-50 text-violet-700',
    'bg-sky-50 text-sky-700',
    'bg-amber-50 text-amber-700',
    'bg-emerald-50 text-emerald-700',
    'bg-rose-50 text-rose-700',
    'bg-cyan-50 text-cyan-700',
    'bg-fuchsia-50 text-fuchsia-700',
    'bg-orange-50 text-orange-700',
];

const AVATAR_PALETTE = [
    'bg-violet-100 text-violet-700',
    'bg-sky-100 text-sky-700',
    'bg-amber-100 text-amber-700',
    'bg-emerald-100 text-emerald-700',
    'bg-rose-100 text-rose-700',
    'bg-cyan-100 text-cyan-700',
    'bg-fuchsia-100 text-fuchsia-700',
    'bg-orange-100 text-orange-700',
];

function hashString(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        hash = (hash * 31 + str.charCodeAt(i)) >>> 0;
    }
    return hash;
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function jsonHeaders() {
    return {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
    };
}

export default function leadsBoard(initialLeads, statuses, initialStatusFiltro) {
    return {
        leads: initialLeads,
        statuses,
        search: '',
        origemFiltro: '',
        statusFiltro: initialStatusFiltro || null,
        selecionadoId: null,
        novaNota: '',
        novoValor: '',
        nomeEdit: '',
        telefoneEdit: '',
        segmentoEdit: '',
        salvandoNota: false,
        salvandoValor: false,
        salvandoDados: false,
        excluindo: false,
        erro: '',
        criarAberto: false,
        salvandoCriacao: false,
        novoLead: { nome: '', telefone: '', segmento: '', origem: '', valor_estimado: '' },
        dragId: null,
        dragOverStatus: null,

        statusLabel(status) {
            return STATUS_LABELS[status] ?? status;
        },

        statusDot(status) {
            return STATUS_DOT_CLASSES[status] ?? 'bg-slate-400';
        },

        statusBadge(status) {
            return STATUS_BADGE_CLASSES[status] ?? 'bg-slate-100 text-slate-600';
        },

        origemBadge(origem) {
            if (!origem) return 'bg-slate-100 text-slate-500';
            return ORIGEM_PALETTE[hashString(origem) % ORIGEM_PALETTE.length];
        },

        avatarBadge(nome) {
            return AVATAR_PALETTE[hashString(nome ?? '') % AVATAR_PALETTE.length];
        },

        iniciais(nome) {
            if (!nome) return '?';
            const partes = nome.trim().split(/\s+/);
            const primeira = partes[0]?.[0] ?? '';
            const ultima = partes.length > 1 ? partes[partes.length - 1][0] : '';
            return (primeira + ultima).toUpperCase();
        },

        get filtrados() {
            const termo = this.search.trim().toLowerCase();

            return this.leads.filter((lead) => {
                const bateOrigem = !this.origemFiltro || lead.origem === this.origemFiltro;
                const bateStatus = !this.statusFiltro || lead.status === this.statusFiltro;
                const bateBusca =
                    !termo ||
                    lead.nome.toLowerCase().includes(termo) ||
                    (lead.telefone ?? '').toLowerCase().includes(termo);

                return bateOrigem && bateStatus && bateBusca;
            });
        },

        porStatus(status) {
            return this.filtrados.filter((lead) => lead.status === status);
        },

        // Quando chega pela sidebar com ?status=negociando, mostra só aquela coluna
        // (essencial no celular, onde rolar as 5 colunas é mais custoso).
        get colunasVisiveis() {
            return this.statusFiltro ? this.statuses.filter((s) => s === this.statusFiltro) : this.statuses;
        },

        limparFiltroStatus() {
            this.statusFiltro = null;
            const url = new URL(window.location.href);
            url.searchParams.delete('status');
            window.history.replaceState({}, '', url);
        },

        get selecionado() {
            return this.leads.find((lead) => lead.id === this.selecionadoId) ?? null;
        },

        waLink(telefone) {
            const digitos = (telefone ?? '').replace(/\D/g, '');
            const comDdi = digitos.startsWith('55') ? digitos : `55${digitos}`;

            return `https://wa.me/${comDdi}`;
        },

        formatarMoeda(valor) {
            if (valor === null || valor === undefined || valor === '') return null;

            return Number(valor).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL',
            });
        },

        // --- Drag and drop (desktop/mouse) ---

        onDragStart(lead) {
            this.dragId = lead.id;
        },

        onDragOverColumn(status) {
            this.dragOverStatus = status;
        },

        onDropColumn(status) {
            this.dragOverStatus = null;
            if (this.dragId === null) return;

            this.mudarStatus(this.dragId, status);
            this.dragId = null;
        },

        // --- Select de status (funciona em qualquer dispositivo, inclusive celular) ---

        onSelecionarStatus(lead, event) {
            this.mudarStatus(lead.id, event.target.value);
        },

        mudarStatus(leadId, novoStatus) {
            const lead = this.leads.find((l) => l.id === leadId);
            if (!lead || lead.status === novoStatus) return;

            const statusAnterior = lead.status;
            lead.status = novoStatus; // otimista

            fetch(`/admin/leads/${leadId}/status`, {
                method: 'PATCH',
                headers: jsonHeaders(),
                body: JSON.stringify({ status: novoStatus }),
            })
                .then((r) => r.json())
                .then((data) => {
                    if (!data.success) {
                        lead.status = statusAnterior;
                        this.erro = data.message ?? 'Erro ao atualizar status.';
                        return;
                    }
                    lead.tempo_desde_ultimo_contato = data.tempo_desde_ultimo_contato;
                })
                .catch(() => {
                    lead.status = statusAnterior;
                    this.erro = 'Erro de conexão ao atualizar status.';
                });
        },

        // --- Painel lateral ---

        abrir(lead) {
            this.selecionadoId = lead.id;
            this.novoValor = lead.valor_estimado ?? '';
            this.nomeEdit = lead.nome;
            this.telefoneEdit = lead.telefone;
            this.segmentoEdit = lead.segmento;
            this.novaNota = '';
            this.erro = '';
        },

        fechar() {
            this.selecionadoId = null;
        },

        salvarNota() {
            const lead = this.selecionado;
            if (!lead || !this.novaNota.trim()) return;

            this.salvandoNota = true;

            fetch(`/admin/leads/${lead.id}/notes`, {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify({ texto: this.novaNota }),
            })
                .then((r) => r.json())
                .then((data) => {
                    if (!data.success) {
                        this.erro = data.message ?? 'Erro ao salvar nota.';
                        return;
                    }
                    lead.notes.unshift(data.note);
                    lead.tempo_desde_ultimo_contato = data.tempo_desde_ultimo_contato;
                    this.novaNota = '';
                })
                .catch(() => {
                    this.erro = 'Erro de conexão ao salvar nota.';
                })
                .finally(() => {
                    this.salvandoNota = false;
                });
        },

        salvarDados() {
            const lead = this.selecionado;
            if (!lead) return;

            this.salvandoDados = true;

            fetch(`/admin/leads/${lead.id}`, {
                method: 'PATCH',
                headers: jsonHeaders(),
                body: JSON.stringify({
                    nome: this.nomeEdit,
                    telefone: this.telefoneEdit,
                    segmento: this.segmentoEdit,
                }),
            })
                .then((r) => r.json())
                .then((data) => {
                    if (!data.success) {
                        this.erro = data.message ?? 'Erro ao salvar dados do lead.';
                        return;
                    }
                    lead.nome = data.lead.nome;
                    lead.telefone = data.lead.telefone;
                    lead.segmento = data.lead.segmento;
                })
                .catch(() => {
                    this.erro = 'Erro de conexão ao salvar dados do lead.';
                })
                .finally(() => {
                    this.salvandoDados = false;
                });
        },

        excluirLead() {
            const lead = this.selecionado;
            if (!lead) return;

            if (!confirm(`Tem certeza que quer excluir o lead "${lead.nome}"? Essa ação não pode ser desfeita.`)) {
                return;
            }

            this.excluindo = true;

            fetch(`/admin/leads/${lead.id}`, {
                method: 'DELETE',
                headers: jsonHeaders(),
            })
                .then((r) => r.json())
                .then((data) => {
                    if (!data.success) {
                        this.erro = data.message ?? 'Erro ao excluir lead.';
                        return;
                    }
                    this.leads = this.leads.filter((l) => l.id !== lead.id);
                    this.fechar();
                })
                .catch(() => {
                    this.erro = 'Erro de conexão ao excluir lead.';
                })
                .finally(() => {
                    this.excluindo = false;
                });
        },

        salvarValor() {
            const lead = this.selecionado;
            if (!lead) return;

            this.salvandoValor = true;

            fetch(`/admin/leads/${lead.id}/valor`, {
                method: 'PATCH',
                headers: jsonHeaders(),
                body: JSON.stringify({ valor_estimado: this.novoValor === '' ? null : this.novoValor }),
            })
                .then((r) => r.json())
                .then((data) => {
                    if (!data.success) {
                        this.erro = data.message ?? 'Erro ao salvar valor.';
                        return;
                    }
                    lead.valor_estimado = data.valor_estimado;
                })
                .catch(() => {
                    this.erro = 'Erro de conexão ao salvar valor.';
                })
                .finally(() => {
                    this.salvandoValor = false;
                });
        },

        // --- Criação manual de lead ---

        abrirCriacao() {
            this.novoLead = { nome: '', telefone: '', segmento: '', origem: '', valor_estimado: '' };
            this.erro = '';
            this.criarAberto = true;
        },

        fecharCriacao() {
            this.criarAberto = false;
        },

        criarLead() {
            if (!this.novoLead.nome.trim() || !this.novoLead.telefone.trim() || !this.novoLead.segmento.trim()) {
                this.erro = 'Preencha nome, telefone e segmento.';
                return;
            }

            this.salvandoCriacao = true;

            fetch('/admin/leads', {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify({
                    nome: this.novoLead.nome,
                    telefone: this.novoLead.telefone,
                    segmento: this.novoLead.segmento,
                    origem: this.novoLead.origem || null,
                    valor_estimado: this.novoLead.valor_estimado === '' ? null : this.novoLead.valor_estimado,
                }),
            })
                .then((r) => r.json())
                .then((data) => {
                    if (!data.success) {
                        this.erro = data.message ?? 'Erro ao criar lead.';
                        return;
                    }
                    this.leads.unshift(data.lead);
                    this.criarAberto = false;
                })
                .catch(() => {
                    this.erro = 'Erro de conexão ao criar lead.';
                })
                .finally(() => {
                    this.salvandoCriacao = false;
                });
        },
    };
}
