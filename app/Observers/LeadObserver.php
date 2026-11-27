<?php

declare(strict_types=1);

namespace App\Observers;

use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class LeadObserver
{
    public function created(Lead $lead): void
    {
        $recipients = User::query()
            ->where('is_active', true)
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        $vehicleTitle = $lead->vehicle !== null
            ? "{$lead->vehicle->brand} {$lead->vehicle->model} ({$lead->vehicle->year_model})"
            : 'Contato Geral';

        $details = ["Cliente: {$lead->name} ({$lead->phone})", "Interesse: {$vehicleTitle}"];

        if ($lead->wants_financing) {
            $details[] = 'Solicitou simulação de financiamento';
        }

        if ($lead->has_trade_in) {
            $details[] = 'Possui veículo na troca';
        }

        Notification::make()
            ->title('Novo lead recebido na vitrine!')
            ->icon('heroicon-o-bell-alert')
            ->iconColor('warning')
            ->body(implode(' • ', $details))
            ->actions([
                Action::make('viewLead')
                    ->label('Atender Lead')
                    ->url(LeadResource::getUrl('edit', ['record' => $lead]))
                    ->markAsRead(),
            ])
            ->sendToDatabase($recipients);
    }
}
