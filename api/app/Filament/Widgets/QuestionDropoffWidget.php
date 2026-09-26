<?php

namespace App\Filament\Widgets;

use App\Models\Question;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class QuestionDropoffWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Conversão por pergunta (distribuição de respostas e onde mais abandonam)')
            ->query($this->buildQuery())
            ->columns([
                Tables\Columns\TextColumn::make('campaign.titulo')
                    ->label('Campanha'),
                Tables\Columns\TextColumn::make('ordem')
                    ->label('#'),
                Tables\Columns\TextColumn::make('texto')
                    ->label('Pergunta')
                    ->limit(60)
                    ->wrap(),
                Tables\Columns\TextColumn::make('respostas_count')
                    ->label('Respostas recebidas'),
                Tables\Columns\TextColumn::make('parados_count')
                    ->label('Leads parados aqui (abandono)'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campaign_id')
                    ->label('Campanha')
                    ->relationship('campaign', 'titulo'),
            ])
            ->paginated(false)
            ->defaultSort('ordem');
    }

    protected function buildQuery(): Builder
    {
        return Question::query()
            ->with('campaign')
            ->withCount('answers as respostas_count')
            ->withCount('leadsParadosAqui as parados_count')
            ->orderBy('campaign_id')
            ->orderBy('ordem');
    }
}
