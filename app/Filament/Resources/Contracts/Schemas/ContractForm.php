<?php

namespace App\Filament\Resources\Contracts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

use Filament\Schemas\Schema;

class ContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom de la catégorie')
                    ->required(),
                TextInput::make('price')
                    ->label('Prix')
                    ->required(),
                TextInput::make('restriction')
                    ->label("Limite d'utilisation")
                    ->required(),                    
                Select::make('color')
                    ->label('Couleur')
                    ->options([
                        'red'    => 'Rouge',
                        'orange' => 'Orange',
                        "amber" => "Ambre",
                        'yellow' => 'Jaune',
                        'lime'   => 'Citron vert',
                        'green'  => 'Vert',
                        'teal'   => 'Turquoise',
                        'cyan'   => 'Cyan',
                        'blue'   => 'Bleue',
                        'indigo' => 'Indigo',
                        'violet' => 'Violet',
                        'purple' => 'Pourpre',
                        'pink' => 'Rose',
                        'rose' => 'Rouge pale',
                    ])
                    ->required(),
            ]);
    }
}
