<?php

namespace App\Filament\Resources\TaskResource\Widgets;

use App\Forms\CreateTask;
use App\Models\Task;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Guava\Calendar\Widgets\CalendarWidget;
use Illuminate\Support\Collection;

class TaskCalendarWidget extends CalendarWidget
{
//    protected static string $view = 'filament.resources.task-resource.widgets.task-calendar-widget';

    protected string $calendarView = 'dayGridMonth';

    protected ?string $defaultEventClickAction = 'edit';
    protected bool $eventClickEnabled = true;

    protected bool $dateClickEnabled = true;

    public function getDateClickContextMenuActions(): array
    {
        $form = Form::make($this);
        $form = CreateTask::addDefinition($form);

        return [
            CreateAction::make('add-task')
                ->model(Task::class)
                ->form($form->getComponents())
                ->fillForm(function($arguments, $data) {
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
            })
        ];

    }

    public function onEventClick(array $info = [], ?string $action = null): void
    {
        $action ??= $this->getDefaultEventClickAction();
        parent::onEventClick($info, $action);
    }

    public function getEvents(array $fetchInfo = []): Collection|array
    {
        // get a list of Tasks for the current user
        // with the 'start' being the 'due_date' on a task
        // tasks without due dates won't show?
        $tasks = auth()->user()->tasks()
            ->whereBetween('due_date', [$fetchInfo['start'], $fetchInfo['end']])
            ->whereNotNull('due_date')
            ->get()
            ->map(fn($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'due_date' => $task->due_date?->format('m/d/Y'),
                'start' => $task->start_date->addHours(1),
                'end' => $task->due_date->addHours(1),
                'extendedProps' => [
                    'model' => Task::class,
                    'key' => $task->id,
                ],
            ]);
        return $tasks->toArray();
    }

    public function getEventContent(): null|string|array
    {
        // return a blade view
        return view('calendar.event');
    }

    public function getSchema(?string $model = null): ?array
    {
        return CreateTask::getSchema();
    }
}
