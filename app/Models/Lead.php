<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable([
    'nome',
    'telefone',
    'segmento',
    'origem',
    'status',
    'valor_estimado',
    'observacoes',
    'ultimo_contato_em',
])]
#[Appends(['tempo_desde_ultimo_contato'])]
class Lead extends Model
{
    use HasFactory;

    /**
     * Status possíveis do funil de leads, na ordem do Kanban.
     */
    public const STATUSES = ['novo', 'contatado', 'negociando', 'fechado', 'perdido'];

    /**
     * Rótulo de exibição de cada status.
     */
    public const STATUS_LABELS = [
        'novo' => 'Novo',
        'contatado' => 'Contatado',
        'negociando' => 'Negociando',
        'fechado' => 'Fechado',
        'perdido' => 'Perdido',
    ];

    /**
     * Classes Tailwind (badge) associadas a cada status do funil — usadas na
     * sidebar e no Kanban (Blade). Ficam aqui, por extenso, porque o Tailwind
     * escaneia texto literal: strings montadas em runtime (ex: "bg-{$cor}-500")
     * não seriam detectadas e sumiriam do CSS final.
     */
    public const STATUS_BADGE_CLASSES = [
        'novo' => 'bg-slate-100 text-slate-600',
        'contatado' => 'bg-sky-100 text-sky-700',
        'negociando' => 'bg-amber-100 text-amber-700',
        'fechado' => 'bg-emerald-100 text-emerald-700',
        'perdido' => 'bg-rose-100 text-rose-700',
    ];

    /**
     * Classes Tailwind do "ponto" indicador de status (sidebar, cabeçalho das colunas).
     */
    public const STATUS_DOT_CLASSES = [
        'novo' => 'bg-slate-400',
        'contatado' => 'bg-sky-500',
        'negociando' => 'bg-amber-500',
        'fechado' => 'bg-emerald-500',
        'perdido' => 'bg-rose-500',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valor_estimado' => 'decimal:2',
            'ultimo_contato_em' => 'datetime',
        ];
    }

    /**
     * Notas de interação registradas para este lead.
     *
     * @return HasMany<LeadNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class);
    }

    /**
     * Filtra leads por status.
     *
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopePorStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Números do funil usados no Dashboard e no cabeçalho do Kanban.
     *
     * @return array{total: int, novos_semana: int, valor_em_negociacao: float, taxa_fechamento: ?int, por_status: Collection<string, int>}
     */
    public static function funilStats(): array
    {
        $porStatus = self::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $total = $porStatus->sum();
        $fechados = $porStatus->get('fechado', 0);
        $perdidos = $porStatus->get('perdido', 0);
        $finalizados = $fechados + $perdidos;

        return [
            'total' => $total,
            'novos_semana' => self::query()->where('created_at', '>=', now()->startOfWeek())->count(),
            'valor_em_negociacao' => (float) self::query()->where('status', 'negociando')->sum('valor_estimado'),
            'taxa_fechamento' => $finalizados > 0 ? (int) round(($fechados / $finalizados) * 100) : null,
            'por_status' => $porStatus,
        ];
    }

    /**
     * Tempo relativo desde o último contato (ex: "há 3 dias").
     * Cai para o tempo desde a criação quando ainda não houve contato.
     */
    protected function tempoDesdeUltimoContato(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->ultimo_contato_em
                ? $this->ultimo_contato_em->diffForHumans()
                : $this->created_at?->diffForHumans(),
        );
    }
}
