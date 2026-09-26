<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Filament\Resources\LeadResource\RelationManagers;
use App\Models\Lead;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $modelLabel = 'Lead';
    protected static ?string $pluralModelLabel = 'Leads';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Lead')
                ->columns(2)
                ->schema([
                    Forms\Components\Placeholder::make('campaign')
                        ->label('Campanha')
                        ->content(fn (?Lead $record) => $record?->campaign?->titulo ?? '—'),
                    Forms\Components\Placeholder::make('uuid')
                        ->label('UUID')
                        ->content(fn (?Lead $record) => $record?->uuid ?? '—'),
                    Forms\Components\Placeholder::make('nome')
                        ->label('Nome')
                        ->content(fn (?Lead $record) => $record?->nome ?? '—'),
                    Forms\Components\Placeholder::make('whatsapp')
                        ->label('WhatsApp')
                        ->content(fn (?Lead $record) => $record?->whatsapp ?? '—'),
                    Forms\Components\Placeholder::make('email')
                        ->label('E-mail')
                        ->content(fn (?Lead $record) => $record?->email ?? '—'),
                    Forms\Components\Placeholder::make('score')
                        ->label('Score')
                        ->content(fn (?Lead $record) => (string) ($record?->score ?? 0)),
                    Forms\Components\Placeholder::make('classificacao')
                        ->label('Classificação')
                        ->content(fn (?Lead $record) => $record?->classificacao ?? 'em andamento'),
                    Forms\Components\Placeholder::make('started_at')
                        ->label('Iniciou em')
                        ->content(fn (?Lead $record) => $record?->started_at?->format('d/m/Y H:i') ?? '—'),
                    Forms\Components\Placeholder::make('completed_at')
                        ->label('Concluiu em')
                        ->content(fn (?Lead $record) => $record?->completed_at?->format('d/m/Y H:i') ?? '—'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('campaign.titulo')
                    ->label('Campanha')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->sortable(),
                Tables\Columns\TextColumn::make('classificacao')
                    ->label('Classificação')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'quente' => 'danger',
                        'qualificado' => 'success',
                        'potencial' => 'warning',
                        'curioso' => 'gray',
                        default => 'gray',
                    })
                    ->placeholder('em andamento'),
                Tables\Columns\IconColumn::make('completed_at')
                    ->label('Concluiu?')
                    ->boolean()
                    ->getStateUsing(fn (Lead $record) => $record->completed_at !== null),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Iniciou em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('classificacao')
                    ->label('Classificação')
                    ->options([
                        'curioso' => 'Curioso',
                        'potencial' => 'Potencial',
                        'qualificado' => 'Qualificado',
                        'quente' => 'Quente',
                    ]),
                Tables\Filters\SelectFilter::make('campaign_id')
                    ->label('Campanha')
                    ->relationship('campaign', 'titulo'),
                Tables\Filters\TernaryFilter::make('completed_at')
                    ->label('Concluiu')
                    ->nullable(),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AnswersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'view' => Pages\ViewLead::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
