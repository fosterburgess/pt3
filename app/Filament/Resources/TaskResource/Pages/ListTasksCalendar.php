<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\Page;

class ListTasksCalendar extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected static string $view = 'filament.resources.task-resource.pages.list-tasks-calendar';

    public function getHeaderWidgets(): array
    {
        return [
            TaskResource\Widgets\TaskCalendarWidget::class,
        ];
    }

}
