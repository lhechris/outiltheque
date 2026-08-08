<?php

namespace App\Filament\Resources\Tools\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;


class ToolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(), 

                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),   

                Select::make('contract_id')
                    ->relationship('contract', 'name')
                    ->searchable()
                    ->preload()
                    ->required(), 

                TextInput::make('name')
                    ->required(),

                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),

                Repeater::make('features')
                    ->relationship() // utilise la relation "features" du modèle Tool
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('val')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->addActionLabel('Ajouter une caractéristique')
                    ->reorderable(false) // ou true si tu ajoutes une colonne order_column
                    ->collapsible()
                    ->columnSpanFull(),                    

                Textarea::make('advice')
                    ->columnSpanFull(),
                Textarea::make('caution')
                    ->columnSpanFull(),
                TextInput::make('icon'),
                TextInput::make('image'),
                TextInput::make('number')
                    ->numeric()
                    ->default(1),                
                Toggle::make('active')
                    ->required(),
                TextInput::make('views')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
