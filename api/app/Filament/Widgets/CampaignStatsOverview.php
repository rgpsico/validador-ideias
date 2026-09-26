<?php

namespace App\Filament\Widgets;

use App\Models\Campaign;
use App\Models\Lead;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CampaignStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $totalVisitantes = Lead::count();
        $totalIniciaram = Lead::whereNotNull('started_at')->count();
        $totalTerminaram = Lead::whereNotNull('completed_at')->count();
        $taxaConclusao = $totalIniciaram > 0
            ? round(($totalTerminaram / $totalIniciaram) * 100, 1)
            : 0;
        $totalComContato = Lead::where(function ($q) {
            $q->whereNotNull('nome')->orWhereNotNull('whatsapp')->orWhereNotNull('email');
        })->count();
        $totalQualificados = Lead::whereIn('classificacao', ['qualificado', 'quente'])->count();

        return [
            Stat::make('Visitantes (todas campanhas)', $totalVisitantes)
                ->description(Campaign::count() . ' campanhas cadastradas')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),

            Stat::make('Iniciaram o formulário', $totalIniciaram)
                ->description('Lead criado ao abrir a campanha')
                ->descriptionIcon('heroicon-m-play')
                ->color('info'),

            Stat::make('Concluíram', $totalTerminaram)
                ->description("Taxa de conclusão: {$taxaConclusao}%")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Leads com contato', $totalComContato)
                ->description('Nome, WhatsApp ou e-mail preenchido')
                ->descriptionIcon('heroicon-m-identification')
                ->color('warning'),

            Stat::make('Leads qualificados/quentes', $totalQualificados)
                ->description('classificação: qualificado + quente')
                ->descriptionIcon('heroicon-m-fire')
                ->color('danger'),
        ];
    }
}
