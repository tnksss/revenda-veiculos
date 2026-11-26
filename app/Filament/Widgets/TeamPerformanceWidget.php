<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\LeadStatus;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TeamPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Desempenho Comercial da Equipe';

    public static function canView(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->isAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('is_active', true)
                    ->withCount([
                        'assignedLeads as active_leads_count' => fn (Builder $query): Builder => $query->whereIn('status', [
                            LeadStatus::New,
                            LeadStatus::Contacted,
                            LeadStatus::Scheduled,
                        ]),
                        'assignedLeads as converted_leads_count' => fn (Builder $query): Builder => $query->where('status', LeadStatus::Converted),
                        'assignedLeads as total_leads_count',
                    ])
                    ->orderByDesc('converted_leads_count')
            )
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Consultor')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('role')
                    ->label('Perfil')
                    ->badge(),

                Tables\Columns\TextColumn::make('active_leads_count')
                    ->label('Em Atendimento')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('converted_leads_count')
                    ->label('Convertidos')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('total_leads_count')
                    ->label('Total na Carteira'),
            ]);
    }
}
