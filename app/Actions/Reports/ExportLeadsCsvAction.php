<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportLeadsCsvAction
{
    /**
     * @param  Builder<Lead>  $query
     */
    public function execute(Builder $query): StreamedResponse
    {
        $filename = 'tobias-veiculos-leads-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID',
                'Recebido em',
                'Cliente',
                'Telefone',
                'E-mail',
                'Veiculo de Interesse',
                'Financiamento',
                'Troca',
                'Status',
                'Consultor Responsavel',
            ], ';');

            $query->with(['vehicle', 'assignedUser'])->chunk(200, function ($leads) use ($handle): void {
                /** @var Lead $lead */
                foreach ($leads as $lead) {
                    $vehicleLabel = $lead->vehicle !== null
                        ? "{$lead->vehicle->brand} {$lead->vehicle->model} ({$lead->vehicle->year_model})"
                        : 'Contato Geral';

                    fputcsv($handle, [$lead->id,
                        $lead->created_at?->format('d/m/Y H:i') ?? '', $lead->name,
                        $lead->phone, $lead->email ?? '',
                        $vehicleLabel, $lead->wants_financing ? 'Sim' : 'Nao',
                        $lead->has_trade_in ? 'Sim' : 'Nao',
                        $lead->status->getLabel(), $lead->assignedUser->name ?? 'Nao atribuido',
                    ], ';');
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
