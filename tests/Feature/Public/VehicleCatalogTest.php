<?php

declare(strict_types=1);

use App\Enums\FuelType;
use App\Models\Vehicle;

test('vitrine publica exibe veiculos disponiveis e reservados e oculta preparados e vendidos', function (): void {
    $available = Vehicle::factory()->available()->create(['brand' => 'Honda', 'model' => 'Civic']);
    $reserved = Vehicle::factory()->reserved()->create(['brand' => 'Toyota', 'model' => 'Corolla']);
    $preparation = Vehicle::factory()->preparation()->create(['brand' => 'Fiat', 'model' => 'Mobi']);
    $sold = Vehicle::factory()->sold()->create(['brand' => 'Ford', 'model' => 'Ka']);

    $response = $this->get(route('catalog.index'));

    $response->assertOk()
        ->assertSee('Civic')
        ->assertSee('Corolla')
        ->assertDontSee('Mobi')
        ->assertDontSee('Ka');
});

test('vitrine publica exibe apenas o final da placa e nunca vaza placa completa, custo ou notas internas', function (): void {
    $vehicle = Vehicle::factory()->available()->create([
        'brand' => 'Volkswagen',
        'model' => 'T-Cross',
        'plate' => 'XYZ9A87',
        'cost_price' => 89500.00,
        'internal_notes' => 'Repintura no para-choque traseiro',
    ]);

    $this->get(route('catalog.index'))
        ->assertOk()
        ->assertSee('Final 7')
        ->assertDontSee('XYZ9A87')
        ->assertDontSee('89.500,00')
        ->assertDontSee('Repintura no para-choque traseiro');

    $this->get(route('catalog.show', $vehicle->slug))
        ->assertOk()
        ->assertSee('Final da Placa: 7')
        ->assertDontSee('XYZ9A87')
        ->assertDontSee('89.500,00')
        ->assertDontSee('Repintura no para-choque traseiro');
});

test('filtros da vitrine filtram por termo de busca e combustivel', function (): void {
    Vehicle::factory()->available()->create([
        'brand' => 'Honda',
        'model' => 'HR-V',
        'fuel_type' => FuelType::Flex,
    ]);

    Vehicle::factory()->available()->create([
        'brand' => 'BYD',
        'model' => 'Dolphin',
        'fuel_type' => FuelType::Electric,
    ]);

    $this->get(route('catalog.index', ['q' => 'Dolphin', 'fuel' => FuelType::Electric->value]))
        ->assertOk()
        ->assertSee('Dolphin')
        ->assertDontSee('HR-V');
});

test('acessar detalhes de veiculo vendido ou em preparacao retorna 404', function (): void {
    $sold = Vehicle::factory()->sold()->create();
    $preparation = Vehicle::factory()->preparation()->create();

    $this->get(route('catalog.show', $sold->slug))->assertNotFound();
    $this->get(route('catalog.show', $preparation->slug))->assertNotFound();
});
