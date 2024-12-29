<?php

namespace App\Filament\Widgets;

use App\Models\Note;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Widgets\Widget;

class DashboardNotes extends Widget implements HasForms
{
    use InteractsWithForms;
    use InteractsWithFormActions;

    public string $note;

    protected static string $view = 'filament.widgets.dashboard-notes';

    protected function getFormSchema(): array
    {
        return [
            Placeholder::make("header")
                ->hiddenLabel()
                ->content('Quick notes')
                ->extraAttributes(['class' => 'text-xl font-bold']),
            Textarea::make('note')
                ->hiddenLabel()
                ->columnSpanFull(),
        ];
    }

    public function submit()
    {
        $note = Note::create([
            'user_id' => auth()->user()->id,
            'note' => $this->note,
        ]);
        $note->save();
        $this->note = '';
        Notification::make()
            ->success()
            ->title('Note Created')
            ->send();

    }

}
