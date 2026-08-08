<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

use App\Models\User;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('firstname')
                    ->label(__('Firstname'))
                    ->required(),
                TextInput::make('name')
                    ->label( __('Lastname'))
                    ->required(),
                TextInput::make('phone')
                    ->label( 'Téléphone')
                    ->required(),
                TextInput::make('email')
                    ->label(__('Email address'))
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->label(__('Password'))
                    ->password(),
                Select::make('role')
                    ->label('Rôle')
                    ->Options([
                        User::ROLE_ADMIN => 'Administrateur',
                        User::ROLE_USER =>  'Utilisateur',
                    ])
                    ->required(),
            ]);
    }
}
