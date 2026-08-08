<?php

namespace App\Filament\Resources\JournalReservations\Pages;

use App\Filament\Resources\JournalReservations\JournalReservationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJournalReservations extends ListRecords
{
    protected static string $resource = JournalReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
