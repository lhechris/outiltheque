<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ReservationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('reference')
                    ->label('Référence'),
                TextEntry::make('name')
                    ->label('Nom'),
                TextEntry::make('phone')
                    ->label('Téléphone'),
                TextEntry::make('tool.name')
                    ->label('Outil'),
                TextEntry::make('date_start')
                    ->label("Date de l'emprunt")
                    ->dateTime('m/d/y'),
                TextEntry::make('date_end')
                    ->label('Date de retour')
                    ->dateTime('m/d/y')
                    ->placeholder('-'),
                TextEntry::make('state')
                    ->label('Etat')
                    ->placeholder('-'),
                TextEntry::make('payment_state')
                    ->label('Etat du paiement')
                    ->placeholder('-'),
                TextEntry::make('payment_id')
                    ->label('Référence du paiement')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('comment')
                    ->label('commentaires')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
