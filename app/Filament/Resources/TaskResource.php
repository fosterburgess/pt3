<?php

namespace App\Filament\Resources;

use App\Const\TaskStatus;
use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Filament\Resources\TaskResource\Widgets\TaskCalendarWidget;
use App\Forms\CreateTask;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use JaOcero\ActivityTimeline\Components\ActivityDate;
use JaOcero\ActivityTimeline\Components\ActivityDescription;
use JaOcero\ActivityTimeline\Components\ActivityIcon;
use JaOcero\ActivityTimeline\Components\ActivitySection;
use JaOcero\ActivityTimeline\Components\ActivityTitle;
use JaOcero\ActivityTimeline\Enums\IconAnimation;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return CreateTask::addDefinition($form);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $infolist
            ->schema([
                TextEntry::make('title'),
                TextEntry::make('description'),
                TextEntry::make('status'),
                TextEntry::make('project.title'),
                TextEntry::make('start_date'),
                TextEntry::make('due_date'),
                TextEntry::make('completed_date'),
                TextEntry::make('attachments')
                    ->placeholder('none')
                    ->listWithLineBreaks()->bulleted()
                    ->formatStateUsing(function ($state) {
                        return sprintf('<span style="--c-50:var(--primary-50);--c-400:var(--primary-400);--c-600:var(--primary-600);"  class="text-xs rounded-md mx-1 font-medium px-2 min-w-[theme(spacing.6)] py-1  bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30"> <a href="%s"  target="_blank">%s</a></span>', '/storage/' . $state, basename($state));
                    })->html()
            ]);

        return $infolist;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('project.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\TernaryFilter::make('project_id')
                    ->boolean()
                    ->options([
                        '-' => 'All',
                        '1' => 'No',
                        '0' => 'Yes',
                    ])
                    ->label('Has Project?'),
                Tables\Filters\SelectFilter::make('project')
                    ->label('Project')
                    ->multiple()
                    ->preload()
                    ->relationship('project', 'title'),
            ])
            ->actions([
                Tables\Actions\Action::make('activity')
                    ->slideOver()
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->infolist(
                        function ($record) {
                            $il = Infolist::make();
                            $il->state(['activities' => $record->activities->reverse()->toArray()]);
                            $il->schema([
                                    ActivitySection::make('activities')
                                        ->headingVisible(false)
                                        ->schema([
                                            ActivityTitle::make('event')
                                                ->placeholder('No title is set')
                                                ->allowHtml(), // Be aware that you will need to ensure that the HTML is safe to render, otherwise your application will be vulnerable to XSS attacks.
                                            ActivityDescription::make('description')
                                                ->placeholder('No description is set')
                                                ->allowHtml(),
                                            ActivityDate::make('created_at')
                                                ->date('F j, Y', 'Asia/Manila')
                                                ->placeholder('No date is set.'),
                                            ActivityIcon::make('event')
                                                ->icon(fn (string | null $state): string | null => match ($state) {
                                                    'created' => 'heroicon-m-light-bulb',
                                                    'updated' => 'heroicon-m-bolt',
                                                    'deleted' => 'heroicon-m-document-magnifying-glass',
                                                    default => null,
                                                })
                                                ->color(fn (string | null $state): string | null => match ($state) {
                                                    'created' => 'purple',
                                                    'updated' => 'info',
                                                    'deleted' => 'warning',
                                                    default => 'gray',
                                                }),                                        ]),

                                ]);

                            return $il;
                        }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            TaskCalendarWidget::class,
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'calendar' => Pages\ListTasksCalendar::route('/cal'),
            'create' => Pages\CreateTask::route('/create'),
            'view' => Pages\ViewTask::route('/{record}'),
//            'edit' => Pages\EditTask::route('/{record}/edit'),
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
