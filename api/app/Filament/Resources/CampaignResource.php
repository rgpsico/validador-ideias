<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use App\Models\Question;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $modelLabel = 'Campanha';
    protected static ?string $pluralModelLabel = 'Campanhas';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados da campanha')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('titulo')
                        ->label('Título')
                        ->required()
                        ->maxLength(255)
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn ($set, $get, $state) => blank($get('slug')) ? $set('slug', Str::slug($state)) : null)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug (URL pública: /v/{slug})')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->alphaDash(),
                    Forms\Components\Select::make('formato')
                        ->label('Formato de apresentação')
                        ->options([
                            'wizard' => 'Wizard (uma pergunta por tela)',
                        ])
                        ->default('wizard')
                        ->required()
                        ->helperText('Outros formatos (chat, quiz, landing+wizard, etc.) chegam em fases futuras.'),
                    Forms\Components\Textarea::make('descricao')
                        ->label('Descrição')
                        ->rows(2)
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('ativo')
                        ->label('Ativa (visível publicamente)')
                        ->default(true),
                ]),

            Section::make('Perguntas')
                ->description('Monte a sequência de perguntas do wizard. Arraste para reordenar — nenhuma pergunta fica hardcoded no código.')
                ->schema([
                    Forms\Components\Repeater::make('questions')
                        ->relationship()
                        ->label('Perguntas')
                        ->reorderable('ordem')
                        ->orderColumn('ordem')
                        ->collapsible()
                        ->collapsed(fn (?Question $record) => $record !== null)
                        ->itemLabel(fn (array $state): ?string => $state['texto'] ?? 'Nova pergunta')
                        ->addActionLabel('+ Adicionar pergunta')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('tipo')
                                        ->label('Tipo')
                                        ->options(Question::TIPOS)
                                        ->required()
                                        ->live(),
                                    Forms\Components\Toggle::make('obrigatoria')
                                        ->label('Obrigatória')
                                        ->default(true),
                                ]),
                            Forms\Components\Textarea::make('texto')
                                ->label('Texto da pergunta')
                                ->required()
                                ->rows(2)
                                ->columnSpanFull(),

                            Forms\Components\Repeater::make('opcoes')
                                ->label('Opções de resposta')
                                ->visible(fn (Get $get) => in_array($get('tipo'), Question::TIPOS_COM_OPCOES, true))
                                ->schema([
                                    Forms\Components\TextInput::make('label')
                                        ->label('Texto exibido')
                                        ->required(),
                                    Forms\Components\TextInput::make('value')
                                        ->label('Valor (id interno)')
                                        ->required(),
                                    Forms\Components\TextInput::make('pontos')
                                        ->label('Pontos')
                                        ->numeric()
                                        ->default(0)
                                        ->helperText('Usado só no score interno — nunca aparece pro respondente.'),
                                ])
                                ->columns(3)
                                ->addActionLabel('+ Opção')
                                ->columnSpanFull(),

                            Forms\Components\KeyValue::make('config')
                                ->label('Configuração extra (opcional)')
                                ->helperText('Ex.: min/max para escala. Livre — não afeta o layout de outras perguntas.')
                                ->visible(fn (Get $get) => $get('tipo') === 'escala')
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('slug')
                    ->label('URL pública')
                    ->formatStateUsing(fn (string $state) => "/v/{$state}")
                    ->copyable(),
                Tables\Columns\TextColumn::make('formato')
                    ->label('Formato')
                    ->badge(),
                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Perguntas')
                    ->counts('questions'),
                Tables\Columns\TextColumn::make('leads_count')
                    ->label('Leads')
                    ->counts('leads'),
                Tables\Columns\IconColumn::make('ativo')
                    ->label('Ativa')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('ativo')->label('Ativas'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
