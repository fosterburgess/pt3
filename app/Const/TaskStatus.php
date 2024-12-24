<?php

namespace App\Const;

class TaskStatus
{
    const string PENDING = 'pending';
    const string IN_PROGRESS = 'in_progress';
    const string COMPLETED = 'completed';
    const string ARCHIVED = 'archived';

    const string __DEFAULT = self::PENDING;
    const array OPTIONS = [
        self::PENDING => 'Pending',
        self::IN_PROGRESS => 'In Progress',
        self::COMPLETED => 'Completed',
        self::ARCHIVED => 'Archived',
    ];
}
