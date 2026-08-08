<?php

namespace App\Filament\Resources\JournalReservations\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;

class JournalReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('comment')
                    ->label('Commentaire')
            ]);
    }
}
