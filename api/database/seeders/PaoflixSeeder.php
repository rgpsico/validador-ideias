<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Question;
use App\Models\QuestionRule;
use Illuminate\Database\Seeder;

class PaoflixSeeder extends Seeder
{
    public function run(): void
    {
        $campaign = Campaign::query()->updateOrCreate(
            ['slug' => 'paoflix'],
            [
                'titulo' => 'Pão quentinho na sua porta pela manhã?',
                'descricao' => 'Ajude a validar a ideia PãoFlix respondendo algumas perguntas rápidas. Isso não leva nem 2 minutos.',
                'formato' => 'wizard',
                'ativo' => true,
            ]
        );

        // Wipe existing questions/rules for idempotent re-seeding.
        QuestionRule::whereIn('question_id', $campaign->questions()->pluck('id'))->delete();
        $campaign->questions()->delete();

        $q1 = Question::create([
            'campaign_id' => $campaign->id,
            'tipo' => 'multipla_escolha',
            'texto' => 'Onde você costuma comprar pão?',
            'ordem' => 1,
            'obrigatoria' => true,
            'opcoes' => [
                ['label' => 'Padaria do bairro', 'value' => 'padaria_bairro', 'pontos' => 0],
                ['label' => 'Supermercado', 'value' => 'supermercado', 'pontos' => 0],
                ['label' => 'Padaria de rede', 'value' => 'padaria_rede', 'pontos' => 0],
                ['label' => 'Não compro pão', 'value' => 'nao_compro', 'pontos' => 0],
            ],
        ]);

        $q2 = Question::create([
            'campaign_id' => $campaign->id,
            'tipo' => 'multipla_escolha',
            'texto' => 'Quantos dias por semana você compra pão?',
            'ordem' => 2,
            'obrigatoria' => true,
            'opcoes' => [
                ['label' => 'Todo dia', 'value' => 'todo_dia', 'pontos' => 20],
                ['label' => '4-6 dias', 'value' => '4_6_dias', 'pontos' => 15],
                ['label' => '1-3 dias', 'value' => '1_3_dias', 'pontos' => 5],
                ['label' => 'Raramente', 'value' => 'raramente', 'pontos' => 0],
            ],
        ]);

        $q3 = Question::create([
            'campaign_id' => $campaign->id,
            'tipo' => 'multipla_escolha',
            'texto' => 'Quanto tempo normalmente leva entre sair de casa, comprar e voltar?',
            'ordem' => 3,
            'obrigatoria' => true,
            'opcoes' => [
                ['label' => 'Menos de 10 min', 'value' => 'menos_10', 'pontos' => 0],
                ['label' => '10-20 min', 'value' => '10_20', 'pontos' => 5],
                ['label' => '20-40 min', 'value' => '20_40', 'pontos' => 10],
                ['label' => 'Mais de 40 min', 'value' => 'mais_40', 'pontos' => 15],
            ],
        ]);

        $q4 = Question::create([
            'campaign_id' => $campaign->id,
            'tipo' => 'texto_livre',
            'texto' => 'O que mais incomoda nesse processo?',
            'ordem' => 4,
            'obrigatoria' => false,
            'opcoes' => null,
        ]);

        $q5 = Question::create([
            'campaign_id' => $campaign->id,
            'tipo' => 'sim_nao',
            'texto' => 'Se pão fresco pudesse chegar na sua casa pela manhã, você usaria?',
            'ordem' => 5,
            'obrigatoria' => true,
            'opcoes' => [
                ['label' => 'Sim', 'value' => 'sim', 'pontos' => 20],
                ['label' => 'Não', 'value' => 'nao', 'pontos' => 0],
            ],
        ]);

        $q6 = Question::create([
            'campaign_id' => $campaign->id,
            'tipo' => 'multipla_escolha',
            'texto' => 'Quanto estaria disposto a pagar pela entrega?',
            'ordem' => 6,
            'obrigatoria' => true,
            'opcoes' => [
                ['label' => 'R$2,90', 'value' => '2_90', 'pontos' => 5],
                ['label' => 'R$4,90', 'value' => '4_90', 'pontos' => 10],
                ['label' => 'R$6,90', 'value' => '6_90', 'pontos' => 15],
                ['label' => 'Não pagaria', 'value' => 'nao_pagaria', 'pontos' => 0],
            ],
        ]);

        $q7 = Question::create([
            'campaign_id' => $campaign->id,
            'tipo' => 'multipla_escolha',
            'texto' => 'Preferiria pagar por entrega avulsa ou uma assinatura mensal?',
            'ordem' => 7,
            'obrigatoria' => true,
            'opcoes' => [
                ['label' => 'Avulsa', 'value' => 'avulsa', 'pontos' => 5],
                ['label' => 'Assinatura mensal', 'value' => 'assinatura', 'pontos' => 15],
                ['label' => 'Não sei', 'value' => 'nao_sei', 'pontos' => 0],
            ],
        ]);

        $q8 = Question::create([
            'campaign_id' => $campaign->id,
            'tipo' => 'sim_nao',
            'texto' => 'Gostaria de participar de um teste real do serviço?',
            'ordem' => 8,
            'obrigatoria' => true,
            'opcoes' => [
                ['label' => 'Sim', 'value' => 'sim', 'pontos' => 30],
                ['label' => 'Não', 'value' => 'nao', 'pontos' => 0],
            ],
        ]);

        $qNome = Question::create([
            'campaign_id' => $campaign->id,
            'tipo' => 'nome',
            'texto' => 'Como podemos te chamar?',
            'ordem' => 9,
            'obrigatoria' => true,
            'opcoes' => null,
        ]);

        $qWhatsapp = Question::create([
            'campaign_id' => $campaign->id,
            'tipo' => 'whatsapp',
            'texto' => 'Qual seu WhatsApp?',
            'ordem' => 10,
            'obrigatoria' => true,
            'opcoes' => null,
        ]);

        // The contact questions only appear if question 8 ("quer testar agora?") was answered "Sim".
        foreach ([$qNome, $qWhatsapp] as $contactQuestion) {
            QuestionRule::create([
                'question_id' => $contactQuestion->id,
                'depends_on_question_id' => $q8->id,
                'operator' => 'equals',
                'value' => 'sim',
            ]);
        }
    }
}
