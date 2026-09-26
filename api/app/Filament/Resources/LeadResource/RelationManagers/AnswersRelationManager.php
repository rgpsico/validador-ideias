<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'answers';
    protected static ?string $title = 'Respostas';
    protected static ?string $modelLabel = 'resposta';
    protected static ?string $pluralModelLabel = 'respostas';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('question.ordem')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('question.texto')
                    ->label('Pergunta')
                    ->wrap(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Resposta')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return implode(', ', array_map('strval', $state));
                        }

                        return (string) $state;
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('pontos')
                    ->label('Pontos'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
