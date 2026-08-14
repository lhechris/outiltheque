<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

use App\Models\Reservation;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->required(),
                TextInput::make('name')
                    ->label('Nom')
                    ->required(),
                TextInput::make('email')
                    ->label('Addresse Email')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->label('Téléphone')
                    ->tel()
                    ->required(),
                TextInput::make('tool_id')
                    ->label('Outil id')
                    ->required()
                    ->numeric(),
                DatePicker::make('date_start')
                    ->required(),
                DatePicker::make('date_end'),
                Select::make('state')
                    ->Options(Reservation::States())
                    ->required(),
                Select::make('payment_state')
                    ->Options(Reservation::payment_states())
                    ->required(),
                TextInput::make('payment_id')
                    ->numeric(),
                TextInput::make('comment')
                    ->label('Commentaire'),
            ]);
    }
}
