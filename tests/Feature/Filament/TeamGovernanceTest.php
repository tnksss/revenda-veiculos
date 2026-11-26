<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Filament\Resources\LeadResource\Pages\ListLeads;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\Lead;
use App\Models\User;
use Livewire\Livewire;

test('admin consegue listar e criar usuarios na equipe', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);

    $this->actingAs($admin);

    Livewire::test(ListUsers::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$admin]);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Carlos Vendedor',
            'email' => 'carlos@autoprime.com.br',
            'role' => UserRole::Seller->value,
            'password' => 'SenhaForte123!',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'email' => 'carlos@autoprime.com.br',
        'role' => UserRole::Seller->value,
        'is_active' => true,
    ]);
});

test('vendedor nao tem acesso ao UserResource', function () {
    $seller = User::factory()->create(['role' => UserRole::Seller, 'is_active' => true]);

    $this->actingAs($seller)
        ->get(UserResource::getUrl('index'))
        ->assertForbidden();
});

test('usuario inativo nao consegue acessar o painel administrativo', function () {
    $inactiveUser = User::factory()->create([
        'role' => UserRole::Seller,
        'is_active' => false,
    ]);

    $this->actingAs($inactiveUser)
        ->get('/admin')
        ->assertForbidden();
});

test('admin nao pode excluir a propria conta via UserPolicy', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $seller = User::factory()->create(['role' => UserRole::Seller, 'is_active' => true]);

    expect($admin->can('delete', $admin))->toBeFalse()
        ->and($admin->can('delete', $seller))->toBeTrue();
});

test('vendedor consegue assumir lead sem responsavel e nao pode editar lead de outro consultor', function () {
    $sellerA = User::factory()->create(['role' => UserRole::Seller, 'is_active' => true]);
    $sellerB = User::factory()->create(['role' => UserRole::Seller, 'is_active' => true]);

    $unassignedLead = Lead::factory()->create(['assigned_to' => null]);
    $sellerBLead = Lead::factory()->create(['assigned_to' => $sellerB->id]);

    $this->actingAs($sellerA);

    Livewire::test(ListLeads::class)
        ->callTableAction('claim', $unassignedLead);

    expect($unassignedLead->fresh()->assigned_to)->toBe($sellerA->id)
        ->and($sellerA->can('update', $unassignedLead->fresh()))->toBeTrue()
        ->and($sellerA->can('update', $sellerBLead))->toBeFalse();
});
