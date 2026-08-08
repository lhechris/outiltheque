<?php

namespace App\Filament\Resources\JournalReservations;

use App\Filament\Resources\JournalReservations\Pages\CreateJournalReservation;
use App\Filament\Resources\JournalReservations\Pages\EditJournalReservation;
use App\Filament\Resources\JournalReservations\Pages\ListJournalReservations;
use App\Filament\Resources\JournalReservations\Pages\ViewJournalReservation;
use App\Filament\Resources\JournalReservations\Schemas\JournalReservationForm;
use App\Filament\Resources\JournalReservations\Schemas\JournalReservationInfolist;
use App\Filament\Resources\JournalReservations\Tables\JournalReservationsTable;
use App\Models\JournalReservation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JournalReservationResource extends Resource
{
    protected static ?string $model = JournalReservation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Journal des réservations';

    public static function form(Schema $schema): Schema
    {
        return JournalReservationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JournalReservationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JournalReservationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJournalReservations::route('/'),
            'create' => CreateJournalReservation::route('/create'),
            'view' => ViewJournalReservation::route('/{record}'),
            'edit' => EditJournalReservation::route('/{record}/edit'),
        ];
    }
}
