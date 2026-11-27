<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Enums\VehicleStatus;
use App\Models\Lead;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\StoreContact;
use Illuminate\Support\Facades\DB;

test('novo lead cadastrado na vitrine envia notificacao interna para usuarios ativos e ignora inativos', function () {
    $activeAdmin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $activeSeller = User::factory()->create(['role' => UserRole::Seller, 'is_active' => true]);
    $inactiveSeller = User::factory()->create(['role' => UserRole::Seller, 'is_active' => false]);

    $vehicle = Vehicle::factory()->create([
        'brand' => 'Honda',
        'model' => 'HR-V',
        'year_model' => 2023,
        'status' => VehicleStatus::Available,
    ]);

    $this->post(route('catalog.leads.store', $vehicle->slug), [
        'name' => 'Carlos Eduardo',
        'phone' => '42999112233',
        'message' => 'Tenho interesse no HR-V.',
        'wants_financing' => true,
        'has_trade_in' => false,
    ])->assertRedirect();

    expect($activeAdmin->fresh()->unreadNotifications)->toHaveCount(1)
        ->and($activeSeller->fresh()->unreadNotifications)->toHaveCount(1)
        ->and($inactiveSeller->fresh()->unreadNotifications)->toHaveCount(0);

    $notificationData = $activeSeller->fresh()->unreadNotifications->first()->data;

    expect($notificationData['title'])->toBe('Novo lead recebido na vitrine!')
        ->and($notificationData['body'])->toContain('Carlos Eduardo', 'Honda HR-V (2023)');
});

test('atribuir responsavel a um lead envia notificacao apenas para o consultor designado', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $targetSeller = User::factory()->create(['role' => UserRole::Seller, 'is_active' => true]);
    $otherSeller = User::factory()->create(['role' => UserRole::Seller, 'is_active' => true]);

    $vehicle = Vehicle::factory()->create([
        'brand' => 'Toyota',
        'model' => 'Corolla',
        'year_model' => 2022,
        'status' => VehicleStatus::Available,
    ]);

    $lead = Lead::factory()->create([
        'vehicle_id' => $vehicle->id,
        'assigned_to' => null,
        'name' => 'Fernanda Lima',
        'phone' => '42988776655',
    ]);

    // Limpa notificações iniciais da criação do lead
    DB::table('notifications')->delete();

    $this->actingAs($admin);
    $lead->update(['assigned_to' => $targetSeller->id]);

    expect($targetSeller->fresh()->unreadNotifications)->toHaveCount(1)
        ->and($otherSeller->fresh()->unreadNotifications)->toHaveCount(0)
        ->and($admin->fresh()->unreadNotifications)->toHaveCount(0);

    $notificationData = $targetSeller->fresh()->unreadNotifications->first()->data;

    expect($notificationData['title'])->toBe('Você foi definido como responsável por um lead')
        ->and($notificationData['body'])->toContain('Fernanda Lima', 'Toyota Corolla (2022)');
});

test('gera url de whatsapp formatada com os dados do lead para o consultor responsavel', function () {
    $seller = User::factory()->create([
        'name' => 'Marcos Oliveira',
        'phone' => '(42) 99123-4567',
        'role' => UserRole::Seller,
        'is_active' => true,
    ]);

    $vehicle = Vehicle::factory()->create([
        'brand' => 'Volkswagen',
        'model' => 'T-Cross',
        'year_model' => 2024,
        'status' => VehicleStatus::Available,
    ]);

    $lead = Lead::factory()->create([
        'vehicle_id' => $vehicle->id,
        'assigned_to' => $seller->id,
        'name' => 'Patrícia Souza',
        'phone' => '42988001122',
        'wants_financing' => true,
        'has_trade_in' => true,
    ]);

    $url = StoreContact::leadAssignmentWhatsappUrl($lead, $seller);

    expect($url)->not->toBeNull()
        ->and($url)->toStartWith('https://wa.me/5542991234567?text=')
        ->and(rawurldecode((string) $url))->toContain(
            'Marcos Oliveira',
            'Patrícia Souza',
            '42988001122',
            'Volkswagen T-Cross (2024)',
            'Financiamento',
            'Troca'
        );
});
