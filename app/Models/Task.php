<?php

namespace App\Models;

use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model implements Eventable
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'completed_date' => 'date',
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function toEvent(): Event|array {
        return Event::make($this)
            ->action('edit')
            ->model(Task::class)
            ->key($this->id)
            ->title($this->title)
            ->start($this->start_date?->addHour()?->format('m/d/Y'))
            ->end($this->due_date?->addHour()?->format('m/d/Y'));
    }
}
