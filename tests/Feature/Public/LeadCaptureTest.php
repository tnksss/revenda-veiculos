<?php

declare(strict_types=1);

use App\Enums\LeadStatus;
use App\Filament\Resources\LeadResource\Pages\ListLeads;
use App\Models\Lead;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

test('cliente consegue enviar proposta para veiculo disponivel na vitrine', function (): void {
    $vehicle = Vehicle::factory()->available()->create();

    $response = $this->post(route('catalog.leads.store', $vehicle->slug), [
        'website' => '',
        'name' => 'Carlos Eduardo',
        'phone' => '(42) 99988-7766',
        'email' => 'carlos@example.com',
        'wants_financing' => '1',
        'has_trade_in' => '1',
        'message' => 'Tenho um Onix 2021 na troca.',
    ]);

    $response->assertRedirect(route('catalog.show', $vehicle->slug))
        ->assertSessionHas('lead_success');

    $this->assertDatabaseHas('leads', [
        'vehicle_id' => $vehicle->id,
        'name' => 'Carlos Eduardo',
        'phone' => '(42) 99988-7766',
        'wants_financing' => true,
        'has_trade_in' => true,
        'status' => LeadStatus::New->value,
    ]);
});

test('honeypot bloqueia envio automatizado quando campo oculto website e preenchido', function (): void {
    $vehicle = Vehicle::factory()->available()->create();

    $response = $this->from(route('catalog.show', $vehicle->slug))
        ->post(route('catalog.leads.store', $vehicle->slug), [
            'website' => 'https://spam-bot.example.com',
            'name' => 'Bot Spammer',
            'phone' => '11999999999',
        ]);

    $response->assertRedirect(route('catalog.show', $vehicle->slug))
        ->assertSessionHasErrors(['website']);

    $this->assertDatabaseCount('leads', 0);
});

test('nao permite enviar proposta para veiculo vendido ou em preparacao', function (): void {
    $soldVehicle = Vehicle::factory()->sold()->create();

    $this->post(route('catalog.leads.store', $soldVehicle->slug), [
        'name' => 'Maria Silva',
        'phone' => '(42) 98877-6655',
    ])->assertNotFound();

    $this->assertDatabaseCount('leads', 0);
});

test('painel administrativo exibe leads capturados no LeadResource', function (): void {
    $this->actingAs(User::factory()->create());

    $leads = Lead::factory()->count(3)->create();

    Livewire::test(ListLeads::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($leads);
});
