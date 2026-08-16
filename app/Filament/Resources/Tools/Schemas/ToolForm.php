<?php

namespace App\Filament\Resources\Tools\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ToolForm
{
    public static function configure(Schema $schema): Schema
    {
        $inputClasses = [
            'class' => 'rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-[#1b716c] focus:ring-2 focus:ring-[#1b716c]/15',
        ];

        return $schema
            ->components([
                Section::make('Informations générales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom')
                                    ->required()
                                    ->extraInputAttributes($inputClasses),

                                FileUpload::make('icon')
                                    ->label('Icône')
                                    ->image()
                                    ->disk('public')
                                    ->directory('uploads/icons')
                                    ->imagePreviewHeight('120')
                                    ->maxFiles(1)
                                    ->preserveFilenames(),

                                FileUpload::make('image')
                                    ->label('Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('uploads/images')
                                    ->imagePreviewHeight('120')
                                    ->maxFiles(1)
                                    ->preserveFilenames(),

                                TextInput::make('number')
                                    ->label('Nombre en stock')
                                    ->numeric()
                                    ->default(1)
                                    ->extraInputAttributes($inputClasses),
                            ]),

                        Textarea::make('description')
                            ->required()
                            ->rows(5)
                            ->extraInputAttributes([
                                'class' => 'rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-[#1b716c] focus:ring-2 focus:ring-[#1b716c]/15',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->extraAttributes([
                        'class' => 'rounded-2xl border border-slate-200 bg-white shadow-sm',
                    ]),

                Section::make('Catégorie et visibilité')
                    ->schema([
                        Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Select::make('contract_id')
                            ->label('Contrat')
                            ->relationship('contract', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Select::make('tags')
                            ->label('Etiquettes')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false),

                        Toggle::make('active')
                            ->required(),
                    ])
                    ->columns(2)
                    ->extraAttributes([
                        'class' => 'rounded-2xl border border-slate-200 bg-white shadow-sm',
                    ]),

                Section::make('Contenu')
                    ->schema([
                        Textarea::make('advice')
                            ->label('Conseil')
                            ->rows(5)
                            ->extraInputAttributes([
                                'class' => 'rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-[#1b716c] focus:ring-2 focus:ring-[#1b716c]/15',
                            ])
                            ->columnSpanFull(),

                        Textarea::make('caution')
                            ->label('Précaution')
                            ->rows(5)
                            ->extraInputAttributes([
                                'class' => 'rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-[#1b716c] focus:ring-2 focus:ring-[#1b716c]/15',
                            ])
                            ->columnSpanFull(),

                        Repeater::make('features')
                            ->label("Caractéristiques")
                            ->relationship()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->extraInputAttributes($inputClasses),
                                TextInput::make('val')
                                    ->required()
                                    ->maxLength(255)
                                    ->extraInputAttributes($inputClasses),
                            ])
                            ->columns(2)
                            ->addActionLabel('Ajouter une caractéristique')
                            ->reorderable(false)
                            ->collapsible()
                            ->columnSpanFull(),
                    ])
                    ->extraAttributes([
                        'class' => 'rounded-2xl border border-slate-200 bg-white shadow-sm',
                    ]),
            ]);
    }
}
