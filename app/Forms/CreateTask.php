<?php

namespace App\Forms;

use App\Const\TaskStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class CreateTask
{
    static public function addDefinition(Form $form): Form
    {
        return $form
            ->columns([
                'sm' => 1,
                'lg' => 2,
            ])
            ->schema([
                Grid::make()
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('title')
                            ->columnSpanFull()
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(4),
                        Select::make('status')
                            ->columnSpanFull()
                            ->options(
                                TaskStatus::OPTIONS
                            ),
                    ]),
                Grid::make()
                    ->columnSpan(1)
                    ->schema([
                        DatePicker::make('start_date')
                            ->columnSpan(1)
                            ->default(fn() => now()),
                        DatePicker::make('due_date')
                            ->columns(2)
                            ->columnSpan(1)
                            ->default(fn() => now()->addDays(7)),
                        DatePicker::make('completed_date')
                            ->columns(2)
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
