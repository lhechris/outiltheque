<?php

namespace App\Filament\Resources\JournalReservations\Pages;

use App\Filament\Resources\JournalReservations\JournalReservationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditJournalReservation extends EditRecord
{
    protected static string $resource = JournalReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
