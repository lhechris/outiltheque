<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Security;
use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', Profile::class)->name('profile.edit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::livewire('settings/security', Security::class)
        ->middleware([
            'password.confirm',
        ])
        ->name('security.edit');
});

Route::get('/auth/google', function () {
    return Socialite::driver('google')->redirect();
});

Route::get('/auth/google/callback', function () {

    $googleUser = Socialite::driver('google')->user();
    $googleRawUser =  $googleUser->getRaw();

    // chercher ou créer utilisateur
    $user = App\Models\User::where('google_id', $googleUser->getId())
        ->orWhere('email', $googleUser->getEmail())
        ->first();


    if (!$user) {
        // L'utilisateur n'existe pas, on le crée       
        \Log::info("Ajoute l'utilisateur google");
        \Log::debug(print_r($googleRawUser,true));
       $user =  User::create([
            'name' => $googleRawUser['family_name'],
            'firstname' => $googleRawUser['given_name'],
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
        ]);

    } else {
        \Log::info("Utilisateur google existant :".$googleUser->getId());

        $user->update([
            'google_id' => $googleUser->getId()
        ]);
    }
    Auth::login($user,true);

    return redirect(route('tools.index'));
});