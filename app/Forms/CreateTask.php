<?php

namespace App\Forms;

use App\Const\TaskStatus;
use App\Models\Project;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
            ->schema(self::getSchema());
    }

    /**
     * @return array
     */
    public static function getSchema(): array
    {
        return [
            Grid::make()
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->columnSpanFull()
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->columnSpanFull()
                        ->rows(4),
                    Grid::make()
                        ->columnSpanFull()
                        ->columns(2)
                        ->schema([
                            Select::make('status')
                                ->columns(1)
                                ->default(TaskStatus::__DEFAULT)
                                ->options(
                                    TaskStatus::OPTIONS
                                ),
                            Select::make('project_id')
                                ->label('Project')
                                ->columns(1)
                                ->placeholder('No project')
                                ->options(function () {
                                    return Project::query()
                                        ->where('user_id', auth()->user()->id)
                                        ->pluck('title', 'id');
                                })
                                ->searchable()
                                ->preload()
                                ->required(false),
                        ])
                ]),
            Grid::make()
                ->columns(3)
                ->columnSpan(3)
                ->schema([
                    DatePicker::make('start_date')
                        ->columnSpan(1)
                        ->default(fn() => now()),
                    DatePicker::make('due_date')
                        ->columns(1)
                        ->columnSpan(1)
                        ->default(fn() => now()->addDays(7)),
                    DatePicker::make('completed_date')
                        ->columns(2)
                        ->columnSpan(1),
                ]),
            FileUpload::make("attachments")
                ->downloadable()
                ->preserveFilenames()
                ->previewable(false)
                ->storeFileNamesIn('attachment_file_names')
                ->disk(config('filesystem.default'))
                ->directory('task-attachments')
                ->columnSpanFull()
                ->maxFiles(12)
                ->multiple(),
        ];
    }
}
