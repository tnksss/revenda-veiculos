<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VehicleStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $availableCount = Vehicle::where('status', VehicleStatus::Available)->count();
        $preparationCount = Vehicle::where('status', VehicleStatus::Preparation)->count();
        $reservedCount = Vehicle::where('status', VehicleStatus::Reserved)->count();

        $stockTotalValue = (float) Vehicle::whereIn('status', [
            VehicleStatus::Available,
            VehicleStatus::Reserved,
        ])->sum('price');

        $formattedStockValue = 'R$ ' . number_format($stockTotalValue, 2, ',', '.');

        return [
            Stat::make('Disponíveis na Vitrine', $availableCount)
                ->description('Prontos para venda imediata')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Em Preparação', $preparationCount)
                ->description('Aguardando revisão, fotos ou laudo')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning'),

            Stat::make('Reservados', $reservedCount)
                ->description('Em negociação / sinal pago')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Valor Total em Estoque', $formattedStockValue)
                ->description('Soma dos veículos disponíveis e reservados')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
        ];
    }
}