<?php

namespace App\Filament\Resources;

use App\Const\TaskStatus;
use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Forms\CreateTask;
use App\Models\Project;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->columnSpanFull()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull()
                    ]),
                Grid::make()
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->default(fn()=>Carbon::now())
                            ->reactive()
                            ->afterStateUpdated(fn (Forms\Set $set, $state) => $set('due_date', Carbon::parse($state)->addMonth()->format('Y-m-d')))
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('due_date')
                            ->default(fn()=>Carbon::now()->addMonth())
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('completed_date')
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
            ]);
    }

    public static function table(Table $table): Table
    {
        $form = Form::make($table->getLivewire());
        $form = CreateTask::addDefinition($form);

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state?->format('m/d/Y')),
                Tables\Columns\TextColumn::make('open_tasks_count')
                    ->label('Open')
                    ->sortable()
                    ->counts('openTasks'),
                Tables\Columns\TextColumn::make('tasks_count')
                    ->label('Tasks')
                    ->sortable()
                    ->counts('tasks'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('add-task')
                    ->icon('heroicon-o-plus')
                    ->label('Task')
                    ->form($form->getComponents())
                    ->fillForm(function ($record, $data) {
                        $data['project_id'] = $record->id;
                        $data['status'] = TaskStatus::__DEFAULT;
                        $data['start_date'] = Carbon::now()->format('Y-m-d');
                        $data['due_date'] = Carbon::now()->addDays(7)->format('Y-m-d');
                        return $data;
                    })
                    ->mutateFormDataUsing(function($data, $record) {
                        $data['user_id'] = auth()->user()->id;
                        $data['project_id'] = $record->id;
                        return $data;
                    })
                    ->action(fn($record, $data) => $record->tasks()->create($data))
                ->after(function($record) {
                    Notification::make()
                        ->body("Task created successfully")
                        ->success()
                        ->send();
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->user()->id)
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
