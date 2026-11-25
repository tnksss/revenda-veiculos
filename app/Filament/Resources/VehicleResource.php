<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Enums\VehicleStatus;
use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Veículos';

    protected static ?string $modelLabel = 'Veículo';

    protected static ?string $pluralModelLabel = 'Veículos';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Cadastro de Veículo')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Dados Principais')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Forms\Components\Section::make('Identificação do Veículo')
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('brand')
                                            ->label('Marca')
                                            ->placeholder('Ex: Honda, Toyota, Volkswagen')
                                            ->required()
                                            ->maxLength(80),

                                        Forms\Components\TextInput::make('model')
                                            ->label('Modelo')
                                            ->placeholder('Ex: Civic, Corolla, T-Cross')
                                            ->required()
                                            ->maxLength(80),

                                        Forms\Components\TextInput::make('version')
                                            ->label('Versão')
                                            ->placeholder('Ex: 2.0 EXL FlexOne Automático')
                                            ->required()
                                            ->maxLength(120),

                                        Forms\Components\TextInput::make('year_manufacture')
                                            ->label('Ano Fabricação')
                                            ->numeric()
                                            ->minValue(1950)
                                            ->maxValue((int) date('Y') + 1)
                                            ->required(),

                                        Forms\Components\TextInput::make('year_model')
                                            ->label('Ano Modelo')
                                            ->numeric()
                                            ->minValue(1950)
                                            ->maxValue((int) date('Y') + 1)
                                            ->required(),

                                        Forms\Components\TextInput::make('color')
                                            ->label('Cor')
                                            ->placeholder('Ex: Branco Pérola, Prata')
                                            ->required()
                                            ->maxLength(50),
                                    ]),

                                Forms\Components\Section::make('Especificações e Valor')
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\Select::make('fuel_type')
                                            ->label('Combustível')
                                            ->options(FuelType::class)
                                            ->required()
                                            ->native(false),

                                        Forms\Components\Select::make('transmission')
                                            ->label('Câmbio')
                                            ->options(TransmissionType::class)
                                            ->required()
                                            ->native(false),

                                        Forms\Components\TextInput::make('mileage')
                                            ->label('Quilometragem (km)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->suffix('km')
                                            ->required(),

                                        Forms\Components\TextInput::make('price')
                                            ->label('Preço de Venda (R$)')
                                            ->numeric()
                                            ->prefix('R$')
                                            ->minValue(1000)
                                            ->step(0.01)
                                            ->required(),

                                        Forms\Components\Select::make('status')
                                            ->label('Situação na Loja')
                                            ->options(VehicleStatus::class)
                                            ->default(VehicleStatus::Preparation)
                                            ->required()
                                            ->native(false)
                                            ->live()
                                            ->rules([
                                                fn (Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                                    $statusValue = $value instanceof VehicleStatus ? $value->value : $value;

                                                    if ($statusValue === VehicleStatus::Available->value) {
                                                        $photos = $get('photos') ?? [];
                                                        if (count($photos) < 3) {
                                                            $fail('Para publicar o veículo como Disponível, adicione pelo menos 3 fotos na aba Galeria.');
                                                        }
                                                    }
                                                },
                                            ]),

                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Destacar na Vitrine')
                                            ->helperText('Exibe este veículo no topo da página inicial.')
                                            ->inline(false)
                                            ->default(false),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Galeria de Fotos')
                            ->icon('heroicon-o-photo')
                            ->badge(fn (Get $get): int => count($get('photos') ?? []))
                            ->schema([
                                Forms\Components\Repeater::make('photos')
                                    ->label('Fotos do Veículo (arraste para definir a ordem; a primeira será a capa)')
                                    ->relationship()
                                    ->orderColumn('sort_order')
                                    ->reorderableWithButtons()
                                    ->grid(3)
                                    ->defaultItems(0)
                                    ->addActionLabel('Adicionar Foto')
                                    ->schema([
                                        Forms\Components\FileUpload::make('path')
                                            ->label('Imagem')
                                            ->image()
                                            ->disk('public')
                                            ->directory('vehicles')
                                            ->imageEditor()
                                            ->maxSize(5120)
                                            ->required(),

                                        Forms\Components\Toggle::make('is_cover')
                                            ->label('Foto de Capa')
                                            ->default(false),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Opcionais e Descrição')
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                Forms\Components\CheckboxList::make('features')
                                    ->label('Opcionais e Equipamentos')
                                    ->columns(3)
                                    ->bulkToggleable()
                                    ->options([
                                        'Ar-condicionado digital' => 'Ar-condicionado digital',
                                        'Direção elétrica' => 'Direção elétrica',
                                        'Central multimídia com Apple CarPlay / Android Auto' => 'Multimídia Apple CarPlay / Android Auto',
                                        'Câmera de ré' => 'Câmera de ré',
                                        'Sensor de estacionamento traseiro' => 'Sensor de estacionamento',
                                        'Rodas de liga leve' => 'Rodas de liga leve',
                                        'Bancos em couro' => 'Bancos em couro',
                                        'Piloto automático' => 'Piloto automático',
                                        'Controle de tração e estabilidade' => 'Controle de tração e estabilidade',
                                        '6 Airbags' => '6 Airbags',
                                        'Chave presencial (Start/Stop)' => 'Chave presencial (Start/Stop)',
                                        'Faróis em LED' => 'Faróis em LED',
                                        'Teto solar' => 'Teto solar',
                                        'Manual e chave reserva' => 'Manual e chave reserva',
                                        'Laudo cautelar aprovado' => 'Laudo cautelar aprovado',
                                    ]),

                                Forms\Components\Textarea::make('description')
                                    ->label('Descrição Pública do Anúncio')
                                    ->rows(4)
                                    ->placeholder('Detalhes sobre estado de conservação, revisões em concessionária, condições de financiamento...'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Controle Interno (Restrito)')
                            ->icon('heroicon-o-lock-closed')
                            ->schema([
                                Forms\Components\Section::make('Dados Confidenciais da Loja')
                                    ->description('Estas informações nunca são exibidas na vitrine pública (LGPD e margem comercial).')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('plate')
                                            ->label('Placa Completa')
                                            ->placeholder('ABC1D23')
                                            ->required()
                                            ->maxLength(8)
                                            ->unique(ignoreRecord: true)
                                            ->extraInputAttributes(['style' => 'text-transform: uppercase']),

                                        Forms\Components\TextInput::make('cost_price')
                                            ->label('Preço de Custo / Aquisição (R$)')
                                            ->numeric()
                                            ->prefix('R$')
                                            ->step(0.01),

                                        Forms\Components\Textarea::make('internal_notes')
                                            ->label('Observações Internas (Pátio / Reparos / Procedência)')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('coverPhoto.path')
                    ->label('Capa')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/images/car-placeholder.png')),

                Tables\Columns\TextColumn::make('brand')
                    ->label('Veículo')
                    ->formatStateUsing(fn (Vehicle $record): string => "{$record->brand} {$record->model}")
                    ->description(fn (Vehicle $record): string => "{$record->version} • {$record->year_manufacture}/{$record->year_model}")
                    ->searchable(['brand', 'model', 'version'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('plate')
                    ->label('Placa')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('mileage')
                    ->label('KM')
                    ->numeric(thousandsSeparator: '.')
                    ->suffix(' km')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Situação')
                    ->badge()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destaque')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Situação')
                    ->options(VehicleStatus::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('fuel_type')
                    ->label('Combustível')
                    ->options(FuelType::class),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Em Destaque'),

                Tables\Filters\TrashedFilter::make()
                    ->label('Excluídos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('marcarVendido')
                    ->label('Vendido')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar venda do veículo?')
                    ->modalDescription('O veículo será removido da vitrine pública imediatamente e marcado como Vendido.')
                    ->visible(fn (Vehicle $record): bool => $record->status !== VehicleStatus::Sold)
                    ->action(function (Vehicle $record): void {
                        $record->update([
                            'status' => VehicleStatus::Sold,
                            'is_featured' => false,
                        ]);
                    }),
                        
                    
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}