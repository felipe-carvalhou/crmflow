<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;

class LeadController extends Controller
{
    /**
     * Recebe um lead da landing page (endpoint público, sem autenticação).
     */
    public function store(StoreLeadRequest $request): JsonResponse
    {
        $lead = Lead::create($request->validated());

        return response()->json([
            'success' => true,
            'id' => $lead->id,
        ], 201);
    }
}
