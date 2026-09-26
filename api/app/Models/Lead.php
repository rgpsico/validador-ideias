<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Lead extends Model
{
    use HasFactory;

    const CLASSIFICACOES = ['curioso', 'potencial', 'qualificado', 'quente'];

    protected $fillable = [
        'uuid',
        'campaign_id',
        'nome',
        'whatsapp',
        'email',
        'score',
        'classificacao',
        'started_at',
        'completed_at',
        'current_question_id',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Lead $lead) {
            if (empty($lead->uuid)) {
                $lead->uuid = (string) Str::uuid();
            }
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function currentQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'current_question_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public static function classificarPorScore(int $score): string
    {
        return match (true) {
            $score >= 80 => 'quente',
            $score >= 60 => 'qualificado',
            $score >= 30 => 'potencial',
            default => 'curioso',
        };
    }
}
