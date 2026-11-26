<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportVehiclesCsvAction
{
    /**
     * @param  Builder<Vehicle>  $query
     */
    public function execute(Builder $query, ?User $user = null): StreamedResponse
    {
        $isAdmin = $user?->isAdmin() ?? false;
        $filename = 'tobias-veiculos-estoque-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query, $isAdmin): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            // BOM UTF-8 para compatibilidade com Excel
            fwrite($handle, "\xEF\xBB\xBF");

            $headers = [
                'ID',
                'Marca',
                'Modelo',
                'Versao',
                'Ano Fab/Mod',
                'Quilometragem',
                'Cambio',
                'Combustivel',
                'Cor',
                'Status',
                'Preco Venda (R$)',
            ];

            if ($isAdmin) {
                $headers[] = 'Placa';
                $headers[] = 'Preco Custo (R$)';
                $headers[] = 'Margem Bruta (R$)';
            }

            fputcsv($handle, $headers, ';');

            $query->chunk(200, function ($vehicles) use ($handle, $isAdmin): void {
                /** @var Vehicle $vehicle */
                foreach ($vehicles as $vehicle) {
                    $row = [
                        $vehicle->id, $vehicle->brand,
                        $vehicle->model, $vehicle->version ?? '',
                        "{$vehicle->year_manufacture}/{$vehicle->year_model}",
                        $vehicle->mileage,
                        $vehicle->transmission->getLabel(), $vehicle->fuel_type->getLabel(),
                        $vehicle->color, $vehicle->status->getLabel(),
                        number_format((float) $vehicle->price, 2, ',', '.'),
                    ];

                    if ($isAdmin) {
                        $margin = (float) $vehicle->price - (float) $vehicle->cost_price;
                        $row[] = $vehicle->plate;
                        $row[] = number_format((float) $vehicle->cost_price, 2, ',', '.');
                        $row[] = number_format($margin, 2, ',', '.');
                    }

                    fputcsv($handle, $row, ';');
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
