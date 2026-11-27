<?php

declare(strict_types=1);

namespace App\Support;

use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use App\Models\User;

class StoreContact
{
    public static function whatsappUrl(?string $message = null): string
    {
        return self::whatsappUrlTo((string) config('store.whatsapp'), $message);
    }

    public static function whatsappUrlTo(?string $rawPhone, ?string $message = null): string
    {
        $digits = preg_replace('/\D+/', '', (string) $rawPhone) ?? '';

        if ($digits !== '' && ! str_starts_with($digits, '55') && strlen($digits) <= 11) {
            $digits = '55'.$digits;
        }

        $baseUrl = "https://wa.me/{$digits}";

        if ($message !== null && $message !== '') {
            return $baseUrl.'?text='.rawurlencode($message);
        }

        return $baseUrl;
    }

    public static function leadAssignmentWhatsappUrl(Lead $lead, User $assignee): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $assignee->phone) ?? '';

        if ($digits === '') {
            return null;
        }

        $vehicleTitle = $lead->vehicle !== null
            ? "{$lead->vehicle->brand} {$lead->vehicle->model} ({$lead->vehicle->year_model})"
            : 'Contato Geral';

        $lines = [
            "Olá, *{$assignee->name}*! Você foi definido como responsável por um lead na *Tobias Veículos*:",
            '',
            "• *Cliente:* {$lead->name}",
            "• *Telefone/WhatsApp:* {$lead->phone}",
            "• *Veículo:* {$vehicleTitle}",
        ];

        if ($lead->wants_financing) {
            $lines[] = '• *Financiamento:* Sim, solicitou simulação';
        }

        if ($lead->has_trade_in) {
            $lines[] = '• *Troca:* Sim, possui veículo na troca';
        }

        if ($lead->message !== null && trim($lead->message) !== '') {
            $lines[] = "• *Mensagem:* \"{$lead->message}\"";
        }

        $lines[] = '';
        $lines[] = 'Acesse no painel: '.LeadResource::getUrl('edit', ['record' => $lead]);

        return self::whatsappUrlTo($digits, implode("\n", $lines));
    }
}
