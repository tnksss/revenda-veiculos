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

        if ($recipients->isNotEmpty()) {
            $vehicleTitle = $this->formatVehicleTitle($lead);
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

        if ($lead->assigned_to !== null) {
            $this->notifyAssignedUser($lead);
        }
    }

    public function updated(Lead $lead): void
    {
        if (! $lead->wasChanged('assigned_to') || $lead->assigned_to === null) {
            return;
        }

        $this->notifyAssignedUser($lead);
    }

    private function notifyAssignedUser(Lead $lead): void
    {
        /** @var User|null $assignee */
        $assignee = $lead->assignedUser()->first();

        if ($assignee === null || ! $assignee->is_active) {
            return;
        }

        if (auth()->id() === $assignee->id) {
            return;
        }

        $vehicleTitle = $this->formatVehicleTitle($lead);

        Notification::make()
            ->title('Você foi definido como responsável por um lead')
            ->icon('heroicon-o-user-plus')
            ->iconColor('info')
            ->body("Cliente: {$lead->name} ({$lead->phone}) • Interesse: {$vehicleTitle}")
            ->actions([
                Action::make('viewLead')
                    ->label('Abrir Lead')
                    ->url(LeadResource::getUrl('edit', ['record' => $lead]))
                    ->markAsRead(),
            ])
            ->sendToDatabase($assignee);
    }

    private function formatVehicleTitle(Lead $lead): string
    {
        return $lead->vehicle !== null
            ? "{$lead->vehicle->brand} {$lead->vehicle->model} ({$lead->vehicle->year_model})"
            : 'Contato Geral';
    }
}
