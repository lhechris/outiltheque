<?php

namespace App\Filament\Resources\Reservations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('tool.name')
                    ->label('Outil')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('date_start')
                    ->label("Début")
                    ->dateTime('d/m/y')
                    ->sortable(),
                TextColumn::make('date_end')
                    ->label("Fin")
                    ->dateTime('d/m/y')
                    ->sortable(),
                TextColumn::make('state')
                    ->label("Etat")
                    ->searchable(),
                TextColumn::make('payment_state')
                    ->label("Paiement")
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
