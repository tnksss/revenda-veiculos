<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadResource\Pages;

use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use App\Models\User;
use App\Support\StoreContact;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('notifyAssigneeWhatsapp')
                ->label('Avisar Consultor no WhatsApp')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(function (): bool {
                    /** @var Lead $record */
                    $record = $this->getRecord();
                    /** @var User|null $assignee */
                    $assignee = $record->assignedUser;

                    return $assignee !== null && filled($assignee->phone);
                })
                ->url(function (): string {
                    /** @var Lead $record */
                    $record = $this->getRecord();
                    /** @var User $assignee */
                    $assignee = $record->assignedUser;

                    return StoreContact::leadAssignmentWhatsappUrl($record, $assignee) ?? '#';
                }, shouldOpenInNewTab: true),

            Actions\DeleteAction::make(),
        ];
    }
}
