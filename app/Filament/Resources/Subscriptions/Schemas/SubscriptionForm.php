<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Models\Reservation;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('contract_id')
                    ->relationship('contract', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->required(),                     
                Select::make('payment_state')
                    ->Options(Reservation::payment_states())
                    ->required(),
                DateTimePicker::make('begin'),
                DateTimePicker::make('expire'),
            ]);
    }
}
