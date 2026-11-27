<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\User;
use App\Models\Vehicle;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;

class ViewVehicleMinimumPriceAction
{
    public static function make(callable $resolveVehicle, ?string $context = null): Action
    {
        return Action::make('viewMinimumPrice')
            ->label('Ver Piso de Negociação')
            ->icon('heroicon-o-shield-check')
            ->color('warning')
            ->modalHeading('Alçada de Negociação')
            ->modalDescription('Uso interno do consultor. Não exiba esta tela ao cliente.')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->visible(fn (): bool => $resolveVehicle() instanceof Vehicle)
            ->mountUsing(function () use ($resolveVehicle, $context): void {
                $vehicle = $resolveVehicle();

                if (! $vehicle instanceof Vehicle) {
                    return;
                }

                /** @var User|null $user */
                $user = auth()->user();
                $vehicle->recordMinimumPriceConsultation($user, $context);
            })
            ->form(function () use ($resolveVehicle): array {
                /** @var Vehicle $vehicle */
                $vehicle = $resolveVehicle();
                $price = (float) $vehicle->price;
                $minPrice = $vehicle->minimum_price !== null ? (float) $vehicle->minimum_price : null;

                $formattedPrice = 'R$ '.number_format($price, 2, ',', '.');

                if ($minPrice === null) {
                    return [
                        Placeholder::make('no_minimum')
                            ->hiddenLabel()
                            ->content(new HtmlString(
                                "<div class='rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-200'>
                                    <p class='font-semibold'>Preço Anunciado: {$formattedPrice}</p>
                                    <p class='mt-1'>Nenhum piso mínimo pré-autorizado foi cadastrado para este veículo. Consulte o gerente antes de conceder desconto.</p>
                                </div>"
                            )),
                    ];
                }

                $formattedMin = 'R$ '.number_format($minPrice, 2, ',', '.');
                $maxDiscount = max(0, $price - $minPrice);
                $formattedDiscount = 'R$ '.number_format($maxDiscount, 2, ',', '.');

                return [
                    Placeholder::make('negotiation_floor')
                        ->hiddenLabel()
                        ->content(new HtmlString(
                            "<div class='space-y-3 rounded-lg border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-950 dark:border-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-100'>
                                <div class='flex items-center justify-between'>
                                    <span>Preço Anunciado:</span>
                                    <span class='font-medium'>{$formattedPrice}</span>
                                </div>
                                <div class='flex items-center justify-between border-t border-emerald-200 pt-2 text-base font-bold dark:border-emerald-800'>
                                    <span>Piso Autorizado:</span>
                                    <span>{$formattedMin}</span>
                                </div>
                                <div class='flex items-center justify-between text-xs opacity-90'>
                                    <span>Margem máxima de desconto:</span>
                                    <span class='font-semibold'>até {$formattedDiscount}</span>
                                </div>
                            </div>"
                        )),
                ];
            });
    }
}
