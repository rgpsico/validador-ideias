<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionRuleResource\Pages;
use App\Models\Question;
use App\Models\QuestionRule;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionRuleResource extends Resource
{
    protected static ?string $model = QuestionRule::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $modelLabel = 'Regra condicional';
    protected static ?string $pluralModelLabel = 'Regras condicionais (perguntas condicionais)';
    protected static ?int $navigationSort = 3;

    protected static function questionOptions(): array
    {
        return Question::query()
            ->with('campaign')
            ->orderBy('campaign_id')
            ->orderBy('ordem')
            ->get()
            ->mapWithKeys(fn (Question $q) => [
                $q->id => sprintf('[%s] %s', $q->campaign->titulo ?? '—', \Illuminate\Support\Str::limit($q->texto, 60)),
            ])
            ->toArray();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('question_id')
                ->label('Pergunta que será mostrada/ocultada')
                ->options(fn () => static::questionOptions())
                ->searchable()
                ->required(),
            Forms\Components\Select::make('depends_on_question_id')
                ->label('Depende da resposta desta pergunta anterior')
                ->options(fn () => static::questionOptions())
                ->searchable()
                ->required(),
            Forms\Components\Select::make('operator')
                ->label('Condição')
                ->options(QuestionRule::OPERATORS)
                ->default('equals')
                ->required()
                ->live(),
            Forms\Components\TextInput::make('value')
                ->label('Valor esperado')
                ->helperText('Ex: sim. Para "está entre", separe múltiplos valores por vírgula. Ex: sim,talvez')
                ->required()
                ->afterStateHydrated(function (Forms\Components\TextInput $component, $state) {
                    $component->state(is_array($state) ? implode(',', $state) : (string) $state);
                })
                ->dehydrateStateUsing(function ($state, $get) {
                    $parts = array_map('trim', explode(',', (string) $state));

                    return $get('operator') === 'in' ? $parts : ($parts[0] ?? '');
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question.texto')
                    ->label('Pergunta condicional')
                    ->wrap()
                    ->limit(50),
                Tables\Columns\TextColumn::make('dependsOn.texto')
                    ->label('Depende de')
                    ->wrap()
                    ->limit(50),
                Tables\Columns\TextColumn::make('operator')
                    ->label('Condição')
                    ->badge(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : (string) $state),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestionRules::route('/'),
            'create' => Pages\CreateQuestionRule::route('/create'),
            'edit' => Pages\EditQuestionRule::route('/{record}/edit'),
        ];
    }
}
