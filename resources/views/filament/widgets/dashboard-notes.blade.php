<x-filament-widgets::widget>
    <x-filament::section>

        {{$this->form}}

        <x-filament::button
                class="mt-4"
                wire:click="submit"
                type="button">
            {{ __('Add quick note') }}
        </x-filament::button>

    </x-filament::section>
</x-filament-widgets::widget>
