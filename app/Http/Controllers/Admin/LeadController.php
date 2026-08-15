<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LeadController extends Controller
{
    /**
     * Board Kanban com todos os leads agrupados por status.
     * Filtro por origem e busca acontecem no cliente (Alpine.js),
     * já que o volume de leads é pequeno o suficiente pra carregar tudo de uma vez.
     * O ?status= (usado pelos atalhos da sidebar) só define o estado inicial do
     * filtro no board — a filtragem em si também acontece no cliente.
     */
    public function index(Request $request): View
    {
        $leads = Lead::with(['notes' => fn ($query) => $query->latest('created_at')])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Lead $lead) => [
                'id' => $lead->id,
                'nome' => $lead->nome,
                'telefone' => $lead->telefone,
                'segmento' => $lead->segmento,
                'origem' => $lead->origem,
                'status' => $lead->status,
                'valor_estimado' => $lead->valor_estimado,
                'observacoes' => $lead->observacoes,
                'criado_em' => $lead->created_at->diffForHumans(),
                'tempo_desde_ultimo_contato' => $lead->tempo_desde_ultimo_contato,
                'notes' => $lead->notes->map(fn (LeadNote $note) => [
                    'id' => $note->id,
                    'texto' => $note->texto,
                    'created_at' => $note->created_at->diffForHumans(),
                ])->all(),
            ])
            ->values();

        $origens = Lead::query()
            ->whereNotNull('origem')
            ->distinct()
            ->orderBy('origem')
            ->pluck('origem');

        $statusFiltro = $request->string('status')->value();

        return view('admin.leads.index', [
            'leads' => $leads,
            'statuses' => Lead::STATUSES,
            'origens' => $origens,
            'stats' => Lead::funilStats(),
            'statusFiltroInicial' => in_array($statusFiltro, Lead::STATUSES, true) ? $statusFiltro : null,
        ]);
    }

    /**
     * Cria um lead manualmente pelo Kanban (ex: contato que chegou por outro canal).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome' => ['required', 'string', 'min:2', 'max:255'],
            'telefone' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+?(55)?\s?\(?\d{2}\)?\s?9?\d{4}-?\d{4}$/',
            ],
            'segmento' => ['required', 'string', 'max:255'],
            'origem' => ['nullable', 'string', 'max:255'],
            'valor_estimado' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $dados = $validator->validated();
        $dados['origem'] = $dados['origem'] ?? 'manual';

        // refresh() pra trazer de volta o `status` default definido na migration
        // (o insert não devolve as colunas com valor padrão do banco).
        $lead = Lead::create($dados)->refresh();

        return response()->json([
            'success' => true,
            'lead' => [
                'id' => $lead->id,
                'nome' => $lead->nome,
                'telefone' => $lead->telefone,
                'segmento' => $lead->segmento,
                'origem' => $lead->origem,
                'status' => $lead->status,
                'valor_estimado' => $lead->valor_estimado,
                'observacoes' => $lead->observacoes,
                'criado_em' => $lead->created_at->diffForHumans(),
                'tempo_desde_ultimo_contato' => $lead->tempo_desde_ultimo_contato,
                'notes' => [],
            ],
        ], 201);
    }

    /**
     * Atualiza o status de um lead (usado pelo drag-and-drop do Kanban).
     */
    public function updateStatus(Request $request, Lead $lead): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'string', 'in:'.implode(',', Lead::STATUSES)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('status'),
            ], 422);
        }

        $lead->update([
            'status' => $request->string('status')->value(),
            'ultimo_contato_em' => now(),
        ]);

        return response()->json([
            'success' => true,
            'status' => $lead->status,
            'tempo_desde_ultimo_contato' => $lead->tempo_desde_ultimo_contato,
        ]);
    }

    /**
     * Atualiza os dados básicos do lead (corrige typos vindos do formulário público).
     */
    public function update(Request $request, Lead $lead): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome' => ['required', 'string', 'min:2', 'max:255'],
            'telefone' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+?(55)?\s?\(?\d{2}\)?\s?9?\d{4}-?\d{4}$/',
            ],
            'segmento' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $lead->update($validator->validated());

        return response()->json([
            'success' => true,
            'lead' => [
                'nome' => $lead->nome,
                'telefone' => $lead->telefone,
                'segmento' => $lead->segmento,
            ],
        ]);
    }

    /**
     * Remove um lead (e suas notas, em cascata).
     */
    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Atualiza o valor estimado do lead.
     */
    public function updateValor(Request $request, Lead $lead): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'valor_estimado' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('valor_estimado'),
            ], 422);
        }

        $lead->update([
            'valor_estimado' => $request->input('valor_estimado'),
        ]);

        return response()->json([
            'success' => true,
            'valor_estimado' => $lead->valor_estimado,
        ]);
    }
}
