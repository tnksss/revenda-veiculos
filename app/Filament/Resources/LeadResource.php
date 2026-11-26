<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\LeadStatus;
use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Leads & Propostas';

    protected static ?string $modelLabel = 'Lead';

    protected static ?string $pluralModelLabel = 'Leads & Propostas';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $newCount = Lead::query()->where('status', LeadStatus::New)->count();

        return $newCount > 0 ? (string) $newCount : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados do Cliente e Interesse')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('vehicle_id')
                            ->label('Veículo de Interesse')
                            ->relationship('vehicle', 'model')
                            ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->brand} {$record->model} ({$record->year_model})")
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('status')
                            ->label('Status do Funil')
                            ->options(LeadStatus::class)
                            ->required()
                            ->default(LeadStatus::New),

                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Cliente')
                            ->required()
                            ->maxLength(120),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone / WhatsApp')
                            ->required()
                            ->maxLength(30),

                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(150),

                        Forms\Components\Select::make('assigned_to')
                            ->label('Consultor Responsável')
                            ->relationship('assignedUser', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Toggle::make('wants_financing')
                            ->label('Interesse em Financiamento'),

                        Forms\Components\Toggle::make('has_trade_in')
                            ->label('Possui Veículo na Troca'),

                        Forms\Components\Textarea::make('message')
                            ->label('Mensagem do Cliente')
                            ->columnSpanFull()
                            ->rows(3),

                        Forms\Components\Textarea::make('internal_notes')
                            ->label('Anotações do Atendimento (Interno)')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Recebido em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Cliente')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('vehicle.model')
                    ->label('Veículo')
                    ->formatStateUsing(fn (?Lead $record): string => $record?->vehicle !== null
                        ? "{$record->vehicle->brand} {$record->vehicle->model}"
                        : 'Contato Geral')
                    ->searchable(),

                Tables\Columns\IconColumn::make('wants_financing')
                    ->label('Financ.')
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_trade_in')
                    ->label('Troca')
                    ->boolean(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Consultor')
                    ->placeholder('Não atribuído'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(LeadStatus::class),

                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Consultor')
                    ->relationship('assignedUser', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-oval-left-ellipsis')
                    ->color('success')
                    ->url(fn (Lead $record): string => $record->whatsapp_url, shouldOpenInNewTab: true),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
