<?php

namespace App\Filament\Resources\Parameters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ParameterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required(),
                TextInput::make('val')
                    ->label('Valeur'),
            ]);
    }
}
