<?php

declare(strict_types=1);

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use App\Support\StoreContact;

test('gera link de whatsapp normalizado com codigo do pais a partir de config store', function () {
    config()->set('store.whatsapp', '(42) 99876-5432');

    $url = StoreContact::whatsappUrl('Olá Tobias');

    expect($url)->toBe('https://wa.me/5542998765432?text=Ol%C3%A1%20Tobias');
});

test('vitrine e detalhes do veiculo exibem telefone, endereco e link de whatsapp configurados', function () {
    config()->set('store.whatsapp', '5542988887777');
    config()->set('store.phone', '(42) 3622-1234');
    config()->set('store.address', 'Rua XV de Novembro, 500 - Guarapuava');

    $vehicle = Vehicle::factory()->create([
        'status' => VehicleStatus::Available,
    ]);

    $this->get(route('catalog.index'))
        ->assertSuccessful()
        ->assertSee('(42) 3622-1234')
        ->assertSee('Rua XV de Novembro, 500 - Guarapuava')
        ->assertSee('https://wa.me/5542988887777');

    $this->get(route('catalog.show', $vehicle->slug))
        ->assertSuccessful()
        ->assertSee('https://wa.me/5542988887777');
});
