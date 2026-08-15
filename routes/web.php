<?php

use App\Http\Controllers\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Admin\LeadNoteController as AdminLeadNoteController;
use App\Http\Controllers\ProfileController;
use App\Models\Lead;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard', ['stats' => Lead::funilStats()]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/leads', [AdminLeadController::class, 'index'])->name('leads.index');
    Route::post('/leads', [AdminLeadController::class, 'store'])->name('leads.store');
    Route::patch('/leads/{lead}/status', [AdminLeadController::class, 'updateStatus'])->name('leads.status');
    Route::patch('/leads/{lead}/valor', [AdminLeadController::class, 'updateValor'])->name('leads.valor');
    Route::patch('/leads/{lead}', [AdminLeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [AdminLeadController::class, 'destroy'])->name('leads.destroy');
    Route::post('/leads/{lead}/notes', [AdminLeadNoteController::class, 'store'])->name('leads.notes.store');
});

require __DIR__.'/auth.php';
