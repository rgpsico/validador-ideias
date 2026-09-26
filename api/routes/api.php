<?php

use App\Http\Controllers\Api\CampanhaController;
use App\Http\Controllers\Api\LeadController;
use Illuminate\Support\Facades\Route;

Route::get('/campanhas/{slug}', [CampanhaController::class, 'show']);
Route::post('/campanhas/{slug}/iniciar', [CampanhaController::class, 'iniciar']);
Route::post('/leads/{uuid}/responder', [LeadController::class, 'responder']);
Route::post('/leads/{uuid}/finalizar', [LeadController::class, 'finalizar']);
