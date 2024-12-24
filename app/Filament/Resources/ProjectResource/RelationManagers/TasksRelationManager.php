<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Const\TaskStatus;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    public function form(Form $form): Form
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
                        Forms\Components\TextInput::make('title')
                            ->columnSpanFull()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(4),
                        Forms\Components\Select::make('status')
                            ->columnSpanFull()
                            ->options(
                                TaskStatus::OPTIONS
                            ),
                    ]),
                Grid::make()
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->columnSpan(1)
                            ->default(fn() => now()),
                        Forms\Components\DatePicker::make('due_date')
                            ->columns(2)
                            ->columnSpan(1)
                            ->default(fn() => now()->addDays(7)),
                        Forms\Components\DatePicker::make('completed_date')
                            ->columns(2)
                            ->columnSpan(1),
                    ]),
            ]);

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
                        ->formatStateUsing(fn($state) => "Due: ".$state?->format('m/d/Y')),
                    Tables\Columns\TextColumn::make('status')
                      ->formatStateUsing(fn($state) => TaskStatus::OPTIONS[$state]),

                ])
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
                        ->action(function (Collection $records, $data){
                            foreach($records as $record){
                                $record->status = $data['status'];
                                $record->save();
                            }
                            Notification::make()
                                ->body("Status changed to ".TaskStatus::OPTIONS[$data['status']])
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
