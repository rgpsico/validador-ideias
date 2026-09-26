<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionRule extends Model
{
    const OPERATORS = [
        'equals' => 'Igual a',
        'not_equals' => 'Diferente de',
        'in' => 'Está entre (lista)',
    ];

    protected $fillable = [
        'question_id',
        'depends_on_question_id',
        'operator',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'depends_on_question_id');
    }

    /**
     * Evaluate this rule against an already-given answer value.
     */
    public function matches(mixed $answerValue): bool
    {
        $expected = $this->value;
        $expectedScalar = is_array($expected) ? ($expected[0] ?? null) : $expected;

        $actual = is_array($answerValue) ? ($answerValue[0] ?? null) : $answerValue;
        $actual = is_string($actual) ? mb_strtolower(trim($actual)) : $actual;

        return match ($this->operator) {
            'equals' => $actual == (is_string($expectedScalar) ? mb_strtolower(trim($expectedScalar)) : $expectedScalar),
            'not_equals' => $actual != (is_string($expectedScalar) ? mb_strtolower(trim($expectedScalar)) : $expectedScalar),
            'in' => in_array(
                $actual,
                array_map(fn ($v) => is_string($v) ? mb_strtolower(trim($v)) : $v, is_array($expected) ? $expected : [$expected]),
                false
            ),
            default => false,
        };
    }
}
