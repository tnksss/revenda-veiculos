<?php

declare(strict_types=1);

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Enums\VehicleStatus;
use App\Filament\Resources\VehicleResource;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListVehicles extends ListRecords
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo Veículo'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos')
                ->badge(Vehicle::count()),

            'disponiveis' => Tab::make('Disponíveis')
                ->badge(Vehicle::where('status', VehicleStatus::Available)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', VehicleStatus::Available)),

            'preparacao' => Tab::make('Em Preparação')
                ->badge(Vehicle::where('status', VehicleStatus::Preparation)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', VehicleStatus::Preparation)),

            'reservados' => Tab::make('Reservados')
                ->badge(Vehicle::where('status', VehicleStatus::Reserved)->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', VehicleStatus::Reserved)),

            'vendidos' => Tab::make('Vendidos')
                ->badge(Vehicle::where('status', VehicleStatus::Sold)->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', VehicleStatus::Sold)),
        ];
    }
}
