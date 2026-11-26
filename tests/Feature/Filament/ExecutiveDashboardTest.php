<?php

declare(strict_types=1);

use App\Enums\LeadStatus;
use App\Enums\UserRole;
use App\Enums\VehicleStatus;
use App\Filament\Widgets\InventoryOverviewWidget;
use App\Filament\Widgets\LatestUnassignedLeadsWidget;
use App\Filament\Widgets\TeamPerformanceWidget;
use App\Models\Lead;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

test('admin visualiza KPIs financeiros de capital imobilizado e margem potencial', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);

    Vehicle::factory()->create([
        'status' => VehicleStatus::Available,
        'cost_price' => 50000.00,
        'price' => 62000.00,
    ]);

    $this->actingAs($admin);

    Livewire::test(InventoryOverviewWidget::class)
        ->assertSuccessful()
        ->assertSee('Veículos em Pátio')
        ->assertSee('Capital Imobilizado (Custo)')
        ->assertSee('50.000,00')
        ->assertSee('12.000,00');

    expect(TeamPerformanceWidget::canView())->toBeTrue();
});

test('vendedor nao visualiza capital imobilizado nem ranking gerencial da equipe', function () {
    $seller = User::factory()->create(['role' => UserRole::Seller, 'is_active' => true]);

    Vehicle::factory()->create([
        'status' => VehicleStatus::Available,
        'cost_price' => 50000.00,
        'price' => 62000.00,
    ]);

    $this->actingAs($seller);

    Livewire::test(InventoryOverviewWidget::class)
        ->assertSuccessful()
        ->assertSee('Veículos em Pátio')
        ->assertDontSee('Capital Imobilizado (Custo)')
        ->assertDontSee('50.000,00');

    expect(TeamPerformanceWidget::canView())->toBeFalse();
});

test('consultor consegue assumir lead diretamente pelo widget do dashboard', function () {
    $seller = User::factory()->create(['role' => UserRole::Seller, 'is_active' => true]);
    $lead = Lead::factory()->create([
        'assigned_to' => null,
        'status' => LeadStatus::New,
    ]);

    $this->actingAs($seller);

    Livewire::test(LatestUnassignedLeadsWidget::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$lead])
        ->callTableAction('claim', $lead);

    $lead->refresh();

    expect($lead->assigned_to)->toBe($seller->id)
        ->and($lead->status)->toBe(LeadStatus::Contacted);
});
