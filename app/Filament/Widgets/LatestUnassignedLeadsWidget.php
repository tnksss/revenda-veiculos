<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestUnassignedLeadsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Fila de Atendimento (Leads sem Consultor)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Lead::query()
                    ->with('vehicle')
                    ->whereNull('assigned_to')
                    ->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Lost])
                    ->latest()
            )
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Nenhum lead aguardando na fila')
            ->emptyStateDescription('Todos os leads ativos já possuem um consultor responsável.')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Recebido')
                    ->since()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Cliente')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone / WhatsApp'),

                Tables\Columns\TextColumn::make('vehicle.model')
                    ->label('Veículo de Interesse')
                    ->formatStateUsing(fn (?Lead $record): string => $record?->vehicle !== null
                        ? "{$record->vehicle->brand} {$record->vehicle->model} ({$record->vehicle->year_model})"
                        : 'Contato Geral'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('claim')
                    ->label('Assumir Atendimento')
                    ->icon('heroicon-o-hand-raised')
                    ->color('info')
                    ->action(function (Lead $record): void {
                        $record->update([
                            'assigned_to' => auth()->id(),
                            'status' => $record->status === LeadStatus::New ? LeadStatus::Contacted : $record->status,
                        ]);

                        Notification::make()
                            ->title('Atendimento assumido! O lead agora está na sua carteira.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-oval-left-ellipsis')
                    ->color('success')
                    ->url(fn (Lead $record): string => $record->whatsapp_url, shouldOpenInNewTab: true),
            ]);
    }
}
