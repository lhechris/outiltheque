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
                TextInput::make('unit')
                    ->label("Prix à l'unité")
                    ->integer()
                    ->required(),
                TextInput::make('flat_rate')
                    ->label('Prix au forfait')
                    ->integer()
                    ->required(),
                TextInput::make('restriction')
                    ->label("Limite d'utilisation")
                    ->required(),                    
                Select::make('color')
                    ->label('Couleur')
                    ->options([
                        'red'    => 'Rouge',
                        'orange' => 'Orange',
                        "amber"  => "Ambre",
                        'yellow' => 'Jaune',
                        'lime'   => 'Citron vert',
                        'green'  => 'Vert',
                        'emerald'=> "Emeraude",
                        'teal'   => 'Turquoise',
                        'cyan'   => 'Cyan',
                        'blue'   => 'Bleue',
                        'indigo' => 'Indigo',
                        'violet' => 'Violet',
                        'purple' => 'Pourpre',
                        'pink'   => 'Rose',
                        'rose'   => 'Rouge pale',
                    ])
                    ->required(),
            ]);
    }
}
