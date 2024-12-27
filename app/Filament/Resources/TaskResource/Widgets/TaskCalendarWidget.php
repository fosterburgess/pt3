<?php

namespace App\Filament\Resources\TaskResource\Widgets;

use App\Forms\CreateTask;
use App\Models\Task;
use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Guava\Calendar\Widgets\CalendarWidget;
use Illuminate\Support\Collection;

class TaskCalendarWidget extends CalendarWidget
{

    protected string $calendarView = 'dayGridMonth';

    protected ?string $defaultEventClickAction = 'edit';
    protected bool $eventClickEnabled = true;

    protected bool $eventDragEnabled = true;

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
                $this->refreshRecords();
            })
        ];

    }

    public function onEventClick(array $info = [], ?string $action = null): void
    {
        $action ??= $this->getDefaultEventClickAction();
        parent::onEventClick($info, $action);
    }

    public function onEventDrop(array $info = []): bool
    {
        // Don't forget to call the parent method to resolve the event record
        parent::onEventDrop($info);

        $task = $this->getEventRecord();
        $task->start_date = Carbon::parse($info['event']['start']);
        $task->due_date = Carbon::parse($info['event']['end']);
        $task->save();

        Notification::make()
            ->body("Task moved to {$task->start_date->format('m/d/Y')}")
            ->success()
            ->send();
        $this->refreshRecords();

        return true;
    }

    public function getEvents(array $fetchInfo = []): Collection|array
    {
        // get a list of Tasks for the current user
        // with the 'start' being the 'due_date' on a task
        // tasks without due dates won't show?
        $tasks = auth()->user()->tasks()
            ->where(function($query) use ($fetchInfo) {
                return $query->whereBetween('due_date', [$fetchInfo['start'], $fetchInfo['end']])
                    ->orWhereBetween('start_date', [$fetchInfo['start'], $fetchInfo['end']]);
            })
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
