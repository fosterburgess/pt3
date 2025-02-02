<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Const\TaskStatus;
use App\Forms\CreateTask;
use App\Models\Task;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Tasks';

    public static function getDisplayTitle(): ?string
    {
        return self::$title;
    }

    public function form(Form $form): Form
    {
        return CreateTask::addDefinition($form);
    }

    public function table(Table $table): Table
    {
        return $table
            ->contentGrid(
                [
                    'sm' => 1,
                    'md' => 3,
                ]
            )
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('title'),
                    Tables\Columns\TextColumn::make('due_date')
                        ->formatStateUsing(fn($state) => "Due: " . $state?->format('m/d/Y')),
                    Tables\Columns\TextColumn::make('status')
                        ->formatStateUsing(fn($state) => TaskStatus::OPTIONS[$state]),

                ])
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add task')
                    ->model(Task::class)
                    ->mutateFormDataUsing(function($data)  {
                        $data['user_id'] = Auth::user()->id;
                        return $data;
                    })
                    ->fillForm([
                        'status' => TaskStatus::__DEFAULT,
                        'start_date' => Carbon::now()->format('Y-m-d'),
                        'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                        'user_id' => Auth::user()->id,
                        'project_id' => $this->ownerRecord->id,
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('Change status')
                        ->icon('heroicon-s-check-circle')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options(TaskStatus::OPTIONS)
                        ])
                        ->modalSubmitActionLabel('Change status')
                        ->action(function (Collection $records, $data) {
                            foreach ($records as $record) {
                                $record->status = $data['status'];
                                $record->save();
                            }
                            Notification::make()
                                ->body("Status changed to " . TaskStatus::OPTIONS[$data['status']])
                                ->success()
                                ->send();
                        })
                ]),
            ]);
    }

    public function getTabs(): array
    {
        $tabList = [];
        $tabList['all'] = Tab::make()
            ->badge(count($this->ownerRecord->tasks));

        foreach (TaskStatus::OPTIONS as $key => $value) {
            $tabList[$key] = Tab::make()
                ->badge(count($this->ownerRecord->tasks()->where('status', $key)->get()))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', $key));

        }


        return $tabList;
    }

}
