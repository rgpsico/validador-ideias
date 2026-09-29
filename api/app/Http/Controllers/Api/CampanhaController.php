<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\QuestionRule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CampanhaController extends Controller
{
    /**
     * GET /api/campanhas/{slug}
     * Public, no auth. Never leaks `pontos` or any Lead scoring data.
     */
    public function show(string $slug): JsonResponse
    {
        $campaign = Campaign::query()
            ->where('slug', $slug)
            ->where('ativo', true)
            ->with(['questions' => fn ($q) => $q->orderBy('ordem')])
            ->first();

        if (!$campaign) {
            return response()->json(['message' => 'Campanha não encontrada.'], 404);
        }

        $questionIds = $campaign->questions->pluck('id');

        $rules = QuestionRule::query()
            ->whereIn('question_id', $questionIds)
            ->get(['id', 'question_id', 'depends_on_question_id', 'operator', 'value']);

        return response()->json([
            'titulo' => $campaign->titulo,
            'descricao' => $campaign->descricao,
            'formato' => $campaign->formato,
            'slug' => $campaign->slug,
            'pixel_id' => $campaign->pixel_id,
            'questions' => $campaign->questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'tipo' => $q->tipo,
                    'texto' => $q->texto,
                    'ordem' => $q->ordem,
                    'obrigatoria' => $q->obrigatoria,
                    // Strip `pontos` — that's internal scoring, never exposed publicly.
                    'opcoes' => $q->opcoes ? array_map(fn ($o) => [
                        'label' => $o['label'] ?? null,
                        'value' => $o['value'] ?? null,
                    ], $q->opcoes) : null,
                    'config' => $q->config,
                ];
            })->values(),
            'rules' => $rules->map(fn ($r) => [
                'question_id' => $r->question_id,
                'depends_on_question_id' => $r->depends_on_question_id,
                'operator' => $r->operator,
                'value' => $r->value,
            ])->values(),
        ]);
    }

    /**
     * POST /api/campanhas/{slug}/iniciar
     */
    public function iniciar(Request $request, string $slug): JsonResponse
    {
        $campaign = Campaign::query()->where('slug', $slug)->where('ativo', true)->first();

        if (!$campaign) {
            return response()->json(['message' => 'Campanha não encontrada.'], 404);
        }

        $lead = Lead::create([
            'campaign_id' => $campaign->id,
            'started_at' => now(),
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return response()->json(['lead_uuid' => $lead->uuid], 201);
    }
}
