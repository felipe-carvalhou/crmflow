<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadNoteController extends Controller
{
    /**
     * Adiciona uma nota rápida ao histórico de interações do lead.
     */
    public function store(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'texto' => ['required', 'string', 'max:2000'],
        ]);

        $note = $lead->notes()->create($validated);

        $lead->update(['ultimo_contato_em' => now()]);

        return response()->json([
            'success' => true,
            'note' => [
                'id' => $note->id,
                'texto' => $note->texto,
                'created_at' => $note->created_at->diffForHumans(),
            ],
            'tempo_desde_ultimo_contato' => $lead->fresh()->tempo_desde_ultimo_contato,
        ], 201);
    }
}
