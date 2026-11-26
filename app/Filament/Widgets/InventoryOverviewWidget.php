<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\LeadStatus;
use App\Enums\VehicleStatus;
use App\Models\Lead;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        /** @var User|null $user */
        $user = auth()->user();

        $activeStatuses = [
            VehicleStatus::Available,
            VehicleStatus::Reserved,
            VehicleStatus::Preparation,
        ];

        $totalInYard = Vehicle::query()->whereIn('status', $activeStatuses)->count();
        $availableCount = Vehicle::query()->where('status', VehicleStatus::Available)->count();
        $preparationCount = Vehicle::query()->where('status', VehicleStatus::Preparation)->count();

        $newLeadsCount = Lead::query()->where('status', LeadStatus::New)->count();
        $unassignedLeadsCount = Lead::query()->whereNull('assigned_to')->count();

        $soldThisMonthQuery = Vehicle::query()
            ->where('status', VehicleStatus::Sold)
            ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()]);

        $soldThisMonthCount = (clone $soldThisMonthQuery)->count();
        $soldThisMonthRevenue = (float) (clone $soldThisMonthQuery)->sum('price');

        $stats = [
            Stat::make('Veículos em Pátio', (string) $totalInYard)
                ->description("{$availableCount} na vitrine • {$preparationCount} em preparação")
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary'),

            Stat::make('Leads Novos', (string) $newLeadsCount)
                ->description("{$unassignedLeadsCount} aguardando consultor")
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($newLeadsCount > 0 ? 'warning' : 'success'),

            Stat::make('Vendas no Mês', (string) $soldThisMonthCount)
                ->description('Faturamento: R$ '.number_format($soldThisMonthRevenue, 2, ',', '.'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];

        if ($user?->isAdmin() === true) {
            $totalCost = (float) Vehicle::query()
                ->whereIn('status', $activeStatuses)
                ->sum('cost_price');

            $totalAsking = (float) Vehicle::query()
                ->whereIn('status', $activeStatuses)
                ->sum('price');

            $potentialMargin = $totalAsking - $totalCost;

            $stats[] = Stat::make('Capital Imobilizado (Custo)', 'R$ '.number_format($totalCost, 2, ',', '.'))
                ->description('Margem bruta estimada: R$ '.number_format($potentialMargin, 2, ',', '.'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info');
        }

        return $stats;
    }
}
