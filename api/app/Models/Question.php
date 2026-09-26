<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    const TIPOS = [
        'texto' => 'Texto curto',
        'multipla_escolha' => 'Múltipla escolha',
        'sim_nao' => 'Sim / Não',
        'numero' => 'Número',
        'escala' => 'Escala (1-10)',
        'moeda' => 'Moeda (opções de valor)',
        'texto_livre' => 'Texto livre (parágrafo)',
        'nome' => 'Nome (contato)',
        'whatsapp' => 'WhatsApp (contato)',
        'email' => 'E-mail (contato)',
    ];

    const TIPOS_COM_OPCOES = ['multipla_escolha', 'sim_nao', 'moeda'];

    protected $fillable = [
        'campaign_id',
        'tipo',
        'texto',
        'ordem',
        'obrigatoria',
        'opcoes',
        'config',
    ];

    protected $casts = [
        'obrigatoria' => 'boolean',
        'opcoes' => 'array',
        'config' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(QuestionRule::class);
    }

    public function dependentRules(): HasMany
    {
        return $this->hasMany(QuestionRule::class, 'depends_on_question_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Leads currently "stuck" on this question — a rough drop-off proxy
     * (Lead.current_question_id points here and they never advanced further).
     */
    public function leadsParadosAqui(): HasMany
    {
        return $this->hasMany(Lead::class, 'current_question_id');
    }

    /**
     * Resolve how many points a given raw answer value earns for this question,
     * based on its `opcoes` config. Never trust client-sent points.
     */
    public function resolvePontos(mixed $value): int
    {
        if (empty($this->opcoes) || !is_array($this->opcoes)) {
            return 0;
        }

        $needle = is_array($value) ? ($value[0] ?? null) : $value;

        foreach ($this->opcoes as $opcao) {
            if ((string) ($opcao['value'] ?? null) === (string) $needle) {
                return (int) ($opcao['pontos'] ?? 0);
            }
        }

        return 0;
    }
}
