<?php

namespace App\Filament\Resources\JournalReservations\Pages;

use App\Filament\Resources\JournalReservations\JournalReservationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewJournalReservation extends ViewRecord
{
    protected static string $resource = JournalReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
