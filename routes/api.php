<?php

use App\Http\Controllers\Api\LeadController;
use Illuminate\Support\Facades\Route;

// Endpoint público, chamado via fetch() pela landing page (domínio externo).
// Sem autenticação — protegido apenas por rate limiting (5 req/min/IP).
Route::post('/leads', [LeadController::class, 'store'])
    ->middleware('throttle:5,1');
