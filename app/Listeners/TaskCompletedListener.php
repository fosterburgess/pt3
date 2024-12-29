<?php

namespace App\Listeners;

use App\Const\TaskStatus;
use App\Events\TaskCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TaskCompletedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TaskCompleted $event): void
    {
        $task = $event->task;
        $task->completed_date = now();
        $task->status = TaskStatus::COMPLETED;
        $task->saveQuietly();
    }
}
