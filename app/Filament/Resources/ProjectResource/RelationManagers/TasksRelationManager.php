<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Const\TaskStatus;
use App\Enums\TaskStatusEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->rows(4),
                Forms\Components\Select::make('status')
                ->options(
                    TaskStatus::OPTIONS
                )
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn($state) => TaskStatus::OPTIONS[$state]),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function getTabs(): array
    {
        $tabList = [];
        $tabList['all'] = Tab::make()
            ->badge(count($this->ownerRecord->tasks));

        foreach(TaskStatus::OPTIONS as $key => $value) {
            $tabList[$key] = Tab::make()
                ->badge(count($this->ownerRecord->tasks()->where('status', $key)->get()))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', $key));

        }


        return $tabList;
    }

}
