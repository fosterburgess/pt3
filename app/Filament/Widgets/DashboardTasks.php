<?php

namespace App\Filament\Widgets;

use App\Const\TaskStatus;
use App\Forms\CreateTask;
use App\Models\Task;
use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DashboardTasks extends BaseWidget
{
    public function table(Table $table): Table
    {
        $form = Form::make($this);
        $form = CreateTask::addDefinition($form);

        $action = Tables\Actions\CreateAction::make('add-task')
            ->model(Task::class)
            ->form($form->getComponents())
            ->fillForm(function ($arguments, $data) {
                $data['status'] = TaskStatus::__DEFAULT;
                $data['start_date'] = Carbon::parse(data_get($arguments, 'dateStr'))?->format('Y-m-d');
                $data['due_date'] = Carbon::parse(data_get($arguments, 'dateStr'))?->addDays(7)?->format('Y-m-d');
                $data['user_id'] = auth()->user()->id;
                return $data;
            })
            ->action(function ($data) {
                $data['user_id'] = auth()->user()->id;
                Task::create($data);
                Notification::make()
                    ->success()
                    ->title('Task Created')
                    ->send();
            });
        return $table
            ->heading("Upcoming Tasks")
            ->headerActions([
                Action::make('All tasks')
                    ->url(route('filament.main.resources.tasks.index')),
                $action
            ])
            ->paginated([3, 5])
            ->defaultPaginationPageOption(5)
            ->query(
                Task::query()
                    ->where('user_id', auth()->user()->id)
                    ->where('status', '!=', TaskStatus::COMPLETED)
                    ->orderBy('due_date', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('project.title'),
                Tables\Columns\TextColumn::make('due_date')
                    ->date(),
            ]);
    }
}
