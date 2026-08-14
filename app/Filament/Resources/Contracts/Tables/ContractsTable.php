<?php

namespace App\Filament\Resources\Contracts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label("Nom de la catégorie")
                    ->searchable(),
                TextColumn::make('unit')
                    ->label("Prix à l'unité"),
                TextColumn::make('flat_rate')
                    ->label("Prix au forfait"),
                TextColumn::make('restriction')
                    ->label("Limite d'utilisation"),
                TextColumn::make('color')
                    ->label('Couleur'),
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
