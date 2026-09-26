<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('nome')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->unsignedInteger('score')->default(0);
            $table->string('classificacao')->nullable(); // curioso | potencial | qualificado | quente
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('current_question_id')->nullable()->constrained('questions')->nullOnDelete();
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
