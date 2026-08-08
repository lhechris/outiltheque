<?php

namespace App\Filament\Resources\JournalReservations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JournalReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                TextColumn::make('tool_name')
                    ->label('Outil')
                    ->sortable(),
                TextColumn::make('date_start')
                    ->label('Début')
                    ->dateTime('d/m/y')
                    ->sortable(),
                TextColumn::make('state')
                    ->label('statut')
                    ->searchable(),
                TextColumn::make('payment_state')
                    ->label('Statut paiement')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
