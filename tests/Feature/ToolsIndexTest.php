<?php

use App\Filament\Resources\Tools\ToolResource;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('tools.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the catalog', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('tools.index'));
    $response->assertOk();
});

test('edit form renders the real filaments fields for the tool', function () {
    $user = makeUser('admin');
    $tool = makeTool();

    $this->actingAs($user)
        ->get(ToolResource::getUrl('edit', ['record' => $tool]))
        ->assertOk()
        ->assertSee('Informations générales')
        ->assertSee('Ajouter une caractéristique')
        ->assertSee('Icône')
        ->assertSee('Image');
});