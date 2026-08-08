<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/tools', 'tools.index')->name('tools.index');
    Route::livewire('/tools/{tool}', 'tools.show')->name('tools.show');
    Route::livewire('/payments/select/{ref}', 'paiements.select')->name('payments.select');
    Route::livewire('/payments/encaissement/{ref}', 'paiements.encaissement')->name('payments.encaissement');
    Route::livewire('/payments/error/{ref}', 'paiements.error')->name('payments.error');
    Route::livewire('/payments/confirm/{ref}', 'paiements.confirm')->name('payments.confirm');
    Route::livewire('/reservations', 'reservations.index')->name('reservations.index');
    Route::livewire('/reservations/{reservation}', 'reservations.show')->name('reservations.show');

    Route::livewire('/mesreservations/', 'reservations.index_mine')->name('mesreservations.index');
    Route::livewire('/mesreservations/{reservation}', 'reservations.show_mine')->name('mesreservations.show');
});

Route::middleware(['auth', 'verified','admin'])->group(function () {
    Route::livewire('/reservations', 'reservations.index')->name('reservations.index');
    Route::livewire('/reservations/{reservation}', 'reservations.show')->name('reservations.show');
});

require __DIR__.'/settings.php';
