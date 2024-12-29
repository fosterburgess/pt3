<?php

namespace App\Models;

use App\Observers\TaskObserver;
use App\Traits\ActivityLogTrait;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\Event;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([TaskObserver::class])]
class Task extends Model implements Eventable
{
    use SoftDeletes;
    use ActivityLogTrait;

    protected $guarded = ['id'];

    protected $casts = [
        'completed_date' => 'date',
        'start_date' => 'date',
        'due_date' => 'date',
        'attachments' => 'array',
        'attachment_file_names' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
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
