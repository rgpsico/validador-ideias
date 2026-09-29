<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Lead;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    /**
     * POST /api/leads/{uuid}/responder
     * Body: { question_id, value }
     * Upserts the Answer, resolves `pontos` server-side (client points are never trusted),
     * and advances Lead.current_question_id.
     */
    public function responder(Request $request, string $uuid): JsonResponse
    {
        $lead = Lead::query()->where('uuid', $uuid)->first();

        if (!$lead) {
            return response()->json(['message' => 'Lead não encontrado.'], 404);
        }

        $data = $request->validate([
            'question_id' => ['required', Rule::exists('questions', 'id')->where('campaign_id', $lead->campaign_id)],
            'value' => ['required'],
        ]);

        $question = Question::findOrFail($data['question_id']);
        $pontos = $question->resolvePontos($data['value']);

        Answer::updateOrCreate(
            ['lead_id' => $lead->id, 'question_id' => $question->id],
            ['value' => $data['value'], 'pontos' => $pontos]
        );

        $lead->update(['current_question_id' => $question->id]);

        return response()->json(null, 204);
    }

    /**
     * POST /api/leads/{uuid}/finalizar
     * Body (optional): { nome, whatsapp, email }
     * Stamps completed_at, computes final score/classificacao from the sum of Answers,
     * and copies contact info (from the body, or from matching Answers) into the Lead row.
     */
    public function finalizar(Request $request, string $uuid): JsonResponse
    {
        $lead = Lead::query()->where('uuid', $uuid)->with(['answers.question', 'campaign'])->first();

        if (!$lead) {
            return response()->json(['message' => 'Lead não encontrado.'], 404);
        }

        $data = $request->validate([
            'nome' => ['sometimes', 'nullable', 'string', 'max:255'],
            'whatsapp' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $score = (int) $lead->answers->sum('pontos');
        $score = max(0, min(100, $score));

        $contact = ['nome' => null, 'whatsapp' => null, 'email' => null];
        foreach ($lead->answers as $answer) {
            $tipo = $answer->question?->tipo;
            if (in_array($tipo, ['nome', 'whatsapp', 'email'], true)) {
                $value = $answer->value;
                $contact[$tipo] = is_array($value) ? ($value[0] ?? null) : $value;
            }
        }

        $lead->update([
            'completed_at' => now(),
            'score' => $score,
            'classificacao' => Lead::classificarPorScore($score),
            'nome' => $data['nome'] ?? $contact['nome'] ?? $lead->nome,
            'whatsapp' => $data['whatsapp'] ?? $contact['whatsapp'] ?? $lead->whatsapp,
            'email' => $data['email'] ?? $contact['email'] ?? $lead->email,
        ]);

        $this->notificarWebhook($lead);

        return response()->json(['message' => 'Concluído.'], 200);
    }

    /**
     * Se a campanha tem um webhook configurado e o lead deixou nome+whatsapp
     * (ou seja, topou ser contatado), envia os dados pra fora. Nunca falha a
     * requisição do respondente por causa disso.
     */
    private function notificarWebhook(Lead $lead): void
    {
        $url = $lead->campaign?->webhook_url;

        if (!$url || !$lead->nome || !$lead->whatsapp) {
            return;
        }

        try {
            Http::timeout(5)
                ->withHeaders(['X-Webhook-Secret' => config('services.validador_webhook.secret')])
                ->post($url, [
                    'campaign_slug' => $lead->campaign->slug,
                    'campaign_titulo' => $lead->campaign->titulo,
                    'lead_uuid' => $lead->uuid,
                    'nome' => $lead->nome,
                    'whatsapp' => $lead->whatsapp,
                    'email' => $lead->email,
                    'score' => $lead->score,
                    'classificacao' => $lead->classificacao,
                    'respostas' => $lead->answers->map(fn ($a) => [
                        'pergunta' => $a->question->texto,
                        'resposta' => $a->value,
                    ])->values(),
                ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao notificar webhook do validador', [
                'lead_id' => $lead->id,
                'url' => $url,
                'erro' => $e->getMessage(),
            ]);
        }
    }
}
