<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('tipo'); // texto, multipla_escolha, sim_nao, numero, escala, moeda, texto_livre, nome, whatsapp, email
            $table->text('texto');
            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('obrigatoria')->default(true);
            $table->json('opcoes')->nullable(); // [{label, value, pontos}] for choice-based types
            $table->json('config')->nullable(); // free-form: {min, max} for escala, currency presets, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
